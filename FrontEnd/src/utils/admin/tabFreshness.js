export function isFresh(fetchedAt, now, staleMs) {
  if (!fetchedAt) return false
  return (now - fetchedAt) < staleMs
}
