import { describe, it, expect } from 'vitest'
import { readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'

describe('locale file encoding', () => {
  it('index.js is valid UTF-8', () => {
    const bytes = readFileSync(fileURLToPath(new URL('./index.js', import.meta.url)))
    expect(() => new TextDecoder('utf-8', { fatal: true }).decode(bytes)).not.toThrow()
  })
})
