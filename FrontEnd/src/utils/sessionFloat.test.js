import { describe, it, expect } from 'vitest'
import { floatMode } from '@/utils/sessionFloat'

const base = { routeName: 'dashboard', showAppNav: true, hasSession: true, sheetOpen: false }

describe('floatMode', () => {
  it('is a pill on the dashboard', () => {
    expect(floatMode(base)).toBe('pill')
  })
  it.each(['activity', 'rewards', 'account'])('is a badge on %s', (routeName) => {
    expect(floatMode({ ...base, routeName })).toBe('badge')
  })
  it('is hidden without a session', () => {
    expect(floatMode({ ...base, hasSession: false })).toBe('hidden')
  })
  it('is hidden while the sheet is open', () => {
    expect(floatMode({ ...base, sheetOpen: true })).toBe('hidden')
  })
  it('is hidden on routes without the app nav (scan, connected, kiosk)', () => {
    expect(floatMode({ ...base, routeName: 'scan', showAppNav: false })).toBe('hidden')
  })
})
