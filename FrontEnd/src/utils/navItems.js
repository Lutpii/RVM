import { PhHouse, PhScroll, PhCamera, PhGift, PhUser } from '@phosphor-icons/vue'

export const NAV_ITEMS = [
  { key: 'home',     labelKey: 'appNav.home',     icon: PhHouse,  routeName: 'dashboard' },
  { key: 'activity', labelKey: 'appNav.activity', icon: PhScroll, routeName: 'activity' },
  { key: 'scan',     labelKey: 'appNav.scan',     icon: PhCamera, routeName: 'scan', isCenter: true },
  { key: 'rewards',  labelKey: 'appNav.rewards',  icon: PhGift,   routeName: 'rewards' },
  { key: 'account',  labelKey: 'appNav.account',  icon: PhUser,   routeName: 'settings' },
]

export function resolveActiveNavKey(routeName) {
  const match = NAV_ITEMS.find((item) => item.routeName === routeName)
  return match ? match.key : null
}
