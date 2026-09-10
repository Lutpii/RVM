import { describe, it, expect } from 'vitest'
import { normalizeMachine } from './normalizeMachine.js'

describe('normalizeMachine', () => {
  it('defaults missing bin-level fields to 0', () => {
    const machine = { id: 1, name: 'RVM Lobby A', machine_code: 'RVM-001' }

    expect(normalizeMachine(machine)).toEqual({
      id: 1,
      name: 'RVM Lobby A',
      machine_code: 'RVM-001',
      aluminum_level: 0,
      plastic_level: 0,
      glass_level: 0,
      paper_level: 0,
    })
  })

  it('preserves bin-level fields that are already present, including zero', () => {
    const machine = { id: 2, plastic_level: 45, aluminum_level: 0 }

    const result = normalizeMachine(machine)

    expect(result.plastic_level).toBe(45)
    expect(result.aluminum_level).toBe(0)
    expect(result.glass_level).toBe(0)
    expect(result.paper_level).toBe(0)
  })

  it('does not mutate the input object', () => {
    const machine = { id: 3 }

    normalizeMachine(machine)

    expect(machine).toEqual({ id: 3 })
  })
})
