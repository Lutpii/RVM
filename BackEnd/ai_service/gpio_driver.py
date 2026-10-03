"""Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio).

Ported from DSME/Code/rvm_combined.py, the bench script the pins, angles and
timings were tuned with on the machine. Same moves, same safety rules:

- Two flap servos move together: GPIO 22 (a 360-degree servo) and GPIO 27
  (a 180-degree servo, mounted the other way round).
- Compactor and tilt motors are driven through relays. Only one relay is ever
  on: every move switches all relays off and waits RELAY_DEAD_TIME first, so
  a motor never gets both directions at once.
- The tilt returns until the limit switch at its centre is pressed, but never
  longer than TILT_HOME_TIMEOUT. Any problem raises HardwareError with every
  relay already off; hardware.Machine then locks the machine.

gpiozero is imported only when the driver is created, so the service still
runs on a laptop (HW_DRIVER=placeholder) without GPIO libraries. On a Pi,
gpiozero picks the lgpio backend. Only one process can own the pins: stop
rvm-ai before running rvm_combined.py by hand.
"""
import logging
import time

from hardware import (COMPACT_IN, COMPACT_OUT, GATE_OPEN_HOLD, GATE_SETTLE, GATE_STEP_DELAY,
                      GATE_STEPS, TILT_HOLD, TILT_HOME_TIMEOUT, TILT_MOVE)

log = logging.getLogger(__name__)

# Pins (BCM numbering), as wired on the DSME machine.
SERVO_PIN = 22          # flap servo 1 (360 degrees, 50 Hz PWM)
SERVO2_PIN = 27         # flap servo 2 (180 degrees), moves together with servo 1
COMPACT_IN_PIN = 23     # relay: compactor presses
COMPACT_OUT_PIN = 24    # relay: compactor returns
TILT_PIN_5 = 5          # relay: tilt direction 1
TILT_PIN_6 = 6          # relay: tilt direction 2
LIMIT_SWITCH_PIN = 12   # limit switch at the tilt centre; pressed = tilt is home

# Flap angles. Servo 1: closed 180, open 360. Servo 2: closed 90, open 0.
SERVO_MAX_ANGLE = 360
GATE_CLOSED_ANGLE = 180
GATE_OPEN_ANGLE = 360
SERVO2_MAX_ANGLE = 180
GATE2_CLOSED_ANGLE = 90
GATE2_OPEN_ANGLE = 0

RELAY_DEAD_TIME = 0.2   # all relays off this long before a motor changes direction

# Material -> (relay that tilts out to its bin, relay that brings it back).
TILT_DIRECTION = {
    'plastic': (TILT_PIN_5, TILT_PIN_6),
    'aluminum': (TILT_PIN_6, TILT_PIN_5),
}

RELAY_NAMES = {
    COMPACT_IN_PIN: 'compact in',
    COMPACT_OUT_PIN: 'compact out',
    TILT_PIN_5: 'tilt GPIO 5',
    TILT_PIN_6: 'tilt GPIO 6',
}


class HardwareError(RuntimeError):
    """A move failed (e.g. the tilt never reached its limit switch). All relays are off."""


class GpioDriver:
    def __init__(self, time_scale=1.0, pin_factory=None, sleep=time.sleep):
        from gpiozero import AngularServo, Button, OutputDevice

        self._scale = time_scale
        self._sleep = sleep
        # 0.5-2.5 ms pulses (2.5-12.5 % duty), as in DSME's servo.py / rvm.py.
        # initial_angle=None: no PWM until the first move, so nothing jerks on start.
        self._servo = AngularServo(SERVO_PIN, min_angle=0, max_angle=SERVO_MAX_ANGLE,
                                   min_pulse_width=0.0005, max_pulse_width=0.0025,
                                   initial_angle=None, pin_factory=pin_factory)
        self._servo2 = AngularServo(SERVO2_PIN, min_angle=0, max_angle=SERVO2_MAX_ANGLE,
                                    min_pulse_width=0.0005, max_pulse_width=0.0025,
                                    initial_angle=None, pin_factory=pin_factory)
        self._gate = 0.0  # flap position: 0 = closed, 1 = open
        # Relays are active HIGH and off from the start, as in the DSME scripts.
        self._relays = {pin: OutputDevice(pin, active_high=True, initial_value=False,
                                          pin_factory=pin_factory)
                        for pin in (COMPACT_IN_PIN, COMPACT_OUT_PIN, TILT_PIN_5, TILT_PIN_6)}
        # Internal pull-up: pressed connects the pin to GND. 50 ms debounce.
        self._limit = Button(LIMIT_SWITCH_PIN, pull_up=True, bounce_time=0.05,
                             pin_factory=pin_factory)

    # ---------- Machine interface ----------

    def startup(self):
        """Relays off, flap closed, then check the tilt is home. Returns why
        the machine must not run, or None when it is ready."""
        self._all_off()
        self._move_gate(0.0, force=True)
        if not self._limit.is_pressed:
            return 'Tilt is not at its centre (limit switch not pressed). Check the tilt, then restart the machine.'
        log.info('Hardware ready: flap closed, tilt at its centre')
        return None

    def emergency_stop(self):
        """Stop every motor and close the flap. Called after any failure."""
        self._all_off()
        self._move_gate(0.0, force=True)

    def gate_open(self):
        """Open the flap and hold it so the item drops into the compactor."""
        log.info('Flap open')
        self._move_gate(1.0)
        self._wait(GATE_OPEN_HOLD)

    def gate_close(self):
        log.info('Flap close')
        self._move_gate(0.0)

    def compact(self):
        """Press the batch, then bring the compactor back."""
        log.info('Compact: in %ss, out %ss', COMPACT_IN, COMPACT_OUT)
        self._run_relay(COMPACT_IN_PIN, COMPACT_IN)
        self._run_relay(COMPACT_OUT_PIN, COMPACT_OUT)

    def tilt(self, material):
        """Tilt to the material's bin, hold, then return to the limit switch."""
        out_pin, back_pin = TILT_DIRECTION[material]
        log.info('Tilt to the %s bin (GPIO %s)', material, out_pin)
        self._run_relay(out_pin, TILT_MOVE)
        if self._limit.is_pressed:  # still home = the tilt never moved
            raise HardwareError('Tilt did not leave its centre (limit switch still pressed). Check the tilt motor and relay.')
        self._wait(TILT_HOLD)
        self._home_tilt(back_pin)

    def flap_is_empty(self):
        return True  # no flap sensor yet

    def close(self):
        """Release the pins (tests and shutdown)."""
        try:
            self.emergency_stop()
        finally:
            for dev in (*self._relays.values(), self._limit, self._servo, self._servo2):
                dev.close()

    # ---------- internals ----------

    def _wait(self, seconds):
        if self._scale > 0:
            self._sleep(seconds * self._scale)

    def _all_off(self):
        for relay in self._relays.values():
            relay.off()

    def _run_relay(self, pin, seconds):
        """One relay on for `seconds`; all others off first (interlock)."""
        self._all_off()
        self._wait(RELAY_DEAD_TIME)
        self._relays[pin].on()
        log.info('  GPIO %s on (%s)', pin, RELAY_NAMES[pin])
        try:
            self._wait(seconds)
        finally:
            self._relays[pin].off()  # always off, also on errors

    def _home_tilt(self, back_pin):
        """Run the tilt back until the limit switch is pressed, at most TILT_HOME_TIMEOUT."""
        self._all_off()
        self._wait(RELAY_DEAD_TIME)
        self._relays[back_pin].on()
        log.info('  GPIO %s on (%s), back to the limit switch', back_pin, RELAY_NAMES[back_pin])
        try:
            deadline = time.monotonic() + TILT_HOME_TIMEOUT * self._scale
            while not self._limit.is_pressed:
                if time.monotonic() > deadline:
                    raise HardwareError('Tilt did not return to its limit switch in time. Check the switch and its wiring.')
                time.sleep(0.01)
        finally:
            self._relays[back_pin].off()
        log.info('  Tilt back at its centre')

    @staticmethod
    def _gate_angles(pos):
        """Angles of both servos for flap position pos (0 = closed, 1 = open)."""
        a1 = GATE_CLOSED_ANGLE + (GATE_OPEN_ANGLE - GATE_CLOSED_ANGLE) * pos
        a2 = GATE2_CLOSED_ANGLE + (GATE2_OPEN_ANGLE - GATE2_CLOSED_ANGLE) * pos
        return round(a1, 1), round(a2, 1)

    def _move_gate(self, target, force=False):
        """Move both servos together to `target` in GATE_STEPS small steps
        (or at once with force), then release the PWM so they don't buzz."""
        start = self._gate
        positions = [target] if force else [start + (target - start) * i / GATE_STEPS
                                            for i in range(1, GATE_STEPS + 1)]
        for pos in positions:
            self._servo.angle, self._servo2.angle = self._gate_angles(pos)
            if not force:
                self._wait(GATE_STEP_DELAY)
        self._gate = target
        self._wait(GATE_SETTLE)
        self._servo.detach()
        self._servo2.detach()
