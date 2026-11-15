// Open/close rules for the landing page language menu (LanguageSwitch
// variant="menu"). refocus = move focus back to the menu button.
export function languageMenuNext(open, action) {
  switch (action) {
    case 'toggle': return { open: !open, refocus: false }
    case 'choose': return { open: false, refocus: true }
    case 'escape': return { open: false, refocus: open }
    case 'outside': return { open: false, refocus: false }
    default: return { open, refocus: false }
  }
}
