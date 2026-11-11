// Plain-language labels for the admin Transactions table, so a row explains
// itself: what the item was, whether it was accepted, and why.

// Same threshold as ai_service/app.py: below this the item becomes 'unknown'.
export const AI_CONFIDENCE_MIN = 0.6

export const BIN_LETTERS = { plastic: 'A', aluminum: 'B', paper: 'C', glass: 'D' }

const MATERIAL_NAMES = {
  plastic: 'Plastic', aluminum: 'Aluminium can', paper: 'Paper', glass: 'Glass',
  // non-target classes of the model: always rejected
  bricks: 'Bricks', metal: 'Metal', wooden: 'Wooden item',
}

const RESULT_CODES = ['unknown', 'reject']

// "Not recognised" = the AI was below 60 %, so no name is shown. A confident
// reject names what the AI saw (e.g. "Bricks").
export function itemLabel(tx) {
  const m = String(tx?.material_selected || '').toLowerCase()
  const seen = String(tx?.ai_detected_type || '').toLowerCase()
  if (m === 'reject' && seen && !RESULT_CODES.includes(seen)) {
    return MATERIAL_NAMES[seen] || seen.charAt(0).toUpperCase() + seen.slice(1)
  }
  if (m === 'unknown') return 'Not recognised'
  if (m === 'reject') return 'Material not accepted'
  return MATERIAL_NAMES[m] || (m ? m.charAt(0).toUpperCase() + m.slice(1) : '—')
}

export function resultReason(tx) {
  const selected = String(tx?.material_selected || '').toLowerCase()
  const detected = String(tx?.ai_detected_type || '').toLowerCase()
  if (tx?.is_valid) {
    const letter = BIN_LETTERS[selected]
    const name = MATERIAL_NAMES[selected]
    return letter ? `Put in the ${name.toLowerCase()} bin (${letter})` : 'Put in the bin'
  }
  if (detected === 'unknown' || selected === 'unknown') return 'AI was not sure what it was (below 60%)'
  if (selected === 'reject' && detected && detected !== 'reject') {
    const noun = detected === 'wooden' ? 'wooden items' : (MATERIAL_NAMES[detected] || detected).toLowerCase()
    return `This machine does not take ${noun}`
  }
  if (detected === 'reject' || selected === 'reject') return 'This machine does not take this material'
  if (detected && selected && detected !== selected) return `AI saw ${itemLabel({ material_selected: detected }).toLowerCase()}, not ${itemLabel({ material_selected: selected }).toLowerCase()}`
  return 'Given back to the user'
}

// 'high' ≥ 80 %, 'mid' 60–79 %, 'low' < 60 % (would be rejected).
export function confidenceLevel(conf) {
  if (conf === null || conf === undefined || conf === '') return 'none'
  const c = Number(conf)
  if (!Number.isFinite(c)) return 'none'
  if (c < AI_CONFIDENCE_MIN) return 'low'
  if (c < 0.8) return 'mid'
  return 'high'
}

export function percentOf(part, total) {
  if (!total) return 0
  return Math.round((part / total) * 100)
}
