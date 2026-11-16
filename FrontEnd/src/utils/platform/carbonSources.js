export function formatSourceLine(source, t) {
  const mass = source.mass_g == null
    ? t('platform.sources.perKg')
    : t('platform.sources.assumedMass', { grams: source.mass_g })
  const factor = t('platform.sources.factor', { factor: source.kg_co2e_per_kg })
  const reference = source.reference.replace(/\s*\(derived from abstract\)$/, '')
  const suffix = source.derived ? ' (' + t('platform.sources.derived') + ')' : ''
  return mass + ' × ' + factor + ' · ' + reference + suffix
}
