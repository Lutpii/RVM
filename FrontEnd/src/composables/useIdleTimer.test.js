import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { ref } from 'vue'
vi.mock('vue', async (orig) => ({ ...(await orig()), onMounted: (fn) => fn(), onBeforeUnmount: () => {} }))
import { useIdleTimer, IDLE_MS, IDLE_COUNTDOWN_MS } from './useIdleTimer'

function mount(isActive, onExpire) {
  return { api: useIdleTimer({ isActive, onExpire }) }
}

describe('useIdleTimer', () => {
  beforeEach(() => { vi.useFakeTimers(); vi.stubGlobal('window', new EventTarget()) })
  afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals() })

  it('exports the constants', () => {
    expect(IDLE_MS).toBe(120000)
    expect(IDLE_COUNTDOWN_MS).toBe(20000)
  })

  it('shows overlay at 120s and expires once at 140s', () => {
    const onExpire = vi.fn()
    const { api } = mount(ref(true), onExpire)
    vi.advanceTimersByTime(119900)
    expect(api.showOverlay.value).toBe(false)
    vi.advanceTimersByTime(100)
    expect(api.showOverlay.value).toBe(true)
    vi.advanceTimersByTime(19800)
    expect(onExpire).not.toHaveBeenCalled()
    vi.advanceTimersByTime(200)
    expect(onExpire).toHaveBeenCalledTimes(1)
    vi.advanceTimersByTime(100000)
    expect(onExpire).toHaveBeenCalledTimes(1)
  })

  it('poke at 130s hides the overlay and resets', () => {
    const onExpire = vi.fn()
    const { api } = mount(ref(true), onExpire)
    vi.advanceTimersByTime(130000)
    expect(api.showOverlay.value).toBe(true)
    api.poke()
    expect(api.showOverlay.value).toBe(false)
    vi.advanceTimersByTime(119000)
    expect(api.showOverlay.value).toBe(false)
    vi.advanceTimersByTime(1000)
    expect(api.showOverlay.value).toBe(true)
    expect(onExpire).not.toHaveBeenCalled()
  })

  it('pointerdown on window resets', () => {
    const { api } = mount(ref(true), vi.fn())
    vi.advanceTimersByTime(125000)
    globalThis.window.dispatchEvent(new Event('pointerdown'))
    expect(api.showOverlay.value).toBe(false)
  })

  it('isActive=false at 125s hides overlay and never expires', async () => {
    const onExpire = vi.fn()
    const active = ref(true)
    const { api } = mount(active, onExpire)
    vi.advanceTimersByTime(125000)
    expect(api.showOverlay.value).toBe(true)
    active.value = false
    await Promise.resolve()
    expect(api.showOverlay.value).toBe(false)
    vi.advanceTimersByTime(200000)
    expect(onExpire).not.toHaveBeenCalled()
  })
})
