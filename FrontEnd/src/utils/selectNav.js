// Keyboard movement inside an open AppSelect list. `enabled[i]` is false for a
// disabled option; movement skips those and never wraps around.
export function nextOptionIndex(current, key, enabled) {
  const ids = enabled.map((on, i) => (on ? i : -1)).filter((i) => i >= 0)
  if (!ids.length) return current
  if (key === 'Home') return ids[0]
  if (key === 'End') return ids[ids.length - 1]
  if (key === 'ArrowDown') return ids.find((i) => i > current) ?? (current < 0 ? ids[0] : current)
  if (key === 'ArrowUp') return [...ids].reverse().find((i) => i < current) ?? (current < 0 ? ids[0] : current)
  return current
}

// Label shown on the closed select. Strict comparison keeps 0 / false / '' apart.
export function optionLabel(options, value, placeholder = '') {
  return options.find((o) => o.value === value)?.label ?? placeholder
}
