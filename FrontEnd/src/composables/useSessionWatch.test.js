import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { ref } from 'vue'

vi.mock('vue', async (orig) => ({ ...(await orig()), onMounted: (fn) => fn(), onBeforeUnmount: () => {} }))
vi.mock('@/services/api', () => ({ default: { get: vi.fn() } }))
import api from '@/services/api'
import { useSessionWatch } from './useSessionWatch'

function mount(enabled, onEnded) {
  useSessionWatch({ sessionCode: ref('S1'), enabled, onEnded, intervalMs: 5000 })
}

describe('useSessionWatch', () => {
  beforeEach(() => { vi.useFakeTimers(); api.get.mockReset() })
  afterEach(() => vi.useRealTimers())

  it('calls onEnded once after a completed response', async () => {
    api.get.mockResolvedValue({ data: { success: true, status: 'completed' } })
    const onEnded = vi.fn()
    mount(ref(true), onEnded)
    await vi.advanceTimersByTimeAsync(5000)
    await vi.advanceTimersByTimeAsync(20000)
    expect(api.get).toHaveBeenCalledWith('/sessions/S1/status')
    expect(onEnded).toHaveBeenCalledTimes(1)
  })

  it('keeps polling while active', async () => {
    api.get.mockResolvedValue({ data: { status: 'active' } })
    const onEnded = vi.fn()
    mount(ref(true), onEnded)
    await vi.advanceTimersByTimeAsync(15000)
    expect(api.get).toHaveBeenCalledTimes(3)
    expect(onEnded).not.toHaveBeenCalled()
  })

  it('treats 404 as ended', async () => {
    api.get.mockRejectedValue({ response: { status: 404 } })
    const onEnded = vi.fn()
    mount(ref(true), onEnded)
    await vi.advanceTimersByTimeAsync(10000)
    expect(onEnded).toHaveBeenCalledTimes(1)
  })

  it('treats 401 as ended but ignores network errors', async () => {
    api.get.mockRejectedValueOnce({ message: 'Network Error' }).mockRejectedValue({ response: { status: 401 } })
    const onEnded = vi.fn()
    mount(ref(true), onEnded)
    await vi.advanceTimersByTimeAsync(5000)
    expect(onEnded).not.toHaveBeenCalled()
    await vi.advanceTimersByTimeAsync(5000)
    expect(onEnded).toHaveBeenCalledTimes(1)
  })

  it.each([401, 404])('ignores a delayed %i while the kiosk processes an item', async (status) => {
    let rejectPending
    api.get.mockImplementationOnce(() => new Promise((_, reject) => { rejectPending = reject }))
    api.get.mockRejectedValue({ response: { status } })

    const enabled = ref(true)
    const onEnded = vi.fn()
    mount(enabled, onEnded)
    await vi.advanceTimersByTimeAsync(5000)

    enabled.value = false
    rejectPending({ response: { status } })
    await vi.advanceTimersByTimeAsync(5000)
    expect(onEnded).not.toHaveBeenCalled()

    enabled.value = true
    await vi.advanceTimersByTimeAsync(5000)
    expect(onEnded).toHaveBeenCalledTimes(1)
  })

  it('never polls or ends while disabled', async () => {
    api.get.mockResolvedValue({ data: { status: 'completed' } })
    const onEnded = vi.fn()
    mount(ref(false), onEnded)
    await vi.advanceTimersByTimeAsync(30000)
    expect(api.get).not.toHaveBeenCalled()
    expect(onEnded).not.toHaveBeenCalled()
  })
})
