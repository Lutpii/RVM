// src/utils/qrRetry.js
// Wait before the kiosk QR screen asks the backend for a QR again after a
// failed attempt. The free Render backend answers 429 for about a minute while
// it wakes up, so the screen keeps trying instead of stopping at an error:
// 3 s, 5 s, 8 s, then every 10 s until the QR screen's own time limit sends
// the kiosk back to its idle screen.
const DELAYS_MS = [3000, 5000, 8000]
const MAX_DELAY_MS = 10000

export function qrRetryDelay(failedAttempts) {
  const i = Math.max(0, failedAttempts - 1)
  return i < DELAYS_MS.length ? DELAYS_MS[i] : MAX_DELAY_MS
}

// Only failures that can clear up by themselves are retried: no response at
// all (network, CORS during a cold start), 408, 425, 429 and 5xx. A 404
// (unknown machine code) or another 4xx shows "Failed to load QR" at once.
export function isTemporaryQrError(status) {
  if (status === undefined || status === null || status === 0) return true
  return status === 408 || status === 425 || status === 429 || status >= 500
}
