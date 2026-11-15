// Plain-language labels for the admin Transactions table, so a row explains
// itself: what the item was, whether it was accepted, and why.
// Every function takes an optional translator tr(key, params); without one
// the built-in English text is used (AdminView passes t('adminUi.tx_*')).

// Same threshold as ai_service/app.py: below this the item becomes 'unknown'.
export const AI_CONFIDENCE_MIN = 0.6

export const BIN_LETTERS = { plastic: 'A', aluminum: 'B', paper: 'C', glass: 'D' }

export const TX_TEXT_EN = {
  mat_plastic: 'Plastic', mat_aluminum: 'Aluminium can', mat_paper: 'Paper', mat_glass: 'Glass',
  // non-target classes of the model: always rejected
  mat_bricks: 'Bricks', mat_metal: 'Metal', mat_wooden: 'Wooden item',
  notRecognised: 'Not recognised',
  notAccepted: 'Material not accepted',
  putInBin: 'Put in the {name} bin ({letter})',
  putInBinGeneric: 'Put in the bin',
  aiNotSure: 'AI was not sure what it was (below 60%)',
  noTake: 'This machine does not take {noun}',
  noun_wooden: 'wooden items',
  noTakeMaterial: 'This machine does not take this material',
  aiSaw: 'AI saw {seen}, not {selected}',
  givenBack: 'Given back to the user',
}

const en = (key, params = {}) =>
  String(TX_TEXT_EN[key] ?? key).replace(/\{(\w+)\}/g, (_, p) => params[p] ?? '')

const RESULT_CODES = ['unknown', 'reject']
const isKnown = (m) => `mat_${m}` in TX_TEXT_EN
const cap = (s) => s.charAt(0).toUpperCase() + s.slice(1)

// "Not recognised" = the AI was below 60 %, so no name is shown. A confident
// reject names what the AI saw (e.g. "Bricks").
export function itemLabel(tx, tr = en) {
  const m = String(tx?.material_selected || '').toLowerCase()
  const seen = String(tx?.ai_detected_type || '').toLowerCase()
  if (m === 'reject' && seen && !RESULT_CODES.includes(seen)) {
    return isKnown(seen) ? tr(`mat_${seen}`) : cap(seen)
  }
  if (m === 'unknown') return tr('notRecognised')
  if (m === 'reject') return tr('notAccepted')
  if (isKnown(m)) return tr(`mat_${m}`)
  return m ? cap(m) : '—'
}

export function resultReason(tx, tr = en) {
  const selected = String(tx?.material_selected || '').toLowerCase()
  const detected = String(tx?.ai_detected_type || '').toLowerCase()
  if (tx?.is_valid) {
    const letter = BIN_LETTERS[selected]
    return letter ? tr('putInBin', { name: tr(`mat_${selected}`).toLowerCase(), letter }) : tr('putInBinGeneric')
  }
  if (detected === 'unknown' || selected === 'unknown') return tr('aiNotSure')
  if (selected === 'reject' && detected && detected !== 'reject') {
    const noun = detected === 'wooden' ? tr('noun_wooden') : (isKnown(detected) ? tr(`mat_${detected}`) : detected).toLowerCase()
    return tr('noTake', { noun })
  }
  if (detected === 'reject' || selected === 'reject') return tr('noTakeMaterial')
  if (detected && selected && detected !== selected) {
    return tr('aiSaw', {
      seen: itemLabel({ material_selected: detected }, tr).toLowerCase(),
      selected: itemLabel({ material_selected: selected }, tr).toLowerCase(),
    })
  }
  return tr('givenBack')
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
