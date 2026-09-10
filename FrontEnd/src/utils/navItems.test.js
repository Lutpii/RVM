import { describe, it, expect } from 'vitest'
import { NAV_ITEMS, resolveActiveNavKey } from './navItems.js'

describe('NAV_ITEMS', () => {
  it('has exactly 5 items in Home, Activity, Scan, Rewards, Account order', () => {
    expect(NAV_ITEMS.map((i) => i.key)).toEqual(['home', 'activity', 'scan', 'rewards', 'account'])
  })

  it('marks only the scan item as the center item', () => {
    const centerItems = NAV_ITEMS.filter((i) => i.isCenter)
    expect(centerItems.map((i) => i.key)).toEqual(['scan'])
  })
})

describe('resolveActiveNavKey', () => {
  it('resolves each nav item route name to its own key', () => {
    expect(resolveActiveNavKey('dashboard')).toBe('home')
    expect(resolveActiveNavKey('activity')).toBe('activity')
    expect(resolveActiveNavKey('scan')).toBe('scan')
    expect(resolveActiveNavKey('rewards')).toBe('rewards')
    expect(resolveActiveNavKey('settings')).toBe('account')
  })

  it('returns null for a route not in the nav', () => {
    expect(resolveActiveNavKey('login')).toBeNull()
    expect(resolveActiveNavKey(undefined)).toBeNull()
  })
})
