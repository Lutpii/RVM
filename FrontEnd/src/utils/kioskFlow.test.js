import { describe, it, expect } from 'vitest'
import { isKioskFlowRoute, KIOSK_ROUTE_NAMES } from './kioskFlow'

describe('isKioskFlowRoute', () => {
  it.each(KIOSK_ROUTE_NAMES)('%s is part of the kiosk flow', (name) => {
    expect(isKioskFlowRoute({ name, path: '/x' })).toBe(true)
  })
  it('lists the 8 kiosk route names', () => {
    expect(KIOSK_ROUTE_NAMES).toHaveLength(8)
  })
  it('thank-you / welcome with a kiosk redirect are in the flow', () => {
    expect(isKioskFlowRoute({ name: 'thank-you', path: '/thank-you', query: { redirect: '/kiosk/RVM-001' } })).toBe(true)
    expect(isKioskFlowRoute({ name: 'welcome', path: '/welcome', query: { redirect: '/demo-kiosk/RVM-DEMO/session' } })).toBe(true)
  })
  it('phone flow and other pages are not', () => {
    expect(isKioskFlowRoute({ name: 'welcome', path: '/welcome', query: { redirect: '/dashboard' } })).toBe(false)
    expect(isKioskFlowRoute({ name: 'thank-you', path: '/thank-you', query: { redirect: '/' } })).toBe(false)
    expect(isKioskFlowRoute({ name: 'thank-you', path: '/thank-you', query: {} })).toBe(false)
    expect(isKioskFlowRoute({ name: 'thank-you', path: '/thank-you' })).toBe(false)
    expect(isKioskFlowRoute({ name: 'dashboard', path: '/dashboard' })).toBe(false)
  })
})
