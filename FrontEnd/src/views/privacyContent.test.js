import { describe, it, expect } from 'vitest'
import { content } from './privacyContent.js'

const shape = (doc) => doc.sections.map((s) => ({ text: !!s.text, items: s.items?.length ?? 0 }))

describe('privacy policy content', () => {
  for (const code of ['my', 'id']) {
    it(`"${code}" has the same sections and points as English`, () => {
      expect(content[code]).toBeTruthy()
      expect(shape(content[code])).toEqual(shape(content.en))
      for (const key of ['title', 'updated', 'intro', 'contact']) expect(content[code][key]).toBeTruthy()
    })
  }
})
