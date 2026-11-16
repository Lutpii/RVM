// Home station cards on wide screens: fixed columns filled left, right, left…
// Unlike CSS columns, a card never jumps to the other column when a place opens.
export function splitColumns(items, count) {
  const columns = Array.from({ length: Math.max(1, count) }, () => [])
  items.forEach((item, i) => columns[i % columns.length].push(item))
  return columns
}
