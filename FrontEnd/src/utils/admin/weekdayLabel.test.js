import { describe, it, expect } from 'vitest'
import { weekdayLabel } from './weekdayLabel.js'

describe('weekdayLabel (chart days sent by the backend as Mon..Sun)', () => {
  it('shows the day in the chosen language', () => {
    expect(weekdayLabel('Mon', 'id-ID')).toBe(new Intl.DateTimeFormat('id-ID', { weekday: 'short' }).format(new Date(2024, 0, 1)))
    expect(weekdayLabel('Sun', 'ms-MY')).toBe(new Intl.DateTimeFormat('ms-MY', { weekday: 'short' }).format(new Date(2024, 0, 7)))
  })
  it('keeps English and leaves anything else untouched', () => {
    expect(weekdayLabel('Fri', 'en-GB')).toBe('Fri')
    expect(weekdayLabel('15/11', 'id-ID')).toBe('15/11')
  })
})
