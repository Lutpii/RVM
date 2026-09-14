export const DETECTION_DATE_PRESETS = [
  'all',
  'today',
  'yesterday',
  'last_7_days',
  'last_30_days',
  'custom',
]

function toLocalDateInput(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function dayOffset(now, amount) {
  return new Date(now.getFullYear(), now.getMonth(), now.getDate() + amount)
}

/**
 * Resolve calendar-day presets in the browser's local timezone. Custom returns
 * null so changing to it can preserve the currently visible range until the
 * admin edits either date field.
 */
export function detectionDateRange(preset, now = new Date()) {
  const today = toLocalDateInput(now)

  switch (preset) {
    case 'today':
      return { from: today, to: today }
    case 'yesterday': {
      const yesterday = toLocalDateInput(dayOffset(now, -1))
      return { from: yesterday, to: yesterday }
    }
    case 'last_7_days':
      return { from: toLocalDateInput(dayOffset(now, -6)), to: today }
    case 'last_30_days':
      return { from: toLocalDateInput(dayOffset(now, -29)), to: today }
    case 'all':
      return { from: '', to: '' }
    case 'custom':
    default:
      return null
  }
}
