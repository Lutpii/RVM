import { describe, it, expect } from 'vitest'
import { NAV_ITEMS, resolveActiveNavKey } from './navItems.js'
import { PhHouse, PhScroll, PhCamera, PhGift, PhUser } from '@phosphor-icons/vue'

describe('NAV_ITEMS', () => {
  it('has exactly 5 items in Home, Activity, Scan, Rewards, Account order', () => {
    expect(NAV_ITEMS.map((i) => i.key)).toEqual(['home', 'activity', 'scan', 'rewards', 'account'])
  })

  it('marks only the scan item as the center item', () => {
    const centerItems = NAV_ITEMS.filter((i) => i.isCenter)
    expect(centerItems.map((i) => i.key)).toEqual(['scan'])
  })

  it('assigns the correct Phosphor icon component to each nav item', () => {
    expect(NAV_ITEMS.find((i) => i.key === 'home').icon).toBe(PhHouse)
    expect(NAV_ITEMS.find((i) => i.key === 'activity').icon).toBe(PhScroll)
    expect(NAV_ITEMS.find((i) => i.key === 'scan').icon).toBe(PhCamera)
    expect(NAV_ITEMS.find((i) => i.key === 'rewards').icon).toBe(PhGift)
    expect(NAV_ITEMS.find((i) => i.key === 'account').icon).toBe(PhUser)
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
