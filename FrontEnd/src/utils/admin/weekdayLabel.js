// The admin chart gets day labels from the backend as English abbreviations
// (Carbon format 'D': Mon..Sun). Show them in the chosen language.
const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

export function weekdayLabel(label, intlLocale) {
  const i = DAYS.indexOf(label)
  if (i < 0) return label
  // 1 January 2024 was a Monday.
  return new Intl.DateTimeFormat(intlLocale, { weekday: 'short' }).format(new Date(2024, 0, 1 + i))
}
