import os
import io
import pathlib
from flask import Flask, request, jsonify
from flask_cors import CORS
from ultralytics import YOLO
from PIL import Image

app = Flask(__name__)
CORS(app)

API_KEY = os.environ.get('AI_API_KEY', 'rvm_ai_secret_key_2024')

_HERE = pathlib.Path(__file__).parent
_BEST_PT = _HERE.parent.parent / 'best.pt'
_FALLBACK = _HERE / 'model' / 'best.pt'
MODEL_PATH = str(_BEST_PT) if _BEST_PT.exists() else str(_FALLBACK)

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


@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'ok',
        'model_loaded': model is not None,
        'model_path': MODEL_PATH,
    })


@app.route('/classify', methods=['POST'])
@require_api_key
def classify():
    if 'image' not in request.files:
        return jsonify({'error': 'No image provided'}), 400

    file = request.files['image']
    img = Image.open(io.BytesIO(file.read())).convert('RGB')

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
    material = CLASSES[class_id] if class_id < len(CLASSES) else 'unknown'

    if confidence < 0.6:
        return jsonify({'material': 'unknown', 'confidence': round(confidence, 4), 'predictions': []})

    img_w, img_h = img.size
    predictions = []
    for box in results.boxes:
        cid = int(box.cls[0])
        xyxy = box.xyxy[0].tolist()
        predictions.append({
            'material': CLASSES[cid] if cid < len(CLASSES) else 'unknown',
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
    app.run(host='0.0.0.0', port=5000, debug=False)
