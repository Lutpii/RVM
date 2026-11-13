import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

vi.mock('@/services/api', () => ({ default: { get: vi.fn() } }))
vi.mock('@/store/auth', () => ({ useAuthStore: vi.fn() }))

import api from '@/services/api'
import { useAuthStore } from '@/store/auth'
import { useActiveSessionStore } from '@/store/activeSession'

let auth
beforeEach(() => {
  vi.useFakeTimers()
  setActivePinia(createPinia())
  auth = { isLoggedIn: true, fetchMe: vi.fn() }
  useAuthStore.mockReturnValue(auth)
  api.get.mockReset()
  api.get.mockResolvedValue({ data: { session: { points_earned: 1, items: [] } } })
})
afterEach(() => { vi.useRealTimers() })

describe('active session store', () => {
  it('polls every 4 s while started', async () => {
    const store = useActiveSessionStore()
    store.start()
    await vi.advanceTimersByTimeAsync(0)
    expect(api.get).toHaveBeenCalledTimes(1)
    await vi.advanceTimersByTimeAsync(4000)
    expect(api.get).toHaveBeenCalledTimes(2)
    expect(store.session.points_earned).toBe(1)
    store.stop()
  })

  it('stops polling after stop() and clears the session', async () => {
    const store = useActiveSessionStore()
    store.start()
    await vi.advanceTimersByTimeAsync(0)
    store.stop()
    api.get.mockClear()
    await vi.advanceTimersByTimeAsync(12000)
    expect(api.get).not.toHaveBeenCalled()
    expect(store.session).toBeNull()
  })

  it('does not start when logged out', async () => {
    auth.isLoggedIn = false
    const store = useActiveSessionStore()
    store.start()
    await vi.advanceTimersByTimeAsync(12000)
    expect(api.get).not.toHaveBeenCalled()
  })

  it('starts only one timer when start() is called twice', async () => {
    const store = useActiveSessionStore()
    store.start()
    store.start()
    await vi.advanceTimersByTimeAsync(0)
    api.get.mockClear()
    await vi.advanceTimersByTimeAsync(4000)
    expect(api.get).toHaveBeenCalledTimes(1)
    store.stop()
  })

  it('refreshes the profile when points_earned changes', async () => {
    const store = useActiveSessionStore()
    store.start()
    await vi.advanceTimersByTimeAsync(0)
    api.get.mockResolvedValue({ data: { session: { points_earned: 5, items: [] } } })
    await vi.advanceTimersByTimeAsync(4000)
    expect(auth.fetchMe).toHaveBeenCalledTimes(1)
    store.stop()
  })

  it('keeps the last session when a poll fails', async () => {
    const store = useActiveSessionStore()
    store.start()
    await vi.advanceTimersByTimeAsync(0)
    api.get.mockRejectedValue(new Error('offline'))
    await vi.advanceTimersByTimeAsync(4000)
    expect(store.session.points_earned).toBe(1)
    store.stop()
  })
})
