"""GpioDriver on gpiozero's mock pins (no Raspberry Pi needed).

Skipped where gpiozero isn't installed (e.g. a laptop running the
placeholder driver).
"""
import threading
import time
import unittest

try:
    from gpiozero.pins.mock import MockFactory, MockPWMPin
except ImportError:  # pragma: no cover - depends on the machine
    MockFactory = None

from gpio_driver import (COMPACT_IN_PIN, COMPACT_OUT_PIN, LIMIT_SWITCH_PIN, SERVO2_PIN, SERVO_PIN,
                         TILT_PIN_5, TILT_PIN_6, GpioDriver, HardwareError)

SCALE = 0.002  # 1 s of machine time = 2 ms here


def duty(ms):
    """PWM duty of a servo pulse of `ms` milliseconds at 50 Hz."""
    return ms / 20


@unittest.skipIf(MockFactory is None, 'gpiozero not installed')
class GpioDriverTest(unittest.TestCase):
    def setUp(self):
        self.factory = MockFactory(pin_class=MockPWMPin)
        self.driver = GpioDriver(time_scale=SCALE, pin_factory=self.factory)
        self.pin = self.factory.pin
        self.press(True)
        self.sim = None

    def tearDown(self):
        if self.sim:
            self.sim['stop'] = True
            self.sim['thread'].join(1)
        self.driver.close()

    def press(self, pressed):
        # pull-up switch: pressed pulls the pin to GND
        (self.pin(LIMIT_SWITCH_PIN).drive_low if pressed else self.pin(LIMIT_SWITCH_PIN).drive_high)()

    def simulate_tilt(self, returns=True):
        """Switch releases when the tilt drives out; presses again shortly after
        the opposite relay starts (unless `returns` is False)."""
        sim = {'stop': False}

        def run():
            out_pin, back_since = None, None
            while not sim['stop']:
                on = [p for p in (TILT_PIN_5, TILT_PIN_6) if self.pin(p).state]
                if on and out_pin is None:
                    out_pin = on[0]
                    self.press(False)
                elif on and on[0] != out_pin:
                    back_since = back_since or time.monotonic()
                    if returns and time.monotonic() - back_since > 0.005:
                        self.press(True)
                time.sleep(0.0005)

        sim['thread'] = threading.Thread(target=run, daemon=True)
        sim['thread'].start()
        self.sim = sim

    def servo_duties(self, pin):
        return [round(state, 4) for _, state in self.pin(pin).states if state]

    def on_intervals(self, pin):
        """[(on_time, off_time), ...] of a relay pin."""
        out, since = [], None
        for t, state in self.pin(pin).states:
            if state and since is None:
                since = t
            elif not state and since is not None:
                out.append((since, t))
                since = None
        return out

    def test_startup_closes_the_flap_and_reports_ready(self):
        self.assertIsNone(self.driver.startup())
        # closed: servo 1 at 180 of 360 (1.5 ms), servo 2 at 90 of 180 (1.5 ms)
        self.assertEqual(self.servo_duties(SERVO_PIN)[-1], duty(1.5))
        self.assertEqual(self.servo_duties(SERVO2_PIN)[-1], duty(1.5))
        # PWM released afterwards so the servos don't buzz
        self.assertEqual(self.pin(SERVO_PIN).state, 0)
        for relay in (COMPACT_IN_PIN, COMPACT_OUT_PIN, TILT_PIN_5, TILT_PIN_6):
            self.assertFalse(self.pin(relay).state)

    def test_startup_with_tilt_off_centre_is_not_ready(self):
        self.press(False)
        self.assertIn('limit switch', self.driver.startup())

    def test_gate_open_moves_both_servos_to_their_open_angles(self):
        self.driver.startup()
        self.driver.gate_open()
        # open: servo 1 at 360 (2.5 ms), servo 2 at 0 (0.5 ms), in the same final step
        self.assertEqual(self.servo_duties(SERVO_PIN)[-1], duty(2.5))
        self.assertEqual(self.servo_duties(SERVO2_PIN)[-1], duty(0.5))
        self.driver.gate_close()
        self.assertEqual(self.servo_duties(SERVO_PIN)[-1], duty(1.5))
        self.assertEqual(self.servo_duties(SERVO2_PIN)[-1], duty(1.5))

    def test_compact_presses_then_returns_never_both(self):
        self.driver.compact()
        (in_on, in_off), = self.on_intervals(COMPACT_IN_PIN)
        (out_on, out_off), = self.on_intervals(COMPACT_OUT_PIN)
        self.assertLessEqual(in_off, out_on)

    def test_tilt_goes_out_then_back_to_the_limit_switch(self):
        self.simulate_tilt()
        self.driver.tilt('aluminum')
        (out_on, out_off), = self.on_intervals(TILT_PIN_6)
        (back_on, back_off), = self.on_intervals(TILT_PIN_5)
        self.assertLessEqual(out_off, back_on)
        self.assertFalse(self.pin(TILT_PIN_5).state)
        self.assertTrue(self.driver._limit.is_pressed)

    def test_plastic_tilts_the_other_way(self):
        self.simulate_tilt()
        self.driver.tilt('plastic')
        (out_on, _), = self.on_intervals(TILT_PIN_5)
        (back_on, _), = self.on_intervals(TILT_PIN_6)
        self.assertLess(out_on, back_on)

    def test_tilt_that_never_returns_fails_with_relays_off(self):
        self.simulate_tilt(returns=False)
        with self.assertRaises(HardwareError):
            self.driver.tilt('plastic')
        self.assertFalse(self.pin(TILT_PIN_5).state)
        self.assertFalse(self.pin(TILT_PIN_6).state)

    def test_tilt_that_never_leaves_its_centre_fails(self):
        # no simulation: the switch stays pressed, as if the motor didn't move
        with self.assertRaises(HardwareError):
            self.driver.tilt('aluminum')
        self.assertEqual(self.on_intervals(TILT_PIN_5), [])  # never tried to come back

    def test_emergency_stop_turns_every_relay_off(self):
        self.driver._relays[COMPACT_IN_PIN].on()
        self.driver.emergency_stop()
        self.assertFalse(self.pin(COMPACT_IN_PIN).state)

    def test_material_without_a_bin_is_refused(self):
        with self.assertRaises(KeyError):
            self.driver.tilt('glass')


if __name__ == '__main__':
    unittest.main()
