import { describe, it, expect } from 'vitest'
import { isFresh } from './tabFreshness.js'

describe('isFresh', () => {
  it('is fresh when well within the stale window', () => {
    expect(isFresh(1000, 1000 + 3000, 10000)).toBe(true)
  })

  it('is stale once the window has fully elapsed', () => {
    expect(isFresh(1000, 1000 + 10000, 10000)).toBe(false)
  })

  it('is stale when never fetched (falsy timestamp)', () => {
    expect(isFresh(0, 5000, 10000)).toBe(false)
    expect(isFresh(null, 5000, 10000)).toBe(false)
  })
})
