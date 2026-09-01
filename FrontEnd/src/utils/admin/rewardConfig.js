export function buildRewardUpdatePayload(savedValues, editValues, material) {
  const value = editValues[material]
  if (!Number.isInteger(value) || value < 0 || value > 9999) {
    throw new Error(`Invalid value for ${material}: must be a whole number between 0 and 9999.`)
  }
  return { ...savedValues, [material]: value }
}
