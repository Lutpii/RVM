"""HTTP routes for the 2-bin compactor (see hardware.Machine).

Laravel proxies these as /api/hardware/state|deposit|flush|flap-check.
PROFILE is how the kiosk tells this service apart from ai_service_4bin,
which has no /state route at all.
"""
from flask import Blueprint, jsonify, request

PROFILE = '2bin'


def create_machine_blueprint(machine, require_api_key):
    bp = Blueprint('machine', __name__)

    @bp.route('/state', methods=['GET'])
    @require_api_key
    def state():
        return jsonify({'profile': PROFILE, **machine.state(request.args.get('job'))})

    @bp.route('/deposit', methods=['POST'])
    @require_api_key
    def deposit():
        data = request.get_json(silent=True) or {}
        # Only a real JSON true may trigger a ~40 s compact: a string "false"
        # is truthy in Python and must not slip through.
        allow_flush = data.get('allow_flush') is True
        result = machine.deposit(str(data.get('material') or ''), allow_flush)
        return jsonify(result), (400 if result.get('reason') == 'not_accepted' else 200)

    @bp.route('/flush', methods=['POST'])
    @require_api_key
    def flush():
        return jsonify(machine.flush())

    @bp.route('/flap-check', methods=['GET'])
    @require_api_key
    def flap_check():
        return jsonify({'empty': machine.flap_is_empty()})

    return bp
