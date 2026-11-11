import { describe, it, expect } from 'vitest'
import { itemLabel, resultReason, confidenceLevel, percentOf } from './transactionDisplay.js'

describe('itemLabel', () => {
  it('names real materials', () => {
    expect(itemLabel({ material_selected: 'plastic' })).toBe('Plastic')
    expect(itemLabel({ material_selected: 'aluminum' })).toBe('Aluminium can')
  })
  it('explains unknown and reject instead of showing the raw code', () => {
    expect(itemLabel({ material_selected: 'unknown' })).toBe('Not recognised')
    expect(itemLabel({ material_selected: 'reject' })).toBe('Material not accepted')
  })
})

describe('resultReason', () => {
  it('says which bin an accepted item went into', () => {
    expect(resultReason({ is_valid: 1, material_selected: 'plastic' })).toBe('Put in the plastic bin (A)')
  })
  it('gives the reason for a rejected item', () => {
    expect(resultReason({ is_valid: 0, material_selected: 'unknown', ai_detected_type: 'unknown' }))
      .toBe('AI was not sure what it was (below 60%)')
    expect(resultReason({ is_valid: 0, material_selected: 'reject', ai_detected_type: 'reject' }))
      .toBe('This machine does not take this material')
    expect(resultReason({ is_valid: 0, material_selected: 'plastic', ai_detected_type: 'glass' }))
      .toBe('AI saw glass, not plastic')
  })
})

describe('confidenceLevel', () => {
  it('uses the 60% rejection threshold', () => {
    expect(confidenceLevel(0.59)).toBe('low')
    expect(confidenceLevel(0.6)).toBe('mid')
    expect(confidenceLevel(0.8)).toBe('high')
    expect(confidenceLevel(null)).toBe('none')
  })
})

describe('percentOf', () => {
  it('handles zero totals', () => {
    expect(percentOf(3, 0)).toBe(0)
    expect(percentOf(1, 3)).toBe(33)
  })
})
