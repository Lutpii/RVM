import { describe, it, expect } from 'vitest'
import { ONLINE_WINDOW_MS, machineConnectivity, machineAvailability } from './machineConnectivity.js'

const now = new Date('2026-11-12T10:00:00Z')

describe('machineConnectivity (shared)', () => {
  it('keeps the 3 minute window', () => expect(ONLINE_WINDOW_MS).toBe(180000))
  it('online / offline / never', () => {
    expect(machineConnectivity({ last_seen_at: '2026-11-12T09:59:00Z' }, now)).toBe('online')
    expect(machineConnectivity({ last_seen_at: '2026-11-12T09:00:00Z' }, now)).toBe('offline')
    expect(machineConnectivity({ last_seen_at: null }, now)).toBe('never')
  })
})

describe('machineAvailability (U06, recycler view)', () => {
  it('available when online without a fault', () => {
    expect(machineAvailability({ last_seen_at: '2026-11-12T09:59:00Z', fault_active: false }, now)).toBe('available')
  })
  it('offline when it stopped reporting', () => {
    expect(machineAvailability({ last_seen_at: '2026-11-12T09:00:00Z', fault_active: false }, now)).toBe('offline')
  })
  it('fault wins even while online', () => {
    expect(machineAvailability({ last_seen_at: '2026-11-12T09:59:00Z', fault_active: true }, now)).toBe('fault')
  })
  it('unknown when it never reported, so the cloud demo shows no false "offline"', () => {
    expect(machineAvailability({ last_seen_at: null, fault_active: false }, now)).toBe('unknown')
  })
})
