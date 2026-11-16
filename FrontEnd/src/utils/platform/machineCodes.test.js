import { describe, expect, it } from 'vitest'
import { plannedCodes } from './machineCodes.js'

describe('plannedCodes', () => {
  it('keeps the code as typed for a single type', () => {
    expect(plannedCodes('GMB-01', ['compactor'])).toEqual(['GMB-01'])
  })

  it('adds the type suffix for each machine when several types are ticked (same as the backend)', () => {
    expect(plannedCodes('GMB-01', ['rvm', 'compactor', 'composter'])).toEqual(['GMB-01-RVM', 'GMB-01-CMP', 'GMB-01-CPS'])
  })

  it('returns nothing when no type is ticked', () => {
    expect(plannedCodes('GMB-01', [])).toEqual([])
  })
})
