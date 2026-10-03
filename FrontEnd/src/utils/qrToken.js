// Kiosk QR codes encode a hash-routed URL (e.g. https://host/#/scan?token=ABC),
// so the token lives inside the hash fragment, not the URL's real query string
// — `new URL(raw).searchParams` never sees it. Reading it with a plain regex
// works regardless of what comes before the `?`, including a bare pasted token.
export function extractQrToken(raw) {
  const value = (raw ?? '').trim()
  const match = value.match(/[?&]token=([^&]+)/)
  return match ? decodeURIComponent(match[1]) : value
}

// Each kiosk login QR is valid this long (must match QrController::QR_LIFETIME_SECONDS).
export const QR_LIFETIME_SECONDS = 100
// After the first QR expires, show a new one this many times, then go back to
// the kiosk landing page instead of cycling QRs forever on an unattended screen.
export const QR_MAX_REFRESHES = 3

export function afterQrExpiry(refreshesDone) {
  return refreshesDone < QR_MAX_REFRESHES ? 'refresh' : 'leave'
}
