import { describe, it, expect } from 'vitest'
import { machinesInScope, countMachinesInScope } from './machineScope'

const machines = [
  { id: 1, is_demo: false }, { id: 2, is_demo: false }, { id: 3, is_demo: true },
]

describe('machinesInScope', () => {
  it('all keeps every machine', () => {
    expect(machinesInScope(machines, 'all').map(m => m.id)).toEqual([1, 2, 3])
  })
  it('demo keeps only demo machines', () => {
    expect(machinesInScope(machines, 'demo').map(m => m.id)).toEqual([3])
  })
  it('real keeps only non-demo machines', () => {
    expect(machinesInScope(machines, 'real').map(m => m.id)).toEqual([1, 2])
  })
  it('unknown scope behaves like all; missing list gives []', () => {
    expect(machinesInScope(machines, 'x')).toHaveLength(3)
    expect(machinesInScope(undefined, 'demo')).toEqual([])
  })
})

describe('countMachinesInScope', () => {
  it('counts per scope', () => {
    expect(countMachinesInScope(machines, 'all')).toBe(3)
    expect(countMachinesInScope(machines, 'real')).toBe(2)
    expect(countMachinesInScope(machines, 'demo')).toBe(1)
  })
})
