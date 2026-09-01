export function paginationLabel({ currentPage, perPage, total }) {
  if (!total) return 'No results'
  const from = (currentPage - 1) * perPage + 1
  const to = Math.min(currentPage * perPage, total)
  return `Showing ${from}-${to} of ${total}`
}
