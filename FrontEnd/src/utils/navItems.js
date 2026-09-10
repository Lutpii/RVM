export const NAV_ITEMS = [
  { key: 'home',     labelKey: 'appNav.home',     icon: '🏠', routeName: 'dashboard' },
  { key: 'activity', labelKey: 'appNav.activity', icon: '📜', routeName: 'activity' },
  { key: 'scan',     labelKey: 'appNav.scan',     icon: '📷', routeName: 'scan', isCenter: true },
  { key: 'rewards',  labelKey: 'appNav.rewards',  icon: '🎁', routeName: 'rewards' },
  { key: 'account',  labelKey: 'appNav.account',  icon: '👤', routeName: 'settings' },
]

export function resolveActiveNavKey(routeName) {
  const match = NAV_ITEMS.find((item) => item.routeName === routeName)
  return match ? match.key : null
}
