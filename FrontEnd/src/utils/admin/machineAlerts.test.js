import { describe, it, expect } from 'vitest'
import { ONLINE_WINDOW_MS, machineConnectivity, buildMachineAlerts, connectivitySummary } from './machineAlerts.js'

const now = new Date('2026-11-12T10:00:00Z')
const m = (id, o = {}) => ({ id, name: `M${id}`, last_seen_at: null, fault_active: false, fault_reason: null, fault_since: null, ...o })

describe('machineConnectivity', () => {
  it('uses a 3 minute window', () => expect(ONLINE_WINDOW_MS).toBe(180000))
  it('is online within the window', () => {
    expect(machineConnectivity(m(1, { last_seen_at: '2026-11-12T09:58:30Z' }), now)).toBe('online')
  })
  it('is offline after the window', () => {
    expect(machineConnectivity(m(1, { last_seen_at: '2026-11-12T09:56:00Z' }), now)).toBe('offline')
  })
  it('is never when it has not reported or the timestamp is unreadable', () => {
    expect(machineConnectivity(m(1), now)).toBe('never')
    expect(machineConnectivity(m(1, { last_seen_at: 'garbage' }), now)).toBe('never')
  })
})

describe('buildMachineAlerts', () => {
  it('raises nothing for machines that never reported', () => {
    expect(buildMachineAlerts([m(1), m(2)], now)).toEqual([])
  })
  it('raises an offline alert for a machine that stopped reporting', () => {
    const alerts = buildMachineAlerts([m(1, { last_seen_at: '2026-11-12T09:30:00Z' })], now)
    expect(alerts).toEqual([{ machineId: 1, name: 'M1', type: 'offline', since: '2026-11-12T09:30:00Z', detail: null }])
  })
  it('raises a fault alert with its reason, even while online', () => {
    const alerts = buildMachineAlerts([m(1, {
      last_seen_at: '2026-11-12T09:59:30Z', fault_active: true, fault_reason: 'flap_jam', fault_since: '2026-11-12T09:50:00Z',
    })], now)
    expect(alerts).toEqual([{ machineId: 1, name: 'M1', type: 'fault', since: '2026-11-12T09:50:00Z', detail: 'flap_jam' }])
  })
  it('orders faults before offline machines', () => {
    const alerts = buildMachineAlerts([
      m(1, { last_seen_at: '2026-11-12T09:00:00Z' }),
      m(2, { last_seen_at: '2026-11-12T09:59:00Z', fault_active: true, fault_reason: 'motor' }),
    ], now)
    expect(alerts.map((a) => [a.machineId, a.type])).toEqual([[2, 'fault'], [1, 'offline']])
  })
  it('a faulted machine that is also offline shows both', () => {
    const alerts = buildMachineAlerts([m(1, { last_seen_at: '2026-11-12T09:00:00Z', fault_active: true, fault_reason: 'x' })], now)
    expect(alerts.map((a) => a.type)).toEqual(['fault', 'offline'])
  })
})

describe('connectivitySummary', () => {
  it('counts online machines and flags when nobody has reported', () => {
    expect(connectivitySummary([m(1), m(2)], now)).toEqual({ online: 0, total: 2, anyReported: false })
    expect(connectivitySummary([
      m(1, { last_seen_at: '2026-11-12T09:59:00Z' }), m(2, { last_seen_at: '2026-11-12T08:00:00Z' }), m(3),
    ], now)).toEqual({ online: 1, total: 3, anyReported: true })
  })
})
