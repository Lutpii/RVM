import { describe, expect, it } from 'vitest'
import { DETECTION_DATE_PRESETS, detectionDateRange } from './detectionDateRange.js'

describe('Detection Review date presets', () => {
  const now = new Date(2026, 8, 14, 15, 30)

  it('offers only the supported presets in UI order', () => {
    expect(DETECTION_DATE_PRESETS).toEqual([
      'all', 'today', 'yesterday', 'last_7_days', 'last_30_days', 'custom',
    ])
  })

  it('uses inclusive local calendar-day ranges', () => {
    expect(detectionDateRange('today', now)).toEqual({ from: '2026-09-14', to: '2026-09-14' })
    expect(detectionDateRange('yesterday', now)).toEqual({ from: '2026-09-13', to: '2026-09-13' })
    expect(detectionDateRange('last_7_days', now)).toEqual({ from: '2026-09-08', to: '2026-09-14' })
    expect(detectionDateRange('last_30_days', now)).toEqual({ from: '2026-08-16', to: '2026-09-14' })
  })

  it('leaves all dates empty and preserves custom ranges', () => {
    expect(detectionDateRange('all', now)).toEqual({ from: '', to: '' })
    expect(detectionDateRange('custom', now)).toBeNull()
  })
})
