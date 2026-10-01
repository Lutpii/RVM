import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

vi.mock('@/services/api', () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: {} })), get: vi.fn() },
}))
vi.mock('@/services/compactor', () => ({ getHardwareState: vi.fn() }))

import api from '@/services/api'
import { getHardwareState } from '@/services/compactor'
import { useRvmStore } from './rvm.js'

describe('rvm store hardware profile', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('detects the 2-bin compactor', async () => {
    getHardwareState.mockResolvedValue({ profile: '2bin', chamber_material: null })
    const rvm = useRvmStore()
    expect(rvm.hardwareProfile).toBe('legacy')
    expect(await rvm.detectHardwareProfile()).toBe('2bin')
    expect(rvm.hardwareProfile).toBe('2bin')
  })

  it('falls back to legacy for the 4-bin service or no service', async () => {
    const rvm = useRvmStore()
    for (const answer of [{ profile: 'legacy' }, null]) {
      getHardwareState.mockResolvedValue(answer)
      expect(await rvm.detectHardwareProfile()).toBe('legacy')
    }
  })

  it('does not call /hardware/sort for guests on the 2-bin machine', async () => {
    getHardwareState.mockResolvedValue({ profile: '2bin' })
    const rvm = useRvmStore()
    rvm.isGuest = true
    await rvm.detectHardwareProfile()
    await rvm.processStep('complete', { ai_detected_type: 'plastic' })
    expect(api.post).not.toHaveBeenCalledWith('/hardware/sort', expect.anything())
  })

  it('still sorts for guests on the 4-bin machine', async () => {
    const rvm = useRvmStore()
    rvm.isGuest = true
    await rvm.processStep('complete', { ai_detected_type: 'plastic' })
    expect(api.post).toHaveBeenCalledWith('/hardware/sort', { material: 'plastic' })
  })
})
