import { describe, it, expect } from 'vitest'
import { summarizeSession } from './activeSession'

describe('summarizeSession', () => {
  it('returns zeros for null', () => {
    expect(summarizeSession(null)).toEqual({
      machineName: '', location: '', isDemo: false, items: 0, points: 0, carbon: 0,
    })
  })

  it('sums items and flags demo machines', () => {
    const s = summarizeSession({
      is_demo: false,
      machine: { name: 'RVM-DEMO', location: 'UMPSA', is_demo: true },
      items: [
        { points_earned: 5, carbon_saved: 0.025 },
        { points_earned: 0, carbon_saved: 0 },
      ],
    })
    expect(s).toEqual({
      machineName: 'RVM-DEMO', location: 'UMPSA', isDemo: true, items: 2, points: 5, carbon: 0.025,
    })
  })

  it('flags demo from the session when the machine does not say', () => {
    expect(summarizeSession({ is_demo: true, machine: {}, items: [] }).isDemo).toBe(true)
  })
})
