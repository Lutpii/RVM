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
# best_rs.pt is the model actually in use (7 classes: bricks, cans, glass,
# metal, paper, plastic, wooden). It's listed first on purpose so it wins
# even if an older best.pt/best_exp6.pt happens to also be sitting around.
_MODEL_CANDIDATES = [
    _HERE / 'best_rs.pt',
    _HERE / 'model' / 'best_rs.pt',
    _HERE.parent.parent / 'best_rs.pt',
    _HERE.parent.parent / 'best_exp6.pt',
    _HERE / 'model' / 'best_exp6.pt',
    _HERE.parent.parent / 'best.pt',
    _HERE / 'model' / 'best.pt',
]
# MODEL_PATH in .env overrides the auto-detected candidate list, for swapping
# in a different trained model (e.g. a new experiment) without editing code.
MODEL_PATH = os.environ.get('MODEL_PATH') or next(
    (str(p) for p in _MODEL_CANDIDATES if p.exists()), str(_MODEL_CANDIDATES[0])
)

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
    """Map whatever class name the model was trained with (best_rs.pt's
    bricks/cans/glass/metal/paper/plastic/wooden) to the fixed slug the rest
    of the app expects. cans->aluminum, glass/plastic/paper pass through;
    metal/wooden/bricks have no physical sorting slot and fall back to reject."""
    name = (raw_name or '').lower()
    if 'alumin' in name or 'can' in name:
        return 'aluminum'
    if 'glass' in name:
        return 'glass'
    if 'plastic' in name:
        return 'plastic'
    if 'paper' in name:
        return 'paper'
    if 'metal' in name or 'wood' in name or 'brick' in name:
        return 'reject'
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
# CAMERA_AVAILABLE/SERVOS_AVAILABLE stay False, and /capture +
# /sort degrade to a clean "unavailable" response instead of
# crashing the whole service — the rest of the API keeps working
# as before.
# Logic ported from test_yolo.py (camera+YOLO+servo test rig).
#
# Camera and servos are initialized independently: a Pi with a
# working USB webcam but a disconnected/broken CSI ribbon (or a
# board with no camera driver at all) should still be able to
# capture/classify, and a machine with a camera but no GPIO/servo
# wiring should still be able to preview + classify without the
# sorting step. HARDWARE_AVAILABLE (both true) is kept only for
# /health's summary field.
# ============================================================

CAMERA_AVAILABLE = False
SERVOS_AVAILABLE = False
camera = None
pan_servo = None
tilt_servo = None


class _PiCameraWrapper:
    """Wraps Picamera2 so capture_array() always returns true RGB.

    Picamera2's 'RGB888' format is actually BGR-ordered (OpenCV convention) —
    reversing the channel axis here means every caller (capture/stream) gets
    a consistent RGB array regardless of which camera backend is active,
    instead of each call site needing to know/undo the quirk itself.
    """
    def __init__(self, picam):
        self._picam = picam

    def capture_array(self):
        return self._picam.capture_array()[:, :, ::-1]


class _UsbCameraWrapper:
    """Wraps an OpenCV VideoCapture (USB webcam) behind the same
    capture_array() -> RGB ndarray interface as _PiCameraWrapper, so the
    rest of the app never has to know which camera is actually plugged in.

    Unlike Picamera2 (a dedicated CSI device nothing else on the Pi wants),
    a USB webcam on a dev laptop is also what the browser's own
    getUserMedia() call needs for QR scanning. Windows' DirectShow backend
    locks a webcam exclusively per-process, so the device is opened lazily
    on the first actual capture/stream request - not at app startup - and
    then kept open for smooth continuous preview from then on. This works
    because the QR-scan step (browser camera) and the item-scan step
    (this camera) happen at different points in the flow, not
    simultaneously: by the time a kiosk session reaches the camera-preview
    step, QR scanning for that session has already finished and released
    the browser's hold on the device.

    /capture releases the handle again right after grabbing its frame (see
    its route), so in the normal flow the device is only actually held
    while a preview is on screen or a capture is in flight. The only way
    it's left open longer than that on a dev laptop with one physical
    webcam is a session abandoned mid-preview (browser tab closed during
    the countdown, before /capture ever runs) - /release-camera exists as
    a fallback for that case. Not a concern on the real kiosk, where the
    CSI camera and the QR-scan webcam are two separate physical devices.
    """
    def __init__(self, index, backend):
        self._index = index
        self._backend = backend
        self._cap = None

    def _ensure_open(self):
        if self._cap is not None:
            return
        cap = cv2.VideoCapture(self._index, self._backend)
        if not cap.isOpened():
            raise RuntimeError(f'Could not open USB webcam at index {self._index}.')
        # 320x320 isn't a standard UVC resolution a USB webcam supports
        # natively (unlike the Pi's CSI sensor, which is configured for it
        # directly) - requesting it here forces the driver to negotiate/scale
        # every frame, which is a big part of why the preview felt slow.
        # 640x480 is a resolution virtually every UVC webcam (Logitech C270
        # included) supports natively.
        cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
        # Throwaway reads so AWB/AE settle - only paid once, the first time
        # the device is actually opened, not on every subsequent frame.
        for _ in range(5):
            cap.read()
        self._cap = cap

    def capture_array(self):
        self._ensure_open()
        ok, frame = self._cap.read()
        if not ok:
            raise RuntimeError('Failed to read a frame from the USB webcam.')
        return frame[:, :, ::-1]  # OpenCV gives BGR -> flip to RGB

    def release(self):
        if self._cap is not None:
            self._cap.release()
            self._cap = None


try:
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

    SERVOS_AVAILABLE = True
    print("Sorting servos initialized.")
except Exception as e:
    print(f"Servos not available, /sort will report unavailable: {e}")

try:
    from picamera2 import Picamera2

    _picam = Picamera2()
    _picam.configure(_picam.create_preview_configuration(
        main={'size': (320, 320), 'format': 'RGB888'}
    ))
    _picam.set_controls({'AwbMode': 4})
    _picam.start()
    time.sleep(1)

    camera = _PiCameraWrapper(_picam)
    CAMERA_AVAILABLE = True
    print("Pi Camera (CSI) initialized.")
except Exception as e:
    print(f"Pi Camera not available ({e}); trying a USB webcam fallback...")
    try:
        import cv2

        # USB_CAMERA_INDEX in .env picks a specific /dev/videoN (Linux) or
        # DirectShow index (Windows) when more than one camera is present -
        # e.g. a laptop's built-in webcam taking index 0 ahead of the
        # external one actually meant for scanning. Run
        # `python -c "from pygrabber.dshow_graph import FilterGraph as F; [print(i, n) for i, n in enumerate(F().get_input_devices())]"`
        # (pip install pygrabber) to see which index maps to which device
        # name on Windows.
        _usb_index = int(os.environ.get('USB_CAMERA_INDEX', '0'))
        # cv2.VideoCapture's default backend gives no feedback while it probes
        # for a device, so a missing/slow webcam looks identical to a hang.
        # CAP_DSHOW is faster and more reliable to open on Windows; on
        # Linux/the Pi, passing it is a no-op since OpenCV there ignores an
        # unsupported backend flag and falls back to V4L2 automatically.
        _cv2_backend = cv2.CAP_DSHOW if os.name == 'nt' else 0
        print(f"Probing USB webcam at index {_usb_index}...")
        # Only probe here that the device actually opens, then release it
        # immediately - _UsbCameraWrapper opens the real handle lazily on
        # the first actual capture/stream request instead of at startup
        # (see its docstring for why: avoids holding an exclusive lock on
        # the webcam before the browser's own QR-scan step even runs).
        _usbcam_probe = cv2.VideoCapture(_usb_index, _cv2_backend)
        _probe_ok = _usbcam_probe.isOpened()
        _usbcam_probe.release()
        if not _probe_ok:
            raise RuntimeError(f'No USB webcam found at index {_usb_index}.')

        camera = _UsbCameraWrapper(_usb_index, _cv2_backend)
        CAMERA_AVAILABLE = True
        print(f"USB webcam available at index {_usb_index} (fallback, opens lazily on first use).")
    except Exception as e2:
        print(f"USB webcam also not available, /capture and /stream will report unavailable: {e2}")

HARDWARE_AVAILABLE = CAMERA_AVAILABLE and SERVOS_AVAILABLE
print(f"Hardware status: camera={CAMERA_AVAILABLE}, servos={SERVOS_AVAILABLE}")


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
def drop_back_right():  _drop(POS_RIGHT, ANGLE_BACK,  'Back Right - Paper')


# All 4 physical slots are now spoken for by the 4 accept categories — reject
# (metal/wooden/bricks/unknown) has no slot at all, so /sort leaves the servo
# untouched for any material not in this map instead of defaulting to a drop.
SORTING_MAP = {
    'aluminum': drop_front_right,
    'glass':    drop_front_left,
    'plastic':  drop_back_left,
    'paper':    drop_back_right,
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
        'camera_available': CAMERA_AVAILABLE,
        'servos_available': SERVOS_AVAILABLE,
    })


camera_lock = threading.Lock()

# Generation counter for /stream. A browser dropping the <img> tag doesn't
# reliably abort the underlying MJPEG connection, so a per-stream token is
# used instead of a single shared flag: starting a new stream (or calling
# /capture) bumps the counter, and any older generator loop sees its own
# token no longer matches the current one and exits on its next tick. This
# avoids two overlapping streams (old + new) fighting over camera_lock and
# producing a stuck/black preview after the first scan in a session.
_stream_generation = 0


@app.route('/capture', methods=['POST'])
@require_api_key
def capture():
    if not CAMERA_AVAILABLE:
        return jsonify({'success': False, 'error': 'Camera not available on this device.'}), 503

    # Force any lingering stream to exit its loop (within one tick, ~0.05-0.15s
    # depending on camera type) and release camera_lock, instead of trusting
    # the client actually closed the connection.
    global _stream_generation
    _stream_generation += 1

    with camera_lock:
        frame = camera.capture_array()
        # A USB webcam (unlike the Pi's dedicated CSI camera) is also what
        # the browser's own QR-scan step needs on the same physical device,
        # so release it right after each capture instead of leaving it
        # open indefinitely - matches the Pi cam's countdown -> capture ->
        # off behavior and frees the device (and its "in use" light) for
        # the next QR scan without waiting for this whole process to
        # restart or a separate /release-camera call.
        if isinstance(camera, _UsbCameraWrapper):
            camera.release()
    img = Image.fromarray(frame)
    buf = io.BytesIO()
    img.save(buf, format='JPEG')
    buf.seek(0)

    return send_file(buf, mimetype='image/jpeg', download_name='capture.jpg')


def _generate_mjpeg():
    global _stream_generation
    _stream_generation += 1
    my_generation = _stream_generation
    while _stream_generation == my_generation:
        with camera_lock:
            if not CAMERA_AVAILABLE:
                break
            frame = camera.capture_array()
        img = Image.fromarray(frame)
        buf = io.BytesIO()
        img.save(buf, format='JPEG', quality=70)
        yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buf.getvalue() + b'\r\n')
        # ~6-7 fps on the Pi's CSI camera — plenty for a kiosk preview,
        # light on limited hardware. A dev-laptop USB webcam has CPU to
        # spare, so give it a snappier ~15-20 fps instead of feeling
        # laggy compared to the Pi Cam.
        time.sleep(0.05 if isinstance(camera, _UsbCameraWrapper) else 0.15)


@app.route('/stream')
@require_api_key
def stream():
    # The browser <img> tag can't send a custom header itself, so in production
    # Nginx injects X-API-Key when it proxies /ai-stream -> here (see
    # deploy/nginx-rvm.conf). Direct-to-Flask access without going through that
    # proxy is rejected like every other endpoint.
    if not CAMERA_AVAILABLE:
        return jsonify({'error': 'Camera not available on this device.'}), 503
    return Response(_generate_mjpeg(), mimetype='multipart/x-mixed-replace; boundary=frame')


@app.route('/release-camera', methods=['POST'])
@require_api_key
def release_camera():
    """Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera)
    so a browser QR scan on the same physical device can succeed right
    after a kiosk session's item-scan step ends, instead of waiting for
    this whole process to restart. Dev-laptop-only concern - see
    _UsbCameraWrapper's docstring."""
    global _stream_generation
    _stream_generation += 1  # stop any in-flight preview stream first
    if isinstance(camera, _UsbCameraWrapper):
        with camera_lock:
            camera.release()
    return jsonify({'success': True})


@app.route('/sort', methods=['POST'])
@require_api_key
def sort():
    if not SERVOS_AVAILABLE:
        return jsonify({'success': False, 'error': 'Servos not available on this device.'}), 503

    data = request.get_json(silent=True) or {}
    material = data.get('material', 'reject')

    if servo_busy:
        return jsonify({'success': False, 'error': 'Servo is busy.'}), 409

    func = SORTING_MAP.get(material)
    if func is None:
        # No physical slot for this material (reject/unknown) — leave the servo alone.
        return jsonify({'success': True, 'sorting': material, 'servo_moved': False})

    threading.Thread(target=trigger_servo_thread, args=(func,), daemon=True).start()
    return jsonify({'success': True, 'sorting': material, 'servo_moved': True})


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
