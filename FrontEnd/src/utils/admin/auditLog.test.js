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

  it('appends before/after values from details.changes', () => {
    const d = JSON.stringify({ message: 'Updated user: X', changes: { total_points: [100, 150], name: ['A', 'B'] } })
    expect(formatAuditLog({ ...base, details: d }).message).toBe('Updated user: X — total_points: 100 → 150, name: A → B')
    const d2 = JSON.stringify({ message: 'M', changes: { name: { old: 'A', new: 'B' } } })
    expect(formatAuditLog({ ...base, details: d2 }).message).toBe('M — name: A → B')
    expect(formatAuditLog({ ...base, details: JSON.stringify({ message: 'M', changes: {} }) }).message).toBe('M')
  })

  it('omits "#0" when target_id is 0, null or missing', () => {
    expect(formatAuditLog({ ...base, target_type: 'transactions', target_id: 0, details: null }).target).toBe('transactions')
    expect(formatAuditLog({ ...base, target_type: 'transactions', target_id: null, details: null }).target).toBe('transactions')
    expect(formatAuditLog({ ...base, target_type: 'transactions', target_id: undefined, details: null }).target).toBe('transactions')
  })
})

describe('formatAuditLog with a translator', () => {
  it('uses the translated action label when one exists, else the built-in one', () => {
    const label = (a) => (a === 'update_user' ? 'Pengguna dikemas kini' : undefined)
    expect(formatAuditLog({ id: 1, action: 'update_user' }, { label }).actionLabel).toBe('Pengguna dikemas kini')
    expect(formatAuditLog({ id: 2, action: 'export_csv' }, { label }).actionLabel).toBe('Report exported')
  })
})

describe('formatAuditLog translated details and target', () => {
  const message = (key, params) => (key === 'deleteMachine' ? `Mesin dihapus: ${params.name}` : undefined)
  const target = (type) => ({ machine: 'mesin' })[type]
  it('uses message_key + params when the translator knows the key', () => {
    const details = JSON.stringify({ message: 'Deleted machine: Lobby', message_key: 'deleteMachine', params: { name: 'Lobby' } })
    const row = formatAuditLog({ id: 1, action: 'delete_machine', target_type: 'machine', target_id: 7, details }, { message, target })
    expect(row.message).toBe('Mesin dihapus: Lobby')
    expect(row.target).toBe('mesin #7')
  })
  it('falls back to the stored English text for old rows and unknown keys', () => {
    const old = formatAuditLog({ id: 2, action: 'x', details: JSON.stringify({ message: 'Old text' }) }, { message, target })
    expect(old.message).toBe('Old text')
    const unknown = formatAuditLog({ id: 3, action: 'x', details: JSON.stringify({ message: 'Kept', message_key: 'nope', params: {} }) }, { message })
    expect(unknown.message).toBe('Kept')
  })
})
