import json
import os
import tempfile
import unittest

from hardware import CHAMBER_CAPACITY, DEPOSIT_SECONDS, FLUSH_SECONDS, Machine, PlaceholderDriver


class RecordingDriver:
    def __init__(self, fail_on=None, startup_result=None):
        self.calls = []
        self.fail_on = fail_on
        self.startup_result = startup_result  # None = ready, a string = why it is not, an exception = raised
        self.stops = 0

    def _do(self, name):
        if name == self.fail_on:
            self.fail_on = None
            raise RuntimeError(f'{name} jammed')
        self.calls.append(name)

    def gate_open(self): self._do('gate_open')
    def gate_close(self): self._do('gate_close')
    def compact(self): self._do('compact')
    def tilt(self, material): self._do(f'tilt:{material}')
    def flap_is_empty(self): return True

    def startup(self):
        if isinstance(self.startup_result, Exception):
            raise self.startup_result
        return self.startup_result

    def emergency_stop(self): self.stops += 1


GATE = ['gate_open', 'gate_close']


class MachineTest(unittest.TestCase):
    def make(self, **kw):
        self.driver = kw.pop('driver', RecordingDriver())
        return Machine(self.driver, **kw)

    def run_all(self, m):
        m.start()
        self.assertTrue(m.wait_idle(5))

    def test_deposit_into_empty_chamber_drops_item(self):
        m = self.make()
        r = m.deposit('aluminum', False)
        self.assertTrue(r['accepted'])
        self.assertFalse(r['will_flush'])
        self.run_all(m)
        self.assertEqual(self.driver.calls, GATE)
        s = m.state(r['job_id'])
        self.assertEqual((s['chamber_material'], s['chamber_count']), ('aluminum', 1))
        self.assertEqual(s['job'], {'id': r['job_id'], 'status': 'dropped'})
        self.assertEqual((s['busy'], s['phase'], s['queue_length']), (False, 'idle', 0))

    def test_same_material_stacks_without_compacting(self):
        m = self.make()
        m.deposit('plastic', False)
        m.deposit('plastic', False)
        self.run_all(m)
        self.assertEqual(self.driver.calls, GATE + GATE)
        self.assertEqual(m.state()['chamber_count'], 2)

    def test_mismatch_without_allow_flush_moves_nothing(self):
        m = self.make()
        m.deposit('aluminum', False)
        self.run_all(m)
        r = m.deposit('plastic', False)
        self.assertEqual(r, {'accepted': False, 'reason': 'mismatch',
                             'chamber_material': 'aluminum', 'chamber_count': 1})
        self.assertTrue(m.wait_idle(5))
        self.assertEqual(self.driver.calls, GATE)
        self.assertEqual(m.state()['chamber_material'], 'aluminum')

    def test_mismatch_with_allow_flush_empties_chamber_then_drops(self):
        m = self.make()
        m.deposit('aluminum', False)
        self.run_all(m)
        r = m.deposit('plastic', True)
        self.assertTrue(r['accepted'])
        self.assertTrue(r['will_flush'])
        self.assertTrue(m.wait_idle(5))
        self.assertEqual(self.driver.calls, GATE + ['compact', 'tilt:aluminum'] + GATE)
        s = m.state(r['job_id'])
        self.assertEqual((s['chamber_material'], s['chamber_count']), ('plastic', 1))
        self.assertEqual(s['job']['status'], 'dropped')

    def test_material_without_bin_is_not_accepted(self):
        m = self.make()
        for material in ('paper', 'glass', 'unknown', 'reject', ''):
            self.assertEqual(m.deposit(material, True), {'accepted': False, 'reason': 'not_accepted'})
        self.assertEqual(m.state()['queue_length'], 0)

    def test_flush_on_empty_chamber_is_a_noop(self):
        m = self.make()
        r = m.flush()
        self.run_all(m)
        self.assertEqual(self.driver.calls, [])
        self.assertEqual(m.state(r['job_id'])['job']['status'], 'flushed')

    def test_flush_compacts_and_tilts_to_the_chamber_material(self):
        m = self.make()
        m.deposit('plastic', False)
        m.flush()
        self.run_all(m)
        self.assertEqual(self.driver.calls, GATE + ['compact', 'tilt:plastic'])
        s = m.state()
        self.assertEqual((s['chamber_material'], s['chamber_count']), (None, 0))

    def test_mismatch_check_uses_chamber_after_queued_jobs(self):
        m = self.make()  # worker not started: everything stays queued
        self.assertTrue(m.deposit('aluminum', False)['accepted'])
        self.assertEqual(m.deposit('plastic', False)['reason'], 'mismatch')
        self.assertTrue(m.deposit('aluminum', False)['accepted'])
        self.assertEqual(m.deposit('plastic', False)['chamber_count'], 2)
        m.flush()
        r = m.deposit('plastic', False)  # chamber will be empty after the queued flush
        self.assertTrue(r['accepted'])
        self.assertFalse(r['will_flush'])
        self.assertTrue(m.state()['busy'])
        self.run_all(m)
        self.assertEqual(self.driver.calls, GATE + GATE + ['compact', 'tilt:aluminum'] + GATE)
        self.assertEqual(m.state()['chamber_material'], 'plastic')

    def test_eta_counts_queued_work(self):
        m = self.make()
        self.assertEqual(m.deposit('aluminum', False)['eta_seconds'], round(DEPOSIT_SECONDS))
        self.assertEqual(m.deposit('plastic', True)['eta_seconds'],
                         round(DEPOSIT_SECONDS + FLUSH_SECONDS + DEPOSIT_SECONDS))

    def test_state_survives_a_restart(self):
        with tempfile.TemporaryDirectory() as tmp:
            path = os.path.join(tmp, 'chamber_state.json')
            m = self.make(state_path=path)
            m.deposit('aluminum', False)
            m.deposit('aluminum', False)
            self.run_all(m)
            again = Machine(RecordingDriver(), state_path=path)
            s = again.state()
            self.assertEqual((s['chamber_material'], s['chamber_count']), ('aluminum', 2))

    def test_corrupt_state_file_loads_as_empty(self):
        with tempfile.TemporaryDirectory() as tmp:
            path = os.path.join(tmp, 'chamber_state.json')
            for content in ('{not json', json.dumps({'chamber_material': 'glass', 'chamber_count': 3}),
                            json.dumps([1, 2])):
                with open(path, 'w', encoding='utf-8') as fh:
                    fh.write(content)
                s = Machine(RecordingDriver(), state_path=path).state()
                self.assertEqual((s['chamber_material'], s['chamber_count']), (None, 0), content)

    def test_interrupted_save_keeps_the_previous_state(self):
        # Simulates power loss mid-write: the old file must stay readable.
        import hardware
        with tempfile.TemporaryDirectory() as tmp:
            path = os.path.join(tmp, 'chamber_state.json')
            m = self.make(state_path=path)
            m.deposit('aluminum', False)
            self.run_all(m)

            real_dump = hardware.json.dump

            def torn_dump(obj, fh, *a, **kw):
                fh.write('{"chamber_mat')
                raise OSError('power lost')

            hardware.json.dump = torn_dump
            try:
                with self.assertLogs('hardware', level='ERROR'):
                    m.deposit('aluminum', False)
                    self.assertTrue(m.wait_idle(5))
            finally:
                hardware.json.dump = real_dump
            s = Machine(RecordingDriver(), state_path=path).state()
            self.assertEqual((s['chamber_material'], s['chamber_count']), ('aluminum', 1))

    def test_no_fault_normally(self):
        m = self.make()
        m.deposit('plastic', False)
        self.run_all(m)
        self.assertIsNone(m.state()['fault'])

    def test_failed_job_locks_the_machine(self):
        # A hardware failure (e.g. tilt not back at the limit switch) must not
        # be driven into again: stop everything, cancel the queue, refuse work.
        m = self.make(driver=RecordingDriver(fail_on='gate_open'))
        bad = m.deposit('plastic', False)
        queued = m.deposit('plastic', False)
        with self.assertLogs('hardware', level='ERROR'):
            self.run_all(m)
        self.assertEqual(m.state(bad['job_id'])['job']['status'], 'failed')
        self.assertEqual(m.state(queued['job_id'])['job']['status'], 'failed')
        self.assertEqual(self.driver.calls, [])  # the queued job never moved anything
        self.assertEqual(self.driver.stops, 1)
        s = m.state()
        self.assertEqual((s['phase'], s['busy'], s['queue_length']), ('idle', False, 0))
        self.assertIn('gate_open jammed', s['fault']['reason'])
        self.assertEqual(s['fault']['job'], 'deposit')

        refused = m.deposit('plastic', False)
        self.assertEqual((refused['accepted'], refused['reason']), (False, 'fault'))
        self.assertEqual(refused['fault'], s['fault'])
        self.assertEqual(m.flush()['reason'], 'fault')
        self.assertTrue(m.wait_idle(1))
        self.assertEqual(self.driver.calls, [])

    def test_failed_flush_locks_the_machine_and_keeps_the_chamber(self):
        m = self.make(driver=RecordingDriver(fail_on='tilt:aluminum'))
        m.deposit('aluminum', False)
        m.flush()
        with self.assertLogs('hardware', level='ERROR'):
            self.run_all(m)
        s = m.state()
        self.assertIsNotNone(s['fault'])
        # unknown where the batch ended up: keep it counted, never assume empty
        self.assertEqual((s['chamber_material'], s['chamber_count']), ('aluminum', 1))

    def test_startup_problem_locks_the_machine(self):
        m = self.make(driver=RecordingDriver(startup_result='Tilt is not at the centre'))
        m.start()
        self.assertEqual(m.state()['fault']['reason'], 'Tilt is not at the centre')
        self.assertEqual(m.state()['fault']['job'], 'startup')
        self.assertEqual(m.deposit('plastic', False)['reason'], 'fault')

    def test_startup_exception_locks_the_machine(self):
        m = self.make(driver=RecordingDriver(startup_result=OSError('GPIO busy')))
        with self.assertLogs('hardware', level='ERROR'):
            m.start()
        self.assertIn('GPIO busy', m.state()['fault']['reason'])
        self.assertEqual(self.driver.stops, 1)

    def test_full_chamber_is_emptied_before_the_next_item(self):
        # The compactor holds at most CHAMBER_CAPACITY items: a 4th item of the
        # same material flushes the chamber first, without asking the user.
        m = self.make()
        for _ in range(CHAMBER_CAPACITY):
            m.deposit('aluminum', False)
        r = m.deposit('aluminum', False)
        self.assertTrue(r['accepted'])
        self.assertTrue(r['will_flush'])
        self.run_all(m)
        self.assertEqual(self.driver.calls,
                         GATE * CHAMBER_CAPACITY + ['compact', 'tilt:aluminum'] + GATE)
        s = m.state()
        self.assertEqual((s['chamber_material'], s['chamber_count']), ('aluminum', 1))

    def test_capacity_is_three(self):
        self.assertEqual(CHAMBER_CAPACITY, 3)

    def test_item_counts_as_dropped_before_the_flap_closes(self):
        # Points may be awarded as soon as the item is in the chamber; the
        # flap closing afterwards must not delay that.
        m = self.make()
        seen = {}

        def on_close():
            seen['job'] = m.state(job_id)['job']['status']
            seen['chamber'] = m.state()['chamber_count']
            # a plastic deposit now sees exactly 1 aluminum in the chamber
            seen['next'] = m.deposit('plastic', False)
        self.driver.gate_close = lambda: (on_close(), self.driver.calls.append('gate_close'))
        job_id = m.deposit('aluminum', False)['job_id']
        self.run_all(m)
        self.assertEqual(seen['job'], 'dropped')
        self.assertEqual(seen['chamber'], 1)
        self.assertEqual(seen['next'], {'accepted': False, 'reason': 'mismatch',
                                        'chamber_material': 'aluminum', 'chamber_count': 1})
        self.assertEqual(m.state()['phase'], 'idle')

    def test_flap_failing_to_close_keeps_the_drop(self):
        m = self.make(driver=RecordingDriver(fail_on='gate_close'))
        r = m.deposit('plastic', False)
        with self.assertLogs('hardware', level='ERROR'):
            self.run_all(m)
        self.assertEqual(m.state(r['job_id'])['job']['status'], 'dropped')
        self.assertEqual((m.state()['busy'], m.state()['chamber_count']), (False, 1))
        self.assertIsNotNone(m.state()['fault'])  # an open flap still needs a technician

    def test_unknown_job_id(self):
        self.assertEqual(self.make().state('nope')['job'], {'id': 'nope', 'status': 'unknown'})


class PlaceholderDriverTest(unittest.TestCase):
    def test_sleeps_scaled_durations(self):
        slept = []
        d = PlaceholderDriver(time_scale=0.5, sleep=slept.append)
        d.gate_open(); d.gate_close(); d.compact(); d.tilt('plastic')
        self.assertEqual(slept, [2.95, 1.95, 8.0, 4.0])
        self.assertTrue(d.flap_is_empty())
        self.assertIsNone(d.startup())
        d.emergency_stop()

    def test_zero_scale_never_sleeps(self):
        slept = []
        d = PlaceholderDriver(time_scale=0, sleep=slept.append)
        d.compact()
        self.assertEqual(slept, [])


if __name__ == '__main__':
    unittest.main()
