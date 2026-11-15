import { describe, it, expect } from 'vitest'
import { formatUserId } from './userCode.js'

describe('formatUserId', () => {
  it('uses the registration-order number, not the database id', () => {
    expect(formatUserId({ id: 2870931, display_no: 42, role: 'user' })).toBe('USR-0042')
  })
  it('pads admins to three digits', () => {
    expect(formatUserId({ id: 7, display_no: 3, role: 'admin' })).toBe('ADMIN-003')
  })
  it('falls back to the id when no number is present yet', () => {
    expect(formatUserId({ id: 5 })).toBe('USR-0005')
  })
  it('shows GUEST without a user', () => {
    expect(formatUserId(null)).toBe('GUEST')
  })
})
