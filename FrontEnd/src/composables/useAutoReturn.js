// Demo kiosk summary (K10): counts down, then returns to the start screen.
// The real kiosk summary does not auto-return, so shouldAutoReturn gates it.
import { isDemoKioskPath } from '@/utils/kioskBase'

export function shouldAutoReturn(routeOrPath) {
  return isDemoKioskPath(routeOrPath)
}

// Calls onTick(secondsLeft) now and every second; onDone() once at 0.
// Returns stop(), which cancels the timer.
export function startCountdown(seconds, onTick, onDone) {
  let left = seconds
  let timer = null
  const stop = () => { if (timer) { clearInterval(timer); timer = null } }
  onTick(left)
  timer = setInterval(() => {
    left -= 1
    onTick(left)
    if (left <= 0) { stop(); onDone() }
  }, 1000)
  return stop
}
