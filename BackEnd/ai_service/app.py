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

from materials import normalize_material
from hardware import Machine, PlaceholderDriver
from machine_api import PROFILE, create_machine_blueprint

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
# CAMERA — Raspberry Pi CSI camera, with a USB webcam fallback.
# On a machine with neither (e.g. a dev laptop without a webcam)
# CAMERA_AVAILABLE stays False and /capture + /stream degrade to a
# clean "unavailable" response instead of crashing the whole
# service — the rest of the API keeps working as before.
# Logic ported from test_yolo.py (camera+YOLO test rig).
# The compactor (flap, compactor, tilt) is set up further down;
# see hardware.py and machine_api.py.
# ============================================================

CAMERA_AVAILABLE = False
camera = None


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
        # USB_CAMERA_ROTATE=180 in .env for a webcam mounted upside-down;
        # applied here so the preview stream and the frame YOLO classifies
        # are always the same orientation.
        self._rotate_180 = os.environ.get('USB_CAMERA_ROTATE', '0').strip() == '180'

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
        cap.set(cv2.CAP_PROP_FPS, 15)
        # Throwaway reads so AWB/AE settle - only paid once, the first time
        # the device is actually opened, not on every subsequent frame.
        for _ in range(5):
            cap.read()
        self._cap = cap

    def capture_array(self):
        self._ensure_open()
        ok, frame = self._cap.read()
        if not ok:
            # Some cheap UVC webcams fail their internal stream renegotiation
            # ("Failed to set UVC commit control") after being released and
            # reopened many times in a row (exactly what happens across
            # repeated scans, since /capture releases the device after every
            # single shot - see its route). A fresh close+reopen re-runs that
            # negotiation from scratch and recovers most of the time; only
            # raise if it fails twice in a row.
            self.release()
            self._ensure_open()
            ok, frame = self._cap.read()
            if not ok:
                raise RuntimeError('Failed to read a frame from the USB webcam.')
        if self._rotate_180:
            frame = cv2.rotate(frame, cv2.ROTATE_180)
        return frame[:, :, ::-1]  # OpenCV gives BGR -> flip to RGB

    def release(self):
        if self._cap is not None:
            self._cap.release()
            self._cap = None


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
        _cv2_backend = cv2.CAP_DSHOW if os.name == 'nt' else cv2.CAP_V4L2
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

HARDWARE_AVAILABLE = CAMERA_AVAILABLE
print(f"Hardware status: camera={CAMERA_AVAILABLE}")


# ============================================================
# COMPACTOR (2-bin DSME machine) — see hardware.py / machine_api.py.
# HW_DRIVER=placeholder (the only option for now) sleeps for the real
# durations instead of touching GPIO; HW_TIME_SCALE=0.1 speeds it up for
# local dev. Chamber contents survive a restart via chamber_state.json.
# ============================================================
HW_DRIVER = os.environ.get('HW_DRIVER', 'placeholder').strip().lower()
if HW_DRIVER != 'placeholder':
    raise RuntimeError(f"HW_DRIVER={HW_DRIVER!r} is not supported yet; use 'placeholder'.")
HW_TIME_SCALE = float(os.environ.get('HW_TIME_SCALE', '1.0'))

machine = Machine(PlaceholderDriver(time_scale=HW_TIME_SCALE),
                  state_path=str(_HERE / 'chamber_state.json'))
machine.start()
app.register_blueprint(create_machine_blueprint(machine, require_api_key))
print(f"Compactor ready: driver={HW_DRIVER}, time_scale={HW_TIME_SCALE}")


@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'ok',
        'profile': PROFILE,
        'model_loaded': model is not None,
        'model_path': MODEL_PATH,
        'hardware_available': HARDWARE_AVAILABLE,
        'camera_available': CAMERA_AVAILABLE,
        'hardware_driver': HW_DRIVER,
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
        frame_start = time.monotonic()
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
        target_interval = 1.0 / 15.0 if isinstance(camera, _UsbCameraWrapper) else 0.15

        elapsed = time.monotonic() - frame_start
        remaining = target_interval - elapsed

        if remaining > 0:
            time.sleep(remaining)


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
    # time by default, which would otherwise stall /capture, /classify, /deposit
    # for as long as a stream is active. camera_lock still serializes actual
    # hardware access, so this is safe.
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)
