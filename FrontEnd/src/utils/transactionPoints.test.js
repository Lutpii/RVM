import { describe, it, expect } from 'vitest'
import { transactionPoints } from './transactionPoints'

describe('transactionPoints', () => {
  it('accepted item shows +points in green', () => {
    expect(transactionPoints({ is_valid: true, points_earned: 5 })).toEqual({ label: '+5', tone: 'green' })
  })

  it('rejected item with no deduction shows a grey 0, never -0', () => {
    expect(transactionPoints({ is_valid: false, material: 'Glass', points_deducted: 0 })).toEqual({ label: '0', tone: 'zero' })
    expect(transactionPoints({ is_valid: false, material: 'Glass' })).toEqual({ label: '0', tone: 'zero' })
  })

  it('unknown / reject items show a grey 0', () => {
    expect(transactionPoints({ is_valid: false, material: 'unknown' })).toEqual({ label: '0', tone: 'zero' })
    expect(transactionPoints({ is_valid: false, material: 'reject' })).toEqual({ label: '0', tone: 'zero' })
  })

  it('legacy deduction still shows -points in red', () => {
    expect(transactionPoints({ is_valid: false, material: 'Glass', points_deducted: 2 })).toEqual({ label: '-2', tone: 'red' })
  })

  it('accepted item with 0 points shows a grey 0', () => {
    expect(transactionPoints({ is_valid: true, points_earned: 0 })).toEqual({ label: '0', tone: 'zero' })
  })
})
