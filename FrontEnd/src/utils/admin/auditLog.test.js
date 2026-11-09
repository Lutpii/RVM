import { describe, it, expect } from 'vitest'
import { formatAuditLog, AUDIT_ACTION_LABELS } from './auditLog.js'

const base = { id: 1, admin: { id: 2, name: 'Alice' }, action: 'use_voucher', target_type: 'machine', target_id: 3, created_at: '2026-11-08T10:00:00Z' }

describe('formatAuditLog', () => {
  it('maps a JSON details string to message and uses the friendly label', () => {
    const row = formatAuditLog({ ...base, details: JSON.stringify({ message: 'Voucher used', demo: true }) })
    expect(row.message).toBe('Voucher used')
    expect(row.actionLabel).toBe(AUDIT_ACTION_LABELS.use_voucher)
    expect(row.actionLabel).toBe('Voucher used')
    expect(row.admin).toBe('Alice')
    expect(row.id).toBe(1)
    expect(row.time).toBeTruthy()
  })

  it('falls back to a Title Case slug label for unknown actions', () => {
    expect(formatAuditLog({ ...base, action: 'reset_something_odd', details: null }).actionLabel).toBe('Reset Something Odd')
  })

  it('formatAuditLog handles null and non-JSON details', () => {
    expect(formatAuditLog({ ...base, details: null }).message).toBe('')
    expect(formatAuditLog({ ...base, details: 'plain text' }).message).toBe('plain text')
  })

  it('renders the target as "machine #3", or an em dash for null/system', () => {
    expect(formatAuditLog({ ...base, details: null }).target).toBe('machine #3')
    expect(formatAuditLog({ ...base, target_type: null, details: null }).target).toBe('—')
    expect(formatAuditLog({ ...base, target_type: 'system', details: null }).target).toBe('—')
  })

  it('handles a missing admin', () => {
    expect(formatAuditLog({ ...base, admin: null, details: null }).admin).toBe('—')
  })
})
