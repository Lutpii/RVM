import { describe, it, expect } from 'vitest'
import { paginationBounds, paginationItems, paginationLabel } from './paginationLabel.js'

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

  it('returns numeric bounds for the reusable pagination control', () => {
    expect(paginationBounds({ currentPage: 2, perPage: 20, total: 7486 })).toEqual({ from: 21, to: 40 })
    expect(paginationBounds({ currentPage: 1, perPage: 20, total: 0 })).toEqual({ from: 0, to: 0 })
  })

  it('builds compact page numbers around the current page', () => {
    expect(paginationItems(2, 375)).toEqual([1, 2, 3, 'ellipsis', 375])
    expect(paginationItems(200, 375)).toEqual([1, 'ellipsis', 199, 200, 201, 'ellipsis', 375])
    expect(paginationItems(374, 375)).toEqual([1, 'ellipsis', 373, 374, 375])
  })

  it('shows every page when the result has seven pages or fewer', () => {
    expect(paginationItems(3, 5)).toEqual([1, 2, 3, 4, 5])
  })
})
