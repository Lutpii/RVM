import { describe, it, expect } from 'vitest'
import { resolveLoadingFlag } from './tabLoading.js'

describe('resolveLoadingFlag', () => {
  it('maps each known tab to its own loading flag name', () => {
    expect(resolveLoadingFlag('dashboard')).toBe('loadingStats')
    expect(resolveLoadingFlag('transactions')).toBe('loadingTransactions')
    expect(resolveLoadingFlag('users')).toBe('loadingUsers')
    expect(resolveLoadingFlag('machines')).toBe('loadingMachines')
    expect(resolveLoadingFlag('sessions')).toBe('loadingSessions')
  })

  it('returns null for an unknown tab', () => {
    expect(resolveLoadingFlag('bogus')).toBeNull()
  })
})
