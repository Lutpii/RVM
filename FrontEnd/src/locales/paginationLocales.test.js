import { describe, expect, it } from 'vitest'
import { messages } from './index.js'

describe('Admin pagination translations', () => {
  it('keeps English and Bahasa Melayu keys in sync', () => {
    expect(Object.keys(messages.my.admin.pagination).sort())
      .toEqual(Object.keys(messages.en.admin.pagination).sort())
  })
})
