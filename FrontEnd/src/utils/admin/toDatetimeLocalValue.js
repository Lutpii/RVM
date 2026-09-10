// FrontEnd/src/utils/admin/toDatetimeLocalValue.js
// The API returns valid_from/valid_until as UTC ISO strings (e.g. "...T06:30:00.000000Z").
// A <input type="datetime-local"> has no timezone concept — its value is read/written
// as literal local wall-clock digits. Slicing the UTC string directly fed UTC digits
// into a local-time field, so re-opening an edit form after saving showed a value
// shifted by the app's UTC offset, and saving again shifted it further. Reading the
// instant's LOCAL getters instead (this function) round-trips correctly.
export function toDatetimeLocalValue(isoString) {
  if (!isoString) return ''
  const d = new Date(isoString)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}
