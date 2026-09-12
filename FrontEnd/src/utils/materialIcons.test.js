import { describe, it, expect } from 'vitest'
import { materialIconSvg } from './materialIcons.js'

describe('materialIconSvg', () => {
  it('returns a non-empty SVG path string for a known material', () => {
    expect(materialIconSvg('aluminum')).toContain('<path')
  })

  it('normalizes case before looking up the material', () => {
    expect(materialIconSvg('ALUMINUM')).toBe(materialIconSvg('aluminum'))
  })

  it('returns an empty string for an unrecognized material', () => {
    expect(materialIconSvg('styrofoam')).toBe('')
  })

  it('returns an empty string for null or undefined input', () => {
    expect(materialIconSvg(null)).toBe('')
    expect(materialIconSvg(undefined)).toBe('')
  })
})
