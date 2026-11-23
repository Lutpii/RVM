// src/utils/languagePill.js
// LanguagePill keeps every language button in one row anchored to the right
// edge. Collapsed, the row is slid right by this many button widths so that
// only the current language is visible inside the round button.
export function pillShift(codes, current) {
  const i = codes.indexOf(current)
  return i < 0 ? 0 : codes.length - 1 - i
}
