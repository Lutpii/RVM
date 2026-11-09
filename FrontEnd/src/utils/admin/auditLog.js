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
    return typeof parsed.message === 'string' ? parsed.message : ''
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
    target: hasTarget ? `${log.target_type} #${log.target_id}` : '—',
    message: detailsMessage(log.details),
  }
}
