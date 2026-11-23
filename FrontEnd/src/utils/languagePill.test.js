import { describe, expect, it } from 'vitest'
import { pillShift } from './languagePill.js'

describe('pillShift', () => {
  const codes = ['en', 'my', 'id']

  it('slides the row so the current language sits in the round button', () => {
    expect(pillShift(codes, 'en')).toBe(2)
    expect(pillShift(codes, 'my')).toBe(1)
    expect(pillShift(codes, 'id')).toBe(0)
  })

  it('shows the last button for an unknown code', () => {
    expect(pillShift(codes, 'xx')).toBe(0)
  })
})
