export const AUDIT_ACTION_LABELS = {
  use_voucher: 'Voucher used',
  request_bin_collection: 'Bin collection requested',
  update_bin_levels: 'Bin levels reset',
  export_csv: 'Report exported',
  update_user: 'User updated',
  update_machine: 'Machine updated',
  maintenance_open: 'Maintenance opened',
  maintenance_close: 'Maintenance closed',
}

function titleCase(slug) {
  return String(slug ?? '')
    .split('_')
    .filter(Boolean)
    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ')
}

function detailsMessage(details, opts = {}) {
  if (details == null) return ''
  let parsed = details
  if (typeof details === 'string') {
    try { parsed = JSON.parse(details) } catch { return details }
  }
  if (parsed && typeof parsed === 'object') {
    // New rows carry message_key + params so the text can follow the viewer's
    // language; old rows (and unknown keys) keep the stored English message.
    const translated = parsed.message_key ? opts.message?.(parsed.message_key, parsed.params || {}) : undefined
    let msg = translated ?? (typeof parsed.message === 'string' ? parsed.message : '')
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

// opts.label(action) may return a translated label (undefined = built-in);
// opts.locale formats the time in the chosen language.
export function formatAuditLog(log, opts = {}) {
  const hasTarget = log.target_type && log.target_type !== 'system'
  const targetName = opts.target?.(log.target_type) ?? log.target_type
  return {
    id: log.id,
    time: log.created_at ? new Date(log.created_at).toLocaleString(opts.locale) : '—',
    admin: log.admin?.name || '—',
    action: log.action,
    actionLabel: opts.label?.(log.action) ?? AUDIT_ACTION_LABELS[log.action] ?? titleCase(log.action),
    target: !hasTarget ? '—' : (log.target_id ? `${targetName} #${log.target_id}` : targetName),
    message: detailsMessage(log.details, opts),
  }
}
