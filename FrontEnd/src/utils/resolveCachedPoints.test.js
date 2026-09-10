import { describe, it, expect } from 'vitest'
import { resolveCachedPoints } from './resolveCachedPoints.js'

describe('resolveCachedPoints', () => {
  it('returns the backend value when there is no cache', () => {
    expect(resolveCachedPoints(null, 100)).toBe(100)
  })

  it('restores a fresh, higher cached value (the kiosk-race case this exists for)', () => {
    const raw = JSON.stringify({ points: 150, savedAt: 1_000_000 })
    expect(resolveCachedPoints(raw, 100, /* now */ 1_000_000 + 5_000)).toBe(150)
  })

  it('ignores a cached value older than the max age', () => {
    const raw = JSON.stringify({ points: 150, savedAt: 1_000_000 })
    expect(resolveCachedPoints(raw, 100, /* now */ 1_000_000 + 60_001)).toBe(100)
  })

  it('does not revert a genuine points decrease from another device (cache lower than backend)', () => {
    const raw = JSON.stringify({ points: 50, savedAt: 1_000_000 })
    expect(resolveCachedPoints(raw, 100, /* now */ 1_000_000 + 1_000)).toBe(100)
  })

  it('does not revert when the cache exactly equals the backend value', () => {
    const raw = JSON.stringify({ points: 100, savedAt: 1_000_000 })
    expect(resolveCachedPoints(raw, 100, /* now */ 1_000_000 + 1_000)).toBe(100)
  })

  it('treats the pre-migration bare-number format as stale', () => {
    expect(resolveCachedPoints('150', 100, 1_000_000)).toBe(100)
  })

  it('treats corrupt JSON as stale', () => {
    expect(resolveCachedPoints('{not json', 100, 1_000_000)).toBe(100)
  })

  it('treats a backend value of null as zero for comparison', () => {
    const raw = JSON.stringify({ points: 10, savedAt: 1_000_000 })
    expect(resolveCachedPoints(raw, null, 1_000_000 + 1_000)).toBe(10)
  })
})
