import { describe, expect, it } from 'vitest'
import { statusPill, pillTone, isRvmMachine } from './machineStatus.js'

describe('isRvmMachine', () => {
  it('treats a missing type (legacy rows, OFF API) as Ultra RVM', () => {
    expect(isRvmMachine({})).toBe(true)
    expect(isRvmMachine({ machine_type: 'rvm' })).toBe(true)
    expect(isRvmMachine({ machine_type: 'compactor' })).toBe(false)
    expect(isRvmMachine({ machine_type: 'composter' })).toBe(false)
  })
})

describe('statusPill', () => {
  it.each([[79, 'available'], [80, 'almost_full'], [89, 'almost_full'], [90, 'full']])(
    'maps %i%% fill to %s', (fill, status) => {
      expect(statusPill({ machine_type: 'composter', fill_level: fill })).toBe(status)
    },
  )

  it('uses the fullest Ultra RVM bin', () => {
    expect(statusPill({ machine_type: 'rvm', bins: { plastic: 10, aluminum: 93, paper: 30 } })).toBe('full')
  })

  it('gives maintenance, faults, and stale RVM heartbeats precedence over fill', () => {
    const now = new Date('2026-11-16T08:00:00Z')
    const machine = { machine_type: 'rvm', status: 'active', fill_level: 100, last_seen_at: '2026-11-16T07:55:00Z' }
    expect(statusPill(machine, now)).toBe('offline')
    expect(statusPill({ ...machine, fault_active: true }, now)).toBe('fault')
    expect(statusPill({ ...machine, status: 'maintenance', fault_active: true }, now)).toBe('maintenance')
  })

  it('uses an explicit backend status and identifies its tone', () => {
    expect(statusPill({ machine_type: 'compactor', status: 'almost_full', fill_level: 1 })).toBe('almost_full')
    expect(['available', 'almost_full', 'full', 'fault', 'offline', 'maintenance'].map(pillTone))
      .toEqual(['ok', 'warn', 'bad', 'bad', 'muted', 'muted'])
  })
})
