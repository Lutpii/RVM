import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { startCountdown, shouldAutoReturn } from './useAutoReturn'

describe('shouldAutoReturn', () => {
  it('is on for demo kiosk routes only', () => {
    expect(shouldAutoReturn('/demo-kiosk/RVM-DEMO/summary')).toBe(true)
    expect(shouldAutoReturn({ path: '/demo-kiosk/RVM-DEMO/summary' })).toBe(true)
    expect(shouldAutoReturn('/kiosk/RVM-001/summary')).toBe(false)
    expect(shouldAutoReturn('/summary')).toBe(false)
  })
})

describe('startCountdown', () => {
  beforeEach(() => vi.useFakeTimers())
  afterEach(() => vi.useRealTimers())

  it('counts down from 10 and fires onDone once at 0', () => {
    const seen = []
    const onDone = vi.fn()
    startCountdown(10, (n) => seen.push(n), onDone)
    expect(seen).toEqual([10])
    vi.advanceTimersByTime(9000)
    expect(onDone).not.toHaveBeenCalled()
    vi.advanceTimersByTime(1000)
    expect(onDone).toHaveBeenCalledTimes(1)
    expect(seen[seen.length - 1]).toBe(0)
    vi.advanceTimersByTime(30000)
    expect(onDone).toHaveBeenCalledTimes(1)
  })

  it('stop() cancels so nothing fires later', () => {
    const onDone = vi.fn()
    const stop = startCountdown(10, () => {}, onDone)
    vi.advanceTimersByTime(4000)
    stop()
    vi.advanceTimersByTime(20000)
    expect(onDone).not.toHaveBeenCalled()
  })
})
