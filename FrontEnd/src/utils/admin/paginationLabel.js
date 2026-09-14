export function paginationLabel({ currentPage, perPage, total }) {
  if (!total) return 'No results'
  const from = (currentPage - 1) * perPage + 1
  const to = Math.min(currentPage * perPage, total)
  return `Showing ${from}-${to} of ${total}`
}

export function paginationBounds({ currentPage, perPage, total }) {
  if (!total) return { from: 0, to: 0 }
  return {
    from: (currentPage - 1) * perPage + 1,
    to: Math.min(currentPage * perPage, total),
  }
}

/**
 * Keep the page control compact while always exposing the first, current,
 * neighbouring, and last pages. Missing ranges are represented by ellipses.
 */
export function paginationItems(currentPage, lastPage) {
  const last = Math.max(1, Number(lastPage) || 1)
  const current = Math.min(last, Math.max(1, Number(currentPage) || 1))

  if (last <= 7) return Array.from({ length: last }, (_, index) => index + 1)

  const visible = new Set([1, last, current - 1, current, current + 1])
  if (current <= 3) visible.add(2).add(3)
  if (current >= last - 2) visible.add(last - 2).add(last - 1)

  const pages = [...visible].filter(page => page >= 1 && page <= last).sort((a, b) => a - b)
  const items = []
  for (const page of pages) {
    if (items.length && page - items[items.length - 1] > 1) items.push('ellipsis')
    items.push(page)
  }
  return items
}
