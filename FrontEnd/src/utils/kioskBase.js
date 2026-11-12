// The real kiosk lives under /kiosk and the demo kiosk under /demo-kiosk.
// Shared screens (landing, QR, summary) use this to build links for whichever
// flow they are currently in.
function pathOf(routeOrPath) {
  return typeof routeOrPath === 'string' ? routeOrPath : (routeOrPath?.path || '')
}

export function isDemoKioskPath(routeOrPath) {
  return pathOf(routeOrPath).startsWith('/demo-kiosk/') || pathOf(routeOrPath) === '/demo-kiosk'
}

export function kioskBase(routeOrPath) {
  return isDemoKioskPath(routeOrPath) ? '/demo-kiosk' : '/kiosk'
}
