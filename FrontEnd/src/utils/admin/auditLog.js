export const AUDIT_ACTION_LABELS = {
  use_voucher: 'Voucher used',
  request_bin_collection: 'Bin collection requested',
  update_bin_levels: 'Bin levels reset',
  export_csv: 'Report exported',
  update_user: 'User updated',
  update_machine: 'Machine updated',
}

function titleCase(slug) {
  return String(slug ?? '')
    .split('_')
    .filter(Boolean)
    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ')
}

function detailsMessage(details) {
  if (details == null) return ''
  let parsed = details
  if (typeof details === 'string') {
    try { parsed = JSON.parse(details) } catch { return details }
  }
  if (parsed && typeof parsed === 'object') {
    let msg = typeof parsed.message === 'string' ? parsed.message : ''
    const ch = parsed.changes
    if (ch && typeof ch === 'object' && !Array.isArray(ch)) {
      const parts = Object.entries(ch).map(([field, v]) => {
        const hasPair = v && typeof v === 'object' && ('old' in v || 'new' in v)
        const from = hasPair ? v.old : v?.[0]
        const to = hasPair ? v.new : v?.[1]
        return `${field}: ${from} → ${to}`
      })
      if (parts.length) msg += (msg ? ' — ' : '') + parts.join(', ')
    }
    return msg
  }
  return typeof parsed === 'string' ? parsed : String(parsed)
}

export function formatAuditLog(log) {
  const hasTarget = log.target_type && log.target_type !== 'system'
  return {
    id: log.id,
    time: log.created_at ? new Date(log.created_at).toLocaleString() : '—',
    admin: log.admin?.name || '—',
    action: log.action,
    actionLabel: AUDIT_ACTION_LABELS[log.action] ?? titleCase(log.action),
    target: !hasTarget ? '—' : (log.target_id ? `${log.target_type} #${log.target_id}` : log.target_type),
    message: detailsMessage(log.details),
  }
}
