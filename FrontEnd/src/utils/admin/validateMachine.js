export function validateMachineName(name) {
  if (!name?.trim()) return 'Machine name is required.'
  return null
}
