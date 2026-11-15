import { describe, it, expect } from 'vitest'
import { languageMenuNext } from './languageMenu.js'

describe('languageMenuNext (landing page language menu)', () => {
  it('toggle opens a closed menu and closes an open one', () => {
    expect(languageMenuNext(false, 'toggle')).toEqual({ open: true, refocus: false })
    expect(languageMenuNext(true, 'toggle')).toEqual({ open: false, refocus: false })
  })
  it('choosing a language closes it and returns focus to the button', () => {
    expect(languageMenuNext(true, 'choose')).toEqual({ open: false, refocus: true })
  })
  it('Escape closes an open menu and returns focus to the button', () => {
    expect(languageMenuNext(true, 'escape')).toEqual({ open: false, refocus: true })
  })
  it('Escape on a closed menu does nothing', () => {
    expect(languageMenuNext(false, 'escape')).toEqual({ open: false, refocus: false })
  })
  it('a tap outside closes it without stealing focus', () => {
    expect(languageMenuNext(true, 'outside')).toEqual({ open: false, refocus: false })
  })
})
