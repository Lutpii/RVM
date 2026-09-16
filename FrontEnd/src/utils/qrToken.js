// Kiosk QR codes encode a hash-routed URL (e.g. https://host/#/scan?token=ABC),
// so the token lives inside the hash fragment, not the URL's real query string
// — `new URL(raw).searchParams` never sees it. Reading it with a plain regex
// works regardless of what comes before the `?`, including a bare pasted token.
export function extractQrToken(raw) {
  const value = (raw ?? '').trim()
  const match = value.match(/[?&]token=([^&]+)/)
  return match ? decodeURIComponent(match[1]) : value
}
