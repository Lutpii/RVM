// Single source of truth for "is this route part of a physical kiosk's flow".
export const KIOSK_ROUTE_NAMES = [
  'kiosk-landing', 'kiosk-qr', 'kiosk-session', 'kiosk-summary',
  'demo-kiosk-landing', 'demo-kiosk-qr', 'demo-kiosk-session', 'demo-kiosk-summary',
]

// Pre-scan entry points: the kiosk starts as guest here.
export const KIOSK_ENTRY_ROUTE_NAMES = [
  'kiosk-landing', 'kiosk-qr', 'demo-kiosk-landing', 'demo-kiosk-qr',
]

// /welcome and /thank-you are shared with the phone flow; they belong to the
// kiosk flow only when they bounce back into a kiosk route.
export function isKioskFlowRoute(route) {
  if (!route) return false
  if (KIOSK_ROUTE_NAMES.includes(route.name)) return true
  if (route.name === 'welcome' || route.name === 'thank-you') {
    const r = route.query?.redirect
    return typeof r === 'string' && (r.startsWith('/kiosk/') || r.startsWith('/demo-kiosk/'))
  }
  return false
}
