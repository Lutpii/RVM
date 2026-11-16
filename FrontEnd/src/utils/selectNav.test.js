import { describe, expect, it } from 'vitest'
import { nextOptionIndex, optionLabel } from './selectNav.js'

describe('nextOptionIndex', () => {
  const enabled = [true, false, true, true] // index 1 is disabled

  it('moves down and up, skipping disabled options, without wrapping', () => {
    expect(nextOptionIndex(0, 'ArrowDown', enabled)).toBe(2)
    expect(nextOptionIndex(3, 'ArrowDown', enabled)).toBe(3)
    expect(nextOptionIndex(2, 'ArrowUp', enabled)).toBe(0)
    expect(nextOptionIndex(0, 'ArrowUp', enabled)).toBe(0)
  })

  it('jumps to the first and last enabled option', () => {
    expect(nextOptionIndex(2, 'Home', enabled)).toBe(0)
    expect(nextOptionIndex(0, 'End', enabled)).toBe(3)
  })

  it('starts from the first enabled option when nothing is focused', () => {
    expect(nextOptionIndex(-1, 'ArrowDown', [false, true])).toBe(1)
  })

  it('ignores other keys', () => {
    expect(nextOptionIndex(2, 'a', enabled)).toBe(2)
  })
})

describe('optionLabel', () => {
  const options = [{ value: 1, label: 'Yes' }, { value: 0, label: 'No' }, { value: '', label: 'All' }]

  it('finds the label by strict value, so 0, false and empty string stay distinct', () => {
    expect(optionLabel(options, 0, 'Pick')).toBe('No')
    expect(optionLabel(options, '', 'Pick')).toBe('All')
    expect(optionLabel(options, false, 'Pick')).toBe('Pick')
  })
})
