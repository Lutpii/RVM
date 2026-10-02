"""Compactor hardware for the 2-bin DSME RVM.

A gate (flap) servo holds the item under the camera, above a compactor
chamber. The chamber holds one material per batch; flushing it compacts the
batch and tilts it into the tin bin (right) or the plastic bin (left).

Machine owns the chamber state and runs every hardware action on a single
worker thread, in order, so two actions never overlap. The driver does the
physical moves; PlaceholderDriver only sleeps for the real durations (no
GPIO). The real driver will be ported from DSME's rvm.py later.
"""
import json
import logging
import os
import threading
import time
import uuid
from collections import OrderedDict, deque

from materials import ACCEPTED_MATERIALS

log = logging.getLogger(__name__)

# Durations in seconds, taken from DSME's rvm.py.
GATE_MOVE = 1.9        # 80 degrees at 0.02 s/degree, plus 0.3 s settle
GATE_OPEN_HOLD = 2
COMPACT_IN = 16
COMPACT_OUT = 16
TILT_MOVE = 3
TILT_HOLD = 2

DEPOSIT_SECONDS = GATE_MOVE + GATE_OPEN_HOLD + GATE_MOVE
FLUSH_SECONDS = COMPACT_IN + COMPACT_OUT + TILT_MOVE + TILT_HOLD + TILT_MOVE

MAX_JOBS_KEPT = 50

# The compactor chamber holds at most this many items; a further item of the
# same material empties the chamber first (compact + tilt) before it drops.
CHAMBER_CAPACITY = 3


class PlaceholderDriver:
    """Stands in for the real GPIO driver: same timing, no hardware."""

    def __init__(self, time_scale=1.0, sleep=time.sleep):
        self._scale = time_scale
        self._sleep = sleep

    def _wait(self, seconds):
        if self._scale > 0:
            self._sleep(round(seconds * self._scale, 6))

    def gate_open(self):
        self._wait(GATE_MOVE + GATE_OPEN_HOLD)

    def gate_close(self):
        self._wait(GATE_MOVE)

    def compact(self):
        self._wait(COMPACT_IN + COMPACT_OUT)

    def tilt(self, material):
        self._wait(TILT_MOVE + TILT_HOLD + TILT_MOVE)

    def flap_is_empty(self):
        return True


class Machine:
    def __init__(self, driver, state_path=None):
        self._driver = driver
        self._state_path = state_path
        self._cv = threading.Condition()
        self._queue = deque()
        self._jobs = OrderedDict()
        self._running = None
        self._phase = 'idle'
        self._chamber_material = None
        self._chamber_count = 0
        self._thread = None
        self._load()

    # ---------- public ----------

    def start(self):
        if self._thread is None:
            self._thread = threading.Thread(target=self._worker, daemon=True)
            self._thread.start()

    def deposit(self, material, allow_flush):
        if material not in ACCEPTED_MATERIALS:
            return {'accepted': False, 'reason': 'not_accepted'}
        with self._cv:
            chamber, count, pending = self._projection()
            mismatch = chamber is not None and chamber != material
            full = chamber == material and count >= CHAMBER_CAPACITY
            will_flush = mismatch or full
            if mismatch and not allow_flush:
                return {'accepted': False, 'reason': 'mismatch',
                        'chamber_material': chamber, 'chamber_count': count}
            eta = pending + (FLUSH_SECONDS if will_flush else 0) + DEPOSIT_SECONDS
            job_id = self._enqueue({'type': 'deposit', 'material': material})
            return {'accepted': True, 'job_id': job_id, 'will_flush': will_flush,
                    'eta_seconds': round(eta)}

    def flush(self):
        with self._cv:
            chamber, _, pending = self._projection()
            eta = pending + (FLUSH_SECONDS if chamber is not None else 0)
            job_id = self._enqueue({'type': 'flush'})
            return {'job_id': job_id, 'eta_seconds': round(eta)}

    def flap_is_empty(self):
        return bool(self._driver.flap_is_empty())

    def state(self, job_id=None):
        with self._cv:
            out = {
                'chamber_material': self._chamber_material,
                'chamber_count': self._chamber_count,
                'busy': self._running is not None or bool(self._queue),
                'phase': self._phase,
                'queue_length': len(self._queue),
            }
            if job_id is not None:
                job = self._jobs.get(job_id)
                out['job'] = {'id': job_id, 'status': job['status'] if job else 'unknown'}
            return out

    def wait_idle(self, timeout=5.0):
        deadline = time.monotonic() + timeout
        with self._cv:
            while self._running is not None or self._queue:
                remaining = deadline - time.monotonic()
                if remaining <= 0:
                    return False
                self._cv.wait(remaining)
            return True

    # ---------- internals (call with self._cv held) ----------

    def _projection(self):
        """Chamber material/count once the running and queued jobs finish,
        plus the seconds those jobs still need. A running job is counted in
        full: its chamber update only lands when it finishes."""
        material, count, seconds = self._chamber_material, self._chamber_count, 0.0
        # A deposit that already dropped has updated the chamber; only its
        # flap is still closing, so it no longer changes the projection.
        running = [self._running] if self._running and self._running['status'] != 'dropped' else []
        pending = running + list(self._queue)
        for job in pending:
            if job['type'] == 'flush':
                if material is not None:
                    seconds += FLUSH_SECONDS
                material, count = None, 0
            else:
                if material is not None and (material != job['material'] or count >= CHAMBER_CAPACITY):
                    seconds += FLUSH_SECONDS
                    material, count = None, 0
                seconds += DEPOSIT_SECONDS
                material, count = job['material'], count + 1
        return material, count, seconds

    def _enqueue(self, job):
        job_id = uuid.uuid4().hex[:12]
        job.update(id=job_id, status='queued')
        self._jobs[job_id] = job
        while len(self._jobs) > MAX_JOBS_KEPT:
            self._jobs.popitem(last=False)
        self._queue.append(job)
        self._cv.notify_all()
        return job_id

    def _finish(self, job, status):
        job['status'] = status
        self._running = None
        self._phase = 'idle'
        self._cv.notify_all()

    def _set_phase(self, phase):
        with self._cv:
            self._phase = phase

    def _load(self):
        if not self._state_path:
            return
        try:
            with open(self._state_path, encoding='utf-8') as fh:
                data = json.load(fh)
            material = data.get('chamber_material')
            count = int(data.get('chamber_count', 0))
        except (OSError, ValueError, TypeError, AttributeError):
            return
        if material in ACCEPTED_MATERIALS and count > 0:
            self._chamber_material, self._chamber_count = material, count

    def _save(self):
        # Write a temp file and swap it in, so a power cut mid-write leaves
        # the previous state instead of a torn file that loads as "empty"
        # (which would let the next different material skip the flush).
        if not self._state_path:
            return
        tmp = self._state_path + '.tmp'
        try:
            with open(tmp, 'w', encoding='utf-8') as fh:
                json.dump({'chamber_material': self._chamber_material,
                           'chamber_count': self._chamber_count}, fh)
                fh.flush()
                os.fsync(fh.fileno())
            os.replace(tmp, self._state_path)
        except OSError as e:
            log.error('Could not save chamber state: %s', e)

    # ---------- worker thread ----------

    def _worker(self):
        while True:
            with self._cv:
                while not self._queue:
                    self._cv.wait()
                job = self._queue.popleft()
                job['status'] = 'running'
                self._running = job
            try:
                if job['type'] == 'flush':
                    self._do_flush(finish_job=job)
                else:
                    self._do_deposit(job)
            except Exception:
                log.exception('Hardware job %s (%s) failed', job['id'], job['type'])
                with self._cv:
                    # A failure after the drop (flap not closing) keeps the drop:
                    # the item is in the chamber and its points were awarded.
                    self._finish(job, 'dropped' if job['status'] == 'dropped' else 'failed')

    def _do_flush(self, finish_job=None):
        with self._cv:
            material = self._chamber_material
        if material is not None:
            self._set_phase('compacting')
            self._driver.compact()
            self._set_phase('tilting')
            self._driver.tilt(material)
        with self._cv:
            if material is not None:
                self._chamber_material, self._chamber_count = None, 0
                self._save()
            if finish_job is not None:
                self._finish(finish_job, 'flushed')

    def _do_deposit(self, job):
        material = job['material']
        with self._cv:
            current, count = self._chamber_material, self._chamber_count
        if current is not None and (current != material or count >= CHAMBER_CAPACITY):
            self._do_flush()
        self._set_phase('gate')
        self._driver.gate_open()                       # includes the hold: the item is now in the chamber
        with self._cv:
            if self._chamber_material == material:
                self._chamber_count += 1
            else:
                self._chamber_material, self._chamber_count = material, 1
            self._save()
            # Report the drop now so points are awarded while the flap closes;
            # the job still holds the machine until the flap is shut.
            job['status'] = 'dropped'
            self._cv.notify_all()
        self._driver.gate_close()
        with self._cv:
            self._finish(job, 'dropped')
