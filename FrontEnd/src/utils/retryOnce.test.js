import { describe, it, expect, vi } from 'vitest'
import { retryOnce, isNetworkError, newIdempotencyKey } from './retryOnce.js'

describe('retryOnce', () => {
  it('retries once on a network error and returns the second result', async () => {
    const fn = vi.fn().mockRejectedValueOnce(new Error('Network Error')).mockResolvedValueOnce('ok')
    await expect(retryOnce(fn, isNetworkError)).resolves.toBe('ok')
    expect(fn).toHaveBeenCalledTimes(2)
  })

  it('does not retry when the server answered (4xx/5xx)', async () => {
    const err = Object.assign(new Error('Bad Request'), { response: { status: 400 } })
    const fn = vi.fn().mockRejectedValue(err)
    await expect(retryOnce(fn, isNetworkError)).rejects.toBe(err)
    expect(fn).toHaveBeenCalledTimes(1)
  })

  it('gives up after the second network error', async () => {
    const fn = vi.fn().mockRejectedValue(new Error('Network Error'))
    await expect(retryOnce(fn, isNetworkError)).rejects.toThrow('Network Error')
    expect(fn).toHaveBeenCalledTimes(2)
  })
})

describe('newIdempotencyKey', () => {
  it('returns distinct keys of at most 64 characters', () => {
    const a = newIdempotencyKey()
    const b = newIdempotencyKey()
    expect(a).not.toBe(b)
    expect(a.length).toBeGreaterThan(8)
    expect(a.length).toBeLessThanOrEqual(64)
  })
})
