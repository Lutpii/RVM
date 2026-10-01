import unittest
from functools import wraps

from flask import Flask, jsonify, request

from hardware import Machine
from machine_api import PROFILE, create_machine_blueprint
from tests.test_hardware import RecordingDriver


def fake_require_api_key(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        if request.headers.get('X-API-Key') != 'k':
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated


H = {'X-API-Key': 'k'}


class MachineApiTest(unittest.TestCase):
    def setUp(self):
        self.machine = Machine(RecordingDriver())
        self.machine.start()
        app = Flask(__name__)
        app.register_blueprint(create_machine_blueprint(self.machine, fake_require_api_key))
        self.client = app.test_client()

    def test_requires_api_key(self):
        for method, path in (('get', '/state'), ('post', '/deposit'), ('post', '/flush'), ('get', '/flap-check')):
            self.assertEqual(getattr(self.client, method)(path).status_code, 401, path)

    def test_state_reports_profile_and_chamber(self):
        body = self.client.get('/state', headers=H).get_json()
        self.assertEqual(PROFILE, '2bin')
        self.assertEqual(body['profile'], '2bin')
        self.assertEqual(body['chamber_material'], None)
        self.assertNotIn('job', body)

    def test_deposit_then_poll_job(self):
        r = self.client.post('/deposit', json={'material': 'aluminum', 'allow_flush': False}, headers=H)
        self.assertEqual(r.status_code, 200)
        job_id = r.get_json()['job_id']
        self.assertTrue(self.machine.wait_idle(5))
        body = self.client.get(f'/state?job={job_id}', headers=H).get_json()
        self.assertEqual(body['job'], {'id': job_id, 'status': 'dropped'})
        self.assertEqual(body['chamber_material'], 'aluminum')

    def test_material_without_bin_is_400(self):
        r = self.client.post('/deposit', json={'material': 'paper', 'allow_flush': True}, headers=H)
        self.assertEqual(r.status_code, 400)
        self.assertEqual(r.get_json(), {'accepted': False, 'reason': 'not_accepted'})

    def test_only_json_true_allows_a_flush(self):
        self.client.post('/deposit', json={'material': 'aluminum', 'allow_flush': False}, headers=H)
        self.assertTrue(self.machine.wait_idle(5))
        for value in ('true', 'false', 1, None):
            r = self.client.post('/deposit', json={'material': 'plastic', 'allow_flush': value}, headers=H)
            self.assertEqual(r.get_json()['reason'], 'mismatch', repr(value))
        r = self.client.post('/deposit', json={'material': 'plastic', 'allow_flush': True}, headers=H)
        self.assertTrue(r.get_json()['will_flush'])

    def test_flush_and_flap_check(self):
        self.assertIn('job_id', self.client.post('/flush', headers=H).get_json())
        self.assertEqual(self.client.get('/flap-check', headers=H).get_json(), {'empty': True})


if __name__ == '__main__':
    unittest.main()
