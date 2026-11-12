import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/services/api', () => ({ default: { get: vi.fn() } }))
import api from '@/services/api'
import { isDemoMachine } from './demoMachine'

const resp = (machines) => ({ data: { machines } })

describe('isDemoMachine', () => {
  beforeEach(() => api.get.mockReset())

  it('true when the machine has is_demo === true', async () => {
    api.get.mockResolvedValue(resp([{ machine_code: 'RVM-DEMO', is_demo: true }]))
    expect(await isDemoMachine('RVM-DEMO')).toBe(true)
  })
  it('false when is_demo is false', async () => {
    api.get.mockResolvedValue(resp([{ machine_code: 'RVM-001', is_demo: false }]))
    expect(await isDemoMachine('RVM-001')).toBe(false)
  })
  it('false when the machine is not found', async () => {
    api.get.mockResolvedValue(resp([{ machine_code: 'A', is_demo: true }]))
    expect(await isDemoMachine('B')).toBe(false)
  })
  it('false when the request fails', async () => {
    api.get.mockImplementation(() => Promise.reject(new Error('network')))
    expect(await isDemoMachine('RVM-DEMO')).toBe(false)
  })
})
