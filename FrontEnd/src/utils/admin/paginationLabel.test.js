import { describe, it, expect } from 'vitest'
import { paginationLabel } from './paginationLabel.js'

describe('paginationLabel', () => {
  it('describes a full page in the middle of the range', () => {
    expect(paginationLabel({ currentPage: 1, perPage: 25, total: 340 })).toBe('Showing 1-25 of 340')
  })

  it('describes a partial last page', () => {
    expect(paginationLabel({ currentPage: 14, perPage: 25, total: 340 })).toBe('Showing 326-340 of 340')
  })

  it('describes a dataset smaller than one page', () => {
    expect(paginationLabel({ currentPage: 1, perPage: 25, total: 10 })).toBe('Showing 1-10 of 10')
  })

  it('describes an empty result set', () => {
    expect(paginationLabel({ currentPage: 1, perPage: 25, total: 0 })).toBe('No results')
  })
})
