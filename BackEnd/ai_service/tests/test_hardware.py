import json
import os
import tempfile
import unittest

from hardware import DEPOSIT_SECONDS, FLUSH_SECONDS, Machine, PlaceholderDriver


class RecordingDriver:
    def __init__(self, fail_on=None):
        self.calls = []
        self.fail_on = fail_on

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

    def test_failed_job_is_reported_and_next_job_still_runs(self):
        m = self.make(driver=RecordingDriver(fail_on='gate_open'))
        bad = m.deposit('plastic', False)
        good = m.deposit('plastic', False)
        with self.assertLogs('hardware', level='ERROR'):
            self.run_all(m)
        self.assertEqual(m.state(bad['job_id'])['job']['status'], 'failed')
        self.assertEqual(m.state(good['job_id'])['job']['status'], 'dropped')
        s = m.state()
        self.assertEqual((s['phase'], s['busy'], s['chamber_count']), ('idle', False, 1))

    def test_unknown_job_id(self):
        self.assertEqual(self.make().state('nope')['job'], {'id': 'nope', 'status': 'unknown'})


class PlaceholderDriverTest(unittest.TestCase):
    def test_sleeps_scaled_durations(self):
        slept = []
        d = PlaceholderDriver(time_scale=0.5, sleep=slept.append)
        d.gate_open(); d.gate_close(); d.compact(); d.tilt('plastic')
        self.assertEqual(slept, [1.95, 0.95, 16.0, 4.0])
        self.assertTrue(d.flap_is_empty())

    def test_zero_scale_never_sleeps(self):
        slept = []
        d = PlaceholderDriver(time_scale=0, sleep=slept.append)
        d.compact()
        self.assertEqual(slept, [])


if __name__ == '__main__':
    unittest.main()
