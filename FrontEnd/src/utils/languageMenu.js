// Open/close rules for the language controls: LanguagePill (round button that
// slides open) and the Account page row menu. refocus = move focus back to the
// button that opened it.
export function languageMenuNext(open, action) {
  switch (action) {
    case 'toggle': return { open: !open, refocus: false }
    case 'choose': return { open: false, refocus: true }
    case 'escape': return { open: false, refocus: open }
    case 'outside': return { open: false, refocus: false }
    default: return { open, refocus: false }
  }
}
