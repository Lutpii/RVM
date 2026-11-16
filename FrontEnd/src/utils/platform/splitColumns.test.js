import { describe, expect, it } from 'vitest'
import { splitColumns } from './splitColumns.js'

describe('splitColumns', () => {
  it('deals items left, right, left… so reading across keeps the admin order', () => {
    expect(splitColumns([1, 2, 3, 4, 5], 2)).toEqual([[1, 3, 5], [2, 4]])
  })

  it('keeps one column in order on phones', () => {
    expect(splitColumns([1, 2, 3], 1)).toEqual([[1, 2, 3]])
  })

  it('returns empty columns for no items', () => {
    expect(splitColumns([], 2)).toEqual([[], []])
  })
})
