import { describe, it, expect } from 'vitest'
import { DATA_SCOPES, DATA_SCOPE_KEY, readDataScope, writeDataScope } from './dataScope.js'

function memoryStorage(initial = {}) {
  const data = { ...initial }
  return { getItem: (k) => (k in data ? data[k] : null), setItem: (k, v) => { data[k] = String(v) } }
}

describe('dataScope', () => {
  it('lists the three scopes', () => {
    expect(DATA_SCOPES).toEqual(['all', 'real', 'demo'])
  })
  it('defaults to all on empty storage', () => {
    expect(readDataScope(memoryStorage())).toBe('all')
  })
  it('reads back what was written', () => {
    const s = memoryStorage()
    writeDataScope('demo', s)
    expect(readDataScope(s)).toBe('demo')
  })
  it('falls back to all for an invalid stored value', () => {
    expect(readDataScope(memoryStorage({ [DATA_SCOPE_KEY]: 'foo' }))).toBe('all')
  })
  it('does not store an invalid scope', () => {
    const s = memoryStorage()
    writeDataScope('foo', s)
    expect(readDataScope(s)).toBe('all')
  })
  it('does not throw with null storage', () => {
    expect(readDataScope(null)).toBe('all')
    expect(() => writeDataScope('real', null)).not.toThrow()
  })
  it('does not throw when storage throws', () => {
    const bad = { getItem() { throw new Error('x') }, setItem() { throw new Error('x') } }
    expect(readDataScope(bad)).toBe('all')
    expect(() => writeDataScope('real', bad)).not.toThrow()
  })
})
