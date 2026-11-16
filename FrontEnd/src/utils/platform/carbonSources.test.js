import { describe, expect, it } from 'vitest'
import { formatSourceLine } from './carbonSources.js'

function t(key, values) {
  const translations = {
    'platform.sources.assumedMass': () => `${values.grams} g`,
    'platform.sources.perKg': () => 'per kg of waste',
    'platform.sources.factor': () => `${values.factor} kg CO₂e/kg`,
    'platform.sources.derived': () => 'derived from abstract',
  }
  return translations[key]()
}

describe('formatSourceLine', () => {
  it('shows mass, factor, reference and the localized derived suffix once', () => {
    expect(formatSourceLine({
      mass_g: 20,
      kg_co2e_per_kg: 0.6,
      reference: 'Lele et al. 2026 (derived from abstract)',
      derived: true,
    }, t)).toBe('20 g × 0.6 kg CO₂e/kg · Lele et al. 2026 (derived from abstract)')
  })

  it('uses the per-kg basis when the input has no assumed item mass', () => {
    expect(formatSourceLine({
      mass_g: null,
      kg_co2e_per_kg: 0.44,
      reference: 'Nordahl et al. 2020',
      derived: false,
    }, t)).toBe('per kg of waste × 0.44 kg CO₂e/kg · Nordahl et al. 2020')
  })
})
