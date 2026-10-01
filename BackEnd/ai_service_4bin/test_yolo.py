import cv2
from picamera2 import Picamera2
from ultralytics import YOLO
import time
import threading
import numpy as np
import os
from gpiozero import AngularServo
from gpiozero.pins.pigpio import PiGPIOFactory


# ============================================================
# 1. SERVO SETUP
# ============================================================

# Start pigpio daemon if it is not running
if os.system("pgrep pigpiod > /dev/null 2>&1") != 0:
    print("Memulakan pigpio daemon...")
    os.system("sudo pigpiod")
    time.sleep(2)

factory = PiGPIOFactory()

# Pan servo -> GPIO 17
pan_servo = AngularServo(
    17,
    initial_angle=None,
    min_angle=0,
    max_angle=180,
    min_pulse_width=0.0005,
    max_pulse_width=0.0025,
    pin_factory=factory
)

# Tilt servo -> GPIO 27
tilt_servo = AngularServo(
    27,
    initial_angle=None,
    min_angle=0,
    max_angle=180,
    min_pulse_width=0.0005,
    max_pulse_width=0.0025,
    pin_factory=factory
)


# Same angles as the original app.py
POS_CENTER   = 90
POS_RIGHT    = 50
POS_LEFT     = 130

ANGLE_CLOSED = 87
ANGLE_FRONT  = 117
ANGLE_BACK   = 57


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

    while (step > 0 and current < target_angle) or \
          (step < 0 and current > target_angle):
        servo.angle = current
        time.sleep(rest_time)
        current += step

    servo.angle = target_angle


def reset_servo_initial():
    print("[SERVO] Resetting servos ke posisi asal...")

    move_slowly(
        tilt_servo,
        ANGLE_CLOSED,
        ANGLE_CLOSED
    )

    time.sleep(0.2)

    move_slowly(
        pan_servo,
        POS_CENTER,
        POS_CENTER
    )

    time.sleep(0.2)


def drop_front_right():
    print("[SERVO] Front Right - Aluminium")

    move_slowly(
        pan_servo,
        POS_RIGHT,
        POS_CENTER
    )
    time.sleep(0.2)

    move_slowly(
        tilt_servo,
        ANGLE_FRONT,
        ANGLE_CLOSED
    )
    time.sleep(0.8)

    move_slowly(
        tilt_servo,
        ANGLE_CLOSED,
        ANGLE_CLOSED
    )
    time.sleep(0.2)

    move_slowly(
        pan_servo,
        POS_CENTER,
        POS_CENTER
    )
    time.sleep(0.2)


def drop_front_left():
    print("[SERVO] Front Left - Glass")

    move_slowly(
        pan_servo,
        POS_LEFT,
        POS_CENTER
    )
    time.sleep(0.2)

    move_slowly(
        tilt_servo,
        ANGLE_FRONT,
        ANGLE_CLOSED
    )
    time.sleep(0.8)

    move_slowly(
        tilt_servo,
        ANGLE_CLOSED,
        ANGLE_CLOSED
    )
    time.sleep(0.2)

    move_slowly(
        pan_servo,
        POS_CENTER,
        POS_CENTER
    )
    time.sleep(0.2)


def drop_back_left():
    print("[SERVO] Back Left - Plastic")

    move_slowly(
        pan_servo,
        POS_LEFT,
        POS_CENTER
    )
    time.sleep(0.2)

    move_slowly(
        tilt_servo,
        ANGLE_BACK,
        ANGLE_CLOSED
    )
    time.sleep(0.8)

    move_slowly(
        tilt_servo,
        ANGLE_CLOSED,
        ANGLE_CLOSED
    )
    time.sleep(0.2)

    move_slowly(
        pan_servo,
        POS_CENTER,
        POS_CENTER
    )
    time.sleep(0.2)


def drop_back_right():
    print("[SERVO] Back Right - Reject")

    move_slowly(
        pan_servo,
        POS_RIGHT,
        POS_CENTER
    )
    time.sleep(0.2)

    move_slowly(
        tilt_servo,
        ANGLE_BACK,
        ANGLE_CLOSED
    )
    time.sleep(0.8)

    move_slowly(
        tilt_servo,
        ANGLE_CLOSED,
        ANGLE_CLOSED
    )
    time.sleep(0.2)

    move_slowly(
        pan_servo,
        POS_CENTER,
        POS_CENTER
    )
    time.sleep(0.2)


# Same sorting map as the original app.py
SORTING_MAP = {
    0: drop_front_right,   # Aluminium Can
    1: drop_front_left,    # Glass Bottle
    2: drop_back_left,     # Plastic Bottle
}


# ============================================================
# 2. SERVO CONTROL
# ============================================================

servo_lock = threading.Lock()
servo_busy = False
just_finished_servo = False


def trigger_servo_thread(func):
    global servo_busy, just_finished_servo

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
            just_finished_servo = True


# ============================================================
# 3. MAIN CAMERA + YOLO
# ============================================================

print("Memulakan Picamera2 (Nisbah Fokus 320x320)...")

picam2 = Picamera2()

config = picam2.create_preview_configuration(
    main={
        "size": (320, 320),
        "format": "RGB888"
    }
)

picam2.configure(config)
picam2.set_controls({"AwbMode": 4})
picam2.start()

time.sleep(1)


# Same YOLO model as original app.py
MODEL_PATH = "/home/raspi01/Desktop/sdp/best_exp6.pt"

print("Loading YOLO Model...")
print(f"Model: {MODEL_PATH}")

model = YOLO(MODEL_PATH)
model.fuse()


# ============================================================
# 4. DETECTION PARAMETERS
# ============================================================

CONF_RECYCLABLE = 0.6

MIN_CONTOUR_AREA = 500
BG_THRESH_VALUE = 30
MIN_BOX_SIZE = 25
ALPHA_BG = 0.02

PIXEL_TO_CM_RATIO = 0.095

# Smart Reject size filter
MIN_REJECT_W = 3.0
MAX_REJECT_W = 12.0

MIN_REJECT_H = 5.0
MAX_REJECT_H = 25.0

clahe = cv2.createCLAHE(
    clipLimit=3.0,
    tileGridSize=(8, 8)
)

CLASS_NAMES = {
    0: "Aluminium Can",
    1: "Glass Bottle",
    2: "Plastic Bottle"
}


# ============================================================
# 5. INITIAL BACKGROUND
# ============================================================

print("Mengambil rujukan background awal...")
print("Pastikan kawasan kamera kosong.")

time.sleep(2)

bg_frame = picam2.capture_array()

bg_gray = cv2.cvtColor(
    bg_frame,
    cv2.COLOR_RGB2GRAY
)

bg_gray = clahe.apply(bg_gray).astype(np.float32)

bg_gray = cv2.GaussianBlur(
    bg_gray,
    (21, 21),
    0
)


# Reset servo before starting
reset_servo_initial()


# ============================================================
# 6. DETECTION STATE
# ============================================================

item_already_sent = False

CONFIRMATION_FRAMES_ITEM = 3
CONFIRMATION_FRAMES_REJECT = 7

consecutive_frames = 0
current_detected_class = None

reject_consecutive = 0


camera_window_open = True

WINDOW_NAME = "Smart Bin Servo Test - YOLO"

print()
print("================================================")
print("       YOLO + CAMERA + SERVO TEST MODE")
print("================================================")
print()
print("LOGIN       : DISABLED")
print("FLASK       : DISABLED")
print("LCD         : DISABLED")
print("YOLO        : ENABLED")
print("CAMERA      : ENABLED")
print("SERVO       : ENABLED")
print()
print("Model:", MODEL_PATH)
print()
print("Sorting:")
print("  0 = Aluminium -> Front Right")
print("  1 = Glass     -> Front Left")
print("  2 = Plastic   -> Back Left")
print("  Reject        -> Back Right")
print()
print("Keyboard:")
print("  Q = Hide camera")
print("  O = Show camera")
print("  R = Reset background")
print("  ESC = Exit")
print("================================================")
print()


try:

    while True:

        start_time = time.time()

        # ----------------------------------------------------
        # AUTO RESET BACKGROUND AFTER SERVO
        # ----------------------------------------------------

        if just_finished_servo and not servo_busy:

            print(
                "[AUTO-RESET BG] "
                "Membaca keadaan selepas servo selesai..."
            )

            time.sleep(0.5)

            frame_clear = picam2.capture_array()

            gray_clear = cv2.cvtColor(
                frame_clear,
                cv2.COLOR_RGB2GRAY
            )

            gray_clear = clahe.apply(
                gray_clear
            ).astype(np.float32)

            bg_gray = cv2.GaussianBlur(
                gray_clear,
                (21, 21),
                0
            )

            just_finished_servo = False

            # Allow another object
            item_already_sent = False
            consecutive_frames = 0
            reject_consecutive = 0
            current_detected_class = None

            continue


        # ----------------------------------------------------
        # CAMERA FRAME
        # ----------------------------------------------------

        frame = picam2.capture_array()

        annotated_frame = cv2.cvtColor(
            frame,
            cv2.COLOR_RGB2BGR
        )


        # ----------------------------------------------------
        # KEYBOARD
        # ----------------------------------------------------

        key = cv2.waitKey(1) & 0xFF

        if key == ord("q") and camera_window_open:

            camera_window_open = False

            print("[CAM] Camera hidden.")

        elif key == ord("o") and not camera_window_open:

            camera_window_open = True

            print("[CAM] Window reopened.")

        elif key == ord("r"):

            gray_now = cv2.cvtColor(
                frame,
                cv2.COLOR_RGB2GRAY
            )

            gray_now = clahe.apply(
                gray_now
            ).astype(np.float32)

            bg_gray = cv2.GaussianBlur(
                gray_now,
                (21, 21),
                0
            )

            item_already_sent = False
            consecutive_frames = 0
            reject_consecutive = 0
            current_detected_class = None

            print(
                "[BG MANUAL RESET] "
                "Background diset semula bersih!"
            )

        elif key == 27:
            break


        # ----------------------------------------------------
        # SERVO BUSY
        # ----------------------------------------------------

        if servo_busy:

            if camera_window_open:

                cv2.putText(
                    annotated_frame,
                    "SERVO MOVING - PLEASE WAIT",
                    (10, 200),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.65,
                    (0, 255, 255),
                    2
                )

                cv2.imshow(
                    WINDOW_NAME,
                    annotated_frame
                )

            continue


        # ----------------------------------------------------
        # BACKGROUND SUBTRACTION
        # ----------------------------------------------------

        gray = cv2.cvtColor(
            frame,
            cv2.COLOR_RGB2GRAY
        )

        gray_enhanced = clahe.apply(
            gray
        ).astype(np.float32)

        gray_blurred = cv2.GaussianBlur(
            gray_enhanced,
            (21, 21),
            0
        )

        diff = cv2.absdiff(
            bg_gray,
            gray_blurred
        )

        _, thresh = cv2.threshold(
            diff,
            BG_THRESH_VALUE,
            255,
            cv2.THRESH_BINARY
        )

        thresh_u8 = thresh.astype(np.uint8)

        kernel = cv2.getStructuringElement(
            cv2.MORPH_ELLIPSE,
            (9, 9)
        )

        thresh_u8 = cv2.morphologyEx(
            thresh_u8,
            cv2.MORPH_OPEN,
            kernel
        )

        thresh_u8 = cv2.morphologyEx(
            thresh_u8,
            cv2.MORPH_CLOSE,
            kernel
        )

        contours, _ = cv2.findContours(
            thresh_u8,
            cv2.RETR_EXTERNAL,
            cv2.CHAIN_APPROX_SIMPLE
        )

        valid_contours = [
            c for c in contours
            if cv2.contourArea(c) > MIN_CONTOUR_AREA
        ]

        object_present = len(valid_contours) > 0


        # ----------------------------------------------------
        # YOLO DETECTION
        # ----------------------------------------------------

        if object_present:

            results = model.predict(
                frame,
                imgsz=320,
                verbose=False,
                conf=CONF_RECYCLABLE,
                iou=0.45
            )

            if len(results[0].boxes) > 0:

                # --------------------------------------------
                # YOLO DETECTED
                # --------------------------------------------

                annotated_frame = results[0].plot()

                first_box = results[0].boxes[0]

                first_cls_id = int(
                    first_box.cls[0]
                )

                conf_val = float(
                    first_box.conf[0]
                )

                x1, y1, x2, y2 = (
                    first_box.xyxy[0].tolist()
                )

                bw = x2 - x1
                bh = y2 - y1

                lebar_cm = (
                    bw * PIXEL_TO_CM_RATIO
                )

                panjang_cm = (
                    bh * PIXEL_TO_CM_RATIO
                )


                # Draw physical size
                cv2.putText(
                    annotated_frame,
                    f"H:{panjang_cm:.1f}cm "
                    f"W:{lebar_cm:.1f}cm",
                    (
                        int(x1),
                        max(int(y1) - 10, 15)
                    ),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.4,
                    (255, 0, 255),
                    2
                )


                if (
                    bw > MIN_BOX_SIZE
                    and bh > MIN_BOX_SIZE
                ):

                    if not item_already_sent:

                        reject_consecutive = 0

                        if (
                            first_cls_id
                            == current_detected_class
                        ):

                            consecutive_frames += 1

                        else:

                            current_detected_class = (
                                first_cls_id
                            )

                            consecutive_frames = 1


                        item_name = CLASS_NAMES.get(
                            first_cls_id,
                            "Unknown"
                        )

                        print(
                            f"[SCANNING] "
                            f"{item_name} | "
                            f"Confidence: {conf_val:.2f} | "
                            f"Stabil: "
                            f"({consecutive_frames}/"
                            f"{CONFIRMATION_FRAMES_ITEM})"
                        )


                        # ------------------------------------
                        # TARGET CONFIRMED
                        # ------------------------------------

                        if (
                            consecutive_frames
                            >= CONFIRMATION_FRAMES_ITEM
                        ):

                            item_type = CLASS_NAMES.get(
                                first_cls_id,
                                "Unknown"
                            )

                            print()
                            print(
                                "================================"
                            )
                            print(
                                f"[TARGET LOCKED] "
                                f"{item_type}"
                            )
                            print(
                                f"Confidence: "
                                f"{conf_val:.2f}"
                            )
                            print(
                                f"Size: "
                                f"{panjang_cm:.1f} x "
                                f"{lebar_cm:.1f} cm"
                            )
                            print(
                                "================================"
                            )


                            item_already_sent = True

                            consecutive_frames = 0
                            current_detected_class = None


                            # Find servo function
                            servo_func = SORTING_MAP.get(
                                first_cls_id
                            )


                            if servo_func is not None:

                                threading.Thread(
                                    target=trigger_servo_thread,
                                    args=(servo_func,),
                                    daemon=True
                                ).start()

            else:

                # --------------------------------------------
                # YOLO FAILED -> SMART REJECT
                # --------------------------------------------

                actual_reject_triggered = False

                for cnt in valid_contours:

                    x, y, w, h = cv2.boundingRect(
                        cnt
                    )

                    w_cm = (
                        w * PIXEL_TO_CM_RATIO
                    )

                    h_cm = (
                        h * PIXEL_TO_CM_RATIO
                    )


                    if (
                        MIN_REJECT_W
                        <= w_cm
                        <= MAX_REJECT_W
                        and
                        MIN_REJECT_H
                        <= h_cm
                        <= MAX_REJECT_H
                    ):

                        actual_reject_triggered = True

                        cv2.rectangle(
                            annotated_frame,
                            (x, y),
                            (x + w, y + h),
                            (0, 0, 255),
                            3
                        )

                        cv2.putText(
                            annotated_frame,
                            f"REJECT "
                            f"{h_cm:.1f}x{w_cm:.1f}cm",
                            (x, max(y - 15, 15)),
                            cv2.FONT_HERSHEY_SIMPLEX,
                            0.7,
                            (0, 0, 255),
                            2
                        )


                if actual_reject_triggered:

                    if not item_already_sent:

                        reject_consecutive += 1

                        consecutive_frames = 0
                        current_detected_class = None

                        print(
                            f"[REJECT SCANNING] "
                            f"{reject_consecutive}/"
                            f"{CONFIRMATION_FRAMES_REJECT}"
                        )


                        if (
                            reject_consecutive
                            >= CONFIRMATION_FRAMES_REJECT
                        ):

                            print()
                            print(
                                "[REJECT LOCKED] "
                                "Objek asing dikesan."
                            )
                            print()


                            item_already_sent = True

                            reject_consecutive = 0


                            threading.Thread(
                                target=trigger_servo_thread,
                                args=(drop_back_right,),
                                daemon=True
                            ).start()

                else:

                    item_already_sent = False
                    consecutive_frames = 0
                    reject_consecutive = 0
                    current_detected_class = None

                    cv2.accumulateWeighted(
                        gray_blurred,
                        bg_gray,
                        ALPHA_BG
                    )


        else:

            # ------------------------------------------------
            # NO OBJECT
            # ------------------------------------------------

            item_already_sent = False

            consecutive_frames = 0
            reject_consecutive = 0
            current_detected_class = None

            cv2.accumulateWeighted(
                gray_blurred,
                bg_gray,
                ALPHA_BG
            )


        # ----------------------------------------------------
        # GUI
        # ----------------------------------------------------

        if camera_window_open:

            fps = 1.0 / max(
                time.time() - start_time,
                1e-6
            )

            cv2.putText(
                annotated_frame,
                f"FPS: {fps:.1f} | "
                f"Motion: {object_present}",
                (10, 300),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.5,
                (0, 255, 0),
                1
            )

            cv2.putText(
                annotated_frame,
                "YOLO + SERVO TEST MODE",
                (10, 25),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.55,
                (0, 255, 255),
                2
            )

            cv2.imshow(
                WINDOW_NAME,
                annotated_frame
            )

        else:

            blank_screen = np.zeros(
                (150, 400, 3),
                dtype=np.uint8
            )

            cv2.putText(
                blank_screen,
                "CAMERA HIDDEN",
                (20, 50),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.7,
                (0, 0, 255),
                2
            )

            cv2.imshow(
                WINDOW_NAME,
                blank_screen
            )


except KeyboardInterrupt:

    print("\n[STOP] Shutting down...")


finally:

    print("[EXIT] Cleaning up...")

    try:
        picam2.stop()
    except Exception:
        pass

    cv2.destroyAllWindows()

    try:
        pan_servo.detach()
        tilt_servo.detach()
    except Exception:
        pass

    print("[EXIT] Servo test selesai.")
