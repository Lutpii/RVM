import os
import io
import time
import threading
import pathlib
from dotenv import load_dotenv
from flask import Flask, request, jsonify, Response, send_file
from flask_cors import CORS
from ultralytics import YOLO
from PIL import Image

load_dotenv()

app = Flask(__name__)
# Reject oversized uploads at the WSGI layer before they ever reach PIL/YOLO —
# closes a straightforward DoS vector (large-file decode + inference cost) on
# hardware with little to spare (a Raspberry Pi). 8 MB comfortably covers a
# real camera-capture JPEG.
app.config['MAX_CONTENT_LENGTH'] = 8 * 1024 * 1024

# No fallback default — a missing key must fail loudly at startup rather than
# silently accept a value that's public knowledge from the repo. (A bare
# `os.environ.get('AI_API_KEY')` with no default would be worse than the old
# hardcoded one: request.headers.get() also returns None for a request with no
# X-API-Key header at all, so None != None would pass require_api_key() for
# EVERY unauthenticated caller.)
API_KEY = os.environ.get('AI_API_KEY')
if not API_KEY:
    raise RuntimeError(
        "AI_API_KEY is not set. Create ai_service/.env with AI_API_KEY=<a random "
        "secret> matching BackEnd/.env's AI_SERVICE_KEY (see ai_service/.env.example)."
    )

# Restrict to the actual frontend origin(s) instead of CORS(app)'s default '*'
# — comma-separated in .env so a Pi/kiosk deployment can list its own hostname.
_allowed_origins = [o.strip() for o in os.environ.get(
    'ALLOWED_ORIGINS', 'https://localhost:5173,https://localhost:4173'
).split(',') if o.strip()]
CORS(app, origins=_allowed_origins)

_HERE = pathlib.Path(__file__).parent
# best_exp6.pt is the model actually in use (3 classes: aluminium can, glass
# bottle, plastic bottle — no paper). It's listed first on purpose so it wins
# even if an older best.pt happens to also be sitting in the repo root.
_MODEL_CANDIDATES = [
    _HERE.parent.parent / 'best_exp6.pt',
    _HERE / 'model' / 'best_exp6.pt',
    _HERE.parent.parent / 'best.pt',
    _HERE / 'model' / 'best.pt',
]
MODEL_PATH = next((str(p) for p in _MODEL_CANDIDATES if p.exists()), str(_MODEL_CANDIDATES[0]))

_FALLBACK_CLASSES = ['aluminum', 'plastic', 'glass', 'paper']

model = None
CLASSES = _FALLBACK_CLASSES

if os.path.exists(MODEL_PATH):
    print(f"Loading model from {MODEL_PATH}")
    model = YOLO(MODEL_PATH)
    # Use class names from the trained model (preserves training label order)
    if hasattr(model, 'names') and model.names:
        CLASSES = [model.names[i] for i in sorted(model.names.keys())]
    print(f"Model loaded. Classes: {CLASSES}")
else:
    print(f"WARNING: Model not found at {MODEL_PATH}. Running in mock mode.")


def normalize_material(raw_name: str) -> str:
    """Map whatever class name the model was trained with (e.g. 'aluminium can',
    'plastic bottle') to the fixed slug the rest of the app expects."""
    name = (raw_name or '').lower()
    if 'alumin' in name:
        return 'aluminum'
    if 'glass' in name:
        return 'glass'
    if 'plastic' in name:
        return 'plastic'
    if 'paper' in name:
        return 'paper'
    return 'unknown'


def require_api_key(f):
    from functools import wraps
    @wraps(f)
    def decorated(*args, **kwargs):
        key = request.headers.get('X-API-Key')
        if key != API_KEY:
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated


# ============================================================
# HARDWARE (camera + sorting servos) — Raspberry Pi only.
# On any other machine (e.g. a dev laptop) these imports fail,
# HARDWARE_AVAILABLE stays False, and /capture + /sort degrade
# to a clean "unavailable" response instead of crashing the
# whole service — the rest of the API keeps working as before.
# Logic ported from test_yolo.py (camera+YOLO+servo test rig).
# ============================================================

HARDWARE_AVAILABLE = False
camera = None
pan_servo = None
tilt_servo = None

try:
    from picamera2 import Picamera2
    from gpiozero import AngularServo
    from gpiozero.pins.pigpio import PiGPIOFactory

    if os.system("pgrep pigpiod > /dev/null 2>&1") != 0:
        os.system("sudo pigpiod")
        time.sleep(2)

    _pin_factory = PiGPIOFactory()

    pan_servo = AngularServo(
        17, initial_angle=None, min_angle=0, max_angle=180,
        min_pulse_width=0.0005, max_pulse_width=0.0025, pin_factory=_pin_factory,
    )
    tilt_servo = AngularServo(
        27, initial_angle=None, min_angle=0, max_angle=180,
        min_pulse_width=0.0005, max_pulse_width=0.0025, pin_factory=_pin_factory,
    )

    camera = Picamera2()
    camera.configure(camera.create_preview_configuration(
        main={'size': (320, 320), 'format': 'RGB888'}
    ))
    camera.set_controls({'AwbMode': 4})
    camera.start()
    time.sleep(1)

    HARDWARE_AVAILABLE = True
    print("Hardware (camera + servos) initialized.")
except Exception as e:
    print(f"Hardware not available, running in software-only mode: {e}")


POS_CENTER, POS_RIGHT, POS_LEFT = 90, 50, 130
ANGLE_CLOSED, ANGLE_FRONT, ANGLE_BACK = 87, 117, 57

servo_lock = threading.Lock()
servo_busy = False


def move_slowly(servo, target_angle, default_angle, delay_time=0.010):
    current_angle = servo.angle
    if current_angle is None:
        current_angle = default_angle
        servo.angle = current_angle
        time.sleep(0.1)

    current_angle = float(current_angle)
    target_angle = float(target_angle)
    if current_angle == target_angle:
        return

    step = 0.6 if current_angle < target_angle else -0.6
    rest_time = delay_time / 2.0
    current = current_angle
    while (step > 0 and current < target_angle) or (step < 0 and current > target_angle):
        servo.angle = current
        time.sleep(rest_time)
        current += step
    servo.angle = target_angle


def reset_servo_initial():
    move_slowly(tilt_servo, ANGLE_CLOSED, ANGLE_CLOSED)
    time.sleep(0.2)
    move_slowly(pan_servo, POS_CENTER, POS_CENTER)
    time.sleep(0.2)


def _drop(pan_pos, tilt_angle, label):
    print(f"[SERVO] {label}")
    move_slowly(pan_servo, pan_pos, POS_CENTER)
    time.sleep(0.2)
    move_slowly(tilt_servo, tilt_angle, ANGLE_CLOSED)
    time.sleep(0.8)
    move_slowly(tilt_servo, ANGLE_CLOSED, ANGLE_CLOSED)
    time.sleep(0.2)
    move_slowly(pan_servo, POS_CENTER, POS_CENTER)
    time.sleep(0.2)


def drop_front_right(): _drop(POS_RIGHT, ANGLE_FRONT, 'Front Right - Aluminum')
def drop_front_left():  _drop(POS_LEFT,  ANGLE_FRONT, 'Front Left - Glass')
def drop_back_left():   _drop(POS_LEFT,  ANGLE_BACK,  'Back Left - Plastic')
def drop_back_right():  _drop(POS_RIGHT, ANGLE_BACK,  'Back Right - Reject')


# Paper has no physical sorting slot on the current hardware — falls back to Reject.
SORTING_MAP = {
    'aluminum': drop_front_right,
    'glass':    drop_front_left,
    'plastic':  drop_back_left,
}


def trigger_servo_thread(func):
    global servo_busy
    with servo_lock:
        if servo_busy:
            return
        servo_busy = True
    try:
        func()
        reset_servo_initial()
    finally:
        with servo_lock:
            servo_busy = False


@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'ok',
        'model_loaded': model is not None,
        'model_path': MODEL_PATH,
        'hardware_available': HARDWARE_AVAILABLE,
    })


camera_lock = threading.Lock()

# Signals an in-flight /stream generator to stop. A browser dropping the
# <img> tag doesn't reliably abort the underlying MJPEG connection, so
# without this a stale stream can hold camera_lock forever and make
# /capture hang indefinitely waiting for it.
_stream_active = threading.Event()


@app.route('/capture', methods=['POST'])
@require_api_key
def capture():
    if not HARDWARE_AVAILABLE:
        return jsonify({'success': False, 'error': 'Camera not available on this device.'}), 503

    # Force any lingering stream to exit its loop (within one ~0.15s tick)
    # and release camera_lock, instead of trusting the client actually
    # closed the connection.
    _stream_active.clear()

    with camera_lock:
        frame = camera.capture_array()
    img = Image.fromarray(frame)
    buf = io.BytesIO()
    img.save(buf, format='JPEG')
    buf.seek(0)

    return send_file(buf, mimetype='image/jpeg', download_name='capture.jpg')


def _generate_mjpeg():
    _stream_active.set()
    try:
        while _stream_active.is_set():
            with camera_lock:
                if not HARDWARE_AVAILABLE:
                    break
                frame = camera.capture_array()
            # Picamera2's 'RGB888' stream is actually ordered BGR (OpenCV convention) —
            # reverse the channel axis so the preview shows correct on-screen colors.
            # /capture and /classify are left untouched since detection already works.
            img = Image.fromarray(frame[:, :, ::-1])
            buf = io.BytesIO()
            img.save(buf, format='JPEG', quality=70)
            yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buf.getvalue() + b'\r\n')
            time.sleep(0.15)  # ~6-7 fps — plenty for a kiosk preview, light on CPU
    finally:
        _stream_active.clear()


@app.route('/stream')
@require_api_key
def stream():
    # The browser <img> tag can't send a custom header itself, so in production
    # Nginx injects X-API-Key when it proxies /ai-stream -> here (see
    # deploy/nginx-rvm.conf). Direct-to-Flask access without going through that
    # proxy is rejected like every other endpoint.
    if not HARDWARE_AVAILABLE:
        return jsonify({'error': 'Camera not available on this device.'}), 503
    return Response(_generate_mjpeg(), mimetype='multipart/x-mixed-replace; boundary=frame')


@app.route('/sort', methods=['POST'])
@require_api_key
def sort():
    if not HARDWARE_AVAILABLE:
        return jsonify({'success': False, 'error': 'Servos not available on this device.'}), 503

    data = request.get_json(silent=True) or {}
    material = data.get('material', 'reject')

    if servo_busy:
        return jsonify({'success': False, 'error': 'Servo is busy.'}), 409

    func = SORTING_MAP.get(material, drop_back_right)
    threading.Thread(target=trigger_servo_thread, args=(func,), daemon=True).start()
    return jsonify({'success': True, 'sorting': material})


@app.route('/classify', methods=['POST'])
@require_api_key
def classify():
    if 'image' not in request.files:
        return jsonify({'error': 'No image provided'}), 400

    file = request.files['image']
    if file.mimetype not in ('image/jpeg', 'image/png'):
        return jsonify({'error': 'Unsupported file type.'}), 400

    try:
        img = Image.open(io.BytesIO(file.read())).convert('RGB')
    except Exception:
        return jsonify({'error': 'Could not read image.'}), 400

    if model is None:
        import random
        mat = random.choice(CLASSES)
        conf = round(random.uniform(0.70, 0.99), 2)
        return jsonify({'material': mat, 'confidence': conf, 'mock': True})

    results = model(img)[0]

    if results.boxes is None or len(results.boxes) == 0:
        return jsonify({'material': 'unknown', 'confidence': 0.0, 'predictions': []})

    best_box = max(results.boxes, key=lambda b: float(b.conf[0]))
    class_id = int(best_box.cls[0])
    confidence = float(best_box.conf[0])
    material = normalize_material(CLASSES[class_id]) if class_id < len(CLASSES) else 'unknown'

    if confidence < 0.6 or material == 'unknown':
        return jsonify({'material': 'unknown', 'confidence': round(confidence, 4), 'predictions': []})

    img_w, img_h = img.size
    predictions = []
    for box in results.boxes:
        cid = int(box.cls[0])
        xyxy = box.xyxy[0].tolist()
        predictions.append({
            'material': normalize_material(CLASSES[cid]) if cid < len(CLASSES) else 'unknown',
            'confidence': round(float(box.conf[0]), 4),
            'bbox': {
                'x1': round(xyxy[0] / img_w, 4),
                'y1': round(xyxy[1] / img_h, 4),
                'x2': round(xyxy[2] / img_w, 4),
                'y2': round(xyxy[3] / img_h, 4),
            },
        })

    return jsonify({
        'material': material,
        'confidence': round(confidence, 4),
        'predictions': predictions,
    })


if __name__ == '__main__':
    # threaded=True is required — the MJPEG /stream endpoint holds a connection
    # open continuously, and Flask's dev server only handles one request at a
    # time by default, which would otherwise stall /capture, /classify, /sort
    # for as long as a stream is active. camera_lock still serializes actual
    # hardware access, so this is safe.
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)
