// FrontEnd/src/utils/sessionFloat.js
// Which form the floating session indicator takes: the full pill on Home,
// a small badge on the other bottom-nav tabs, hidden everywhere else.
export function floatMode({ routeName, showAppNav, hasSession, sheetOpen }) {
  if (!hasSession || sheetOpen || !showAppNav) return 'hidden'
  return routeName === 'dashboard' ? 'pill' : 'badge'
}
