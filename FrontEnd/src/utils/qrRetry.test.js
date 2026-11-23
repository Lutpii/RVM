import { describe, expect, it } from 'vitest'
import { qrRetryDelay } from './qrRetry.js'

describe('qrRetryDelay', () => {
  it('retries quickly at first, then backs off', () => {
    expect([1, 2, 3].map(qrRetryDelay)).toEqual([3000, 5000, 8000])
  })

  it('settles at 10 s however many attempts failed', () => {
    expect(qrRetryDelay(4)).toBe(10000)
    expect(qrRetryDelay(50)).toBe(10000)
  })

  it('treats 0 or fewer failures like the first one', () => {
    expect(qrRetryDelay(0)).toBe(3000)
  })
})
