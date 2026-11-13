import { describe, it, expect } from 'vitest'
import { DEMO_ITEMS, simulatedConfidence, resumeStep, sessionMatchesMachine, itemSaveOutcome, itemCounts } from './kioskDemo'

describe('DEMO_ITEMS', () => {
  it('lists plastic, aluminum, paper, glass in that order', () => {
    expect(DEMO_ITEMS.map(i => i.id)).toEqual(['plastic', 'aluminum', 'paper', 'glass'])
  })
  it('only glass is not accepted', () => {
    expect(DEMO_ITEMS.filter(i => i.accepted === false).map(i => i.id)).toEqual(['glass'])
    expect(DEMO_ITEMS.filter(i => i.accepted === true).map(i => i.id)).toEqual(['plastic', 'aluminum', 'paper'])
  })
})

describe('simulatedConfidence', () => {
  it('is 0.88 at the bottom of the range', () => {
    expect(simulatedConfidence(() => 0)).toBe(0.88)
  })
  it('is 0.98 at the top of the range', () => {
    expect(simulatedConfidence(() => 0.999999)).toBe(0.98)
  })
  it('stays inside [0.88, 0.98] with 3 decimals', () => {
    for (let i = 0; i < 50; i++) {
      const c = simulatedConfidence()
      expect(c).toBeGreaterThanOrEqual(0.88)
      expect(c).toBeLessThanOrEqual(0.98)
      expect(Math.round(c * 1000) / 1000).toBe(c)
    }
  })
})

describe('resumeStep', () => {
  it('sends the simulated capture and AI steps back to the choose step', () => {
    expect(resumeStep('camera')).toBe('choose')
    expect(resumeStep('classify')).toBe('choose')
    expect(resumeStep('result')).toBe('choose')
    expect(resumeStep('weigh')).toBe('choose')
  })
  it('leaves other steps unchanged', () => {
    expect(resumeStep('complete')).toBe('complete')
    expect(resumeStep('bin_check')).toBe('bin_check')
    expect(resumeStep('choose')).toBe('choose')
  })
})

describe('sessionMatchesMachine', () => {
  it('true when the session belongs to this machine', () => {
    expect(sessionMatchesMachine({ machine: { id: 11 } }, 11)).toBe(true)
    expect(sessionMatchesMachine({ machine: { id: '11' } }, 11)).toBe(true)
  })
  it('false when the session is on another machine', () => {
    expect(sessionMatchesMachine({ machine: { id: 1 } }, 11)).toBe(false)
  })
  it('false when either id is unknown', () => {
    expect(sessionMatchesMachine(null, 11)).toBe(false)
    expect(sessionMatchesMachine({}, 11)).toBe(false)
    expect(sessionMatchesMachine({ machine: { id: null } }, 11)).toBe(false)
    expect(sessionMatchesMachine({ machine: { id: 11 } }, null)).toBe(false)
    expect(sessionMatchesMachine({ machine: { id: 11 } }, undefined)).toBe(false)
  })
})

describe('itemSaveOutcome', () => {
  it('records nothing and gives 0 points when the save failed', () => {
    expect(itemSaveOutcome(false, { points: 17, carbon: 0.2, weight: 30 }))
      .toEqual({ record: false, points: 0, carbon: 0, weight: 0 })
  })
  it('records the saved values when the save worked', () => {
    expect(itemSaveOutcome(true, { points: 17, carbon: 0.2, weight: 30 }))
      .toEqual({ record: true, points: 17, carbon: 0.2, weight: 30 })
  })
  it('treats missing values as 0 on success', () => {
    expect(itemSaveOutcome(true, {})).toEqual({ record: true, points: 0, carbon: 0, weight: 0 })
  })
})

describe('itemCounts', () => {
  it('counts every item in the list and splits accepted / rejected', () => {
    const txns = [{ is_valid: true }, { is_valid: false }]
    expect(itemCounts(txns)).toEqual({ total: 2, accepted: 1, rejected: 1 })
  })
  it('handles an empty or missing list', () => {
    expect(itemCounts([])).toEqual({ total: 0, accepted: 0, rejected: 0 })
    expect(itemCounts(undefined)).toEqual({ total: 0, accepted: 0, rejected: 0 })
  })
})
