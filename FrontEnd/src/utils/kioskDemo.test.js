import { describe, it, expect } from 'vitest'
import { DEMO_ITEMS, simulatedConfidence, resumeStep } from './kioskDemo'

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
