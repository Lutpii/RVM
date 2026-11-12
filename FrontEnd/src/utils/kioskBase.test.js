import { describe, it, expect } from 'vitest'
import { kioskBase, isDemoKioskPath } from './kioskBase'

describe('kioskBase', () => {
  it('returns /kiosk for real kiosk paths', () => {
    expect(kioskBase('/kiosk/RVM-001/qr')).toBe('/kiosk')
  })
  it('returns /demo-kiosk for demo paths and route objects', () => {
    expect(kioskBase({ path: '/demo-kiosk/RVM-DEMO' })).toBe('/demo-kiosk')
  })
  it('isDemoKioskPath detects demo paths only', () => {
    expect(isDemoKioskPath('/demo-kiosk/X/session')).toBe(true)
    expect(isDemoKioskPath('/kiosk/X')).toBe(false)
    expect(isDemoKioskPath('/dashboard')).toBe(false)
  })
})
