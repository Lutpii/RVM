import { describe, expect, it } from 'vitest'
import { messages } from './index.js'

const flattenKeys = (value, prefix = '') => Object.entries(value).flatMap(([key, child]) => {
  const path = prefix ? `${prefix}.${key}` : key
  return child && typeof child === 'object' ? flattenKeys(child, path) : [path]
})

describe('Detection Review translations', () => {
  it('keeps English and Bahasa Melayu keys in sync', () => {
    const englishKeys = flattenKeys(messages.en.admin.detectionReview).sort()
    const malayKeys = flattenKeys(messages.my.admin.detectionReview).sort()
    expect(malayKeys).toEqual(englishKeys)
  })

  it('contains exactly the agreed eight actual materials', () => {
    expect(Object.keys(messages.en.admin.detectionReview.materials)).toEqual([
      'aluminum', 'plastic', 'glass', 'paper', 'wood', 'metal', 'brick', 'other',
    ])
  })

  it('keeps the supported date presets in the intended UI order', () => {
    expect(Object.keys(messages.en.admin.detectionReview.datePresets)).toEqual([
      'all', 'today', 'yesterday', 'last_7_days', 'last_30_days', 'custom',
    ])
  })
})
