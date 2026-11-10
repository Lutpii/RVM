import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { startHeartbeat } from './heartbeat.js'

const flush = () => new Promise((r) => setTimeout(r, 0))

describe('startHeartbeat', () => {
  beforeEach(() => vi.useFakeTimers({ toFake: ['setInterval', 'clearInterval'] }))
  afterEach(() => vi.useRealTimers())

  it('posts immediately and then every interval', async () => {
    const post = vi.fn().mockResolvedValue({})
    const stop = startHeartbeat({ post, getState: async () => ({ profile: 'legacy' }), intervalMs: 1000 })
    await flush()
    expect(post).toHaveBeenCalledTimes(1)
    vi.advanceTimersByTime(2000)
    await flush()
    expect(post).toHaveBeenCalledTimes(3)
    stop()
  })

  it('reports a fault from a 2bin state with fault set', async () => {
    const post = vi.fn().mockResolvedValue({})
    const stop = startHeartbeat({ post, getState: async () => ({ profile: '2bin', fault: 'flap_jam' }), intervalMs: 1000 })
    await flush()
    expect(post).toHaveBeenCalledWith('/hardware/heartbeat', { fault: true, fault_reason: 'flap_jam' })
    stop()
  })

  it('reports no fault when the state is healthy or unreachable', async () => {
    const post = vi.fn().mockResolvedValue({})
    const stop = startHeartbeat({ post, getState: async () => null, intervalMs: 1000 })
    await flush()
    expect(post).toHaveBeenCalledWith('/hardware/heartbeat', { fault: false, fault_reason: null })
    stop()
  })

  it('keeps ticking when post or getState rejects', async () => {
    const post = vi.fn().mockRejectedValue(new Error('offline'))
    const getState = vi.fn().mockRejectedValueOnce(new Error('boom')).mockResolvedValue(null)
    const stop = startHeartbeat({ post, getState, intervalMs: 1000 })
    await flush()
    vi.advanceTimersByTime(1000)
    await flush()
    expect(getState).toHaveBeenCalledTimes(2)
    expect(post).toHaveBeenCalled()
    stop()
  })

  it('stop() clears the timer', async () => {
    const post = vi.fn().mockResolvedValue({})
    const stop = startHeartbeat({ post, getState: async () => null, intervalMs: 1000 })
    await flush()
    stop()
    vi.advanceTimersByTime(5000)
    await flush()
    expect(post).toHaveBeenCalledTimes(1)
  })
})
