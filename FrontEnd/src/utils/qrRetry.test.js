import { describe, expect, it } from 'vitest'
import { qrRetryDelay, isTemporaryQrError } from './qrRetry.js'

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

describe('isTemporaryQrError', () => {
  it('retries when there was no response, a timeout, a rate limit or a server error', () => {
    for (const status of [undefined, null, 0, 408, 425, 429, 500, 502, 503]) {
      expect(isTemporaryQrError(status)).toBe(true)
    }
  })

  it('does not retry an unknown machine or another client error', () => {
    for (const status of [400, 401, 403, 404, 422]) {
      expect(isTemporaryQrError(status)).toBe(false)
    }
  })
})
