import { describe, it, expect } from 'vitest'
import { validateMachineName } from './validateMachine.js'

describe('validateMachineName', () => {
  it('rejects an empty name', () => {
    expect(validateMachineName('')).toBe('Machine name is required.')
  })

  it('rejects a whitespace-only name', () => {
    expect(validateMachineName('   ')).toBe('Machine name is required.')
  })

  it('rejects a missing name', () => {
    expect(validateMachineName(undefined)).toBe('Machine name is required.')
  })

  it('accepts a non-empty name', () => {
    expect(validateMachineName('RVM Lobby A')).toBeNull()
  })
})
