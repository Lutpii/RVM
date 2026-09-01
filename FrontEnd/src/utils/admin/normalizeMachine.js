const BIN_LEVEL_KEYS = ['aluminum_level', 'plastic_level', 'glass_level', 'paper_level']

export function normalizeMachine(machine) {
  const normalized = { ...machine }
  for (const key of BIN_LEVEL_KEYS) {
    if (normalized[key] == null) normalized[key] = 0
  }
  return normalized
}
