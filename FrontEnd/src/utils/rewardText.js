// Picks a reward card's text for the current UI language. Admins write the
// card in Malay (name / description / category); name_en, name_id, etc. are
// optional translations. A missing or blank translation falls back to Malay,
// so a card never shows up empty.
const TRANSLATED_LOCALES = ['en', 'id']

export function rewardText(item, field, locale) {
  if (!item) return ''
  if (TRANSLATED_LOCALES.includes(locale)) {
    const translated = item[`${field}_${locale}`]
    if (typeof translated === 'string' && translated.trim()) return translated
  }
  return item[field] ?? ''
}
