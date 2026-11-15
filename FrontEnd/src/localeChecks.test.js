// Dates and numbers must follow the chosen language through intlLocale()
// (composables/useLanguage.js). A two-way "=== 'my' ? ... : ..." check
// silently formats Bahasa Indonesia as English.
import { describe, it, expect } from 'vitest'
import { readdirSync, readFileSync, statSync } from 'node:fs'
import { join } from 'node:path'
import { fileURLToPath } from 'node:url'

const SRC = fileURLToPath(new URL('.', import.meta.url))

function sourceFiles(dir) {
  return readdirSync(dir).flatMap((name) => {
    const p = join(dir, name)
    if (statSync(p).isDirectory()) return name === 'locales' ? [] : sourceFiles(p)
    return /\.(vue|js)$/.test(name) && !/\.test\.js$/.test(name) ? [p] : []
  })
}

describe('locale-dependent formatting', () => {
  it('never picks the Intl locale with a two-way Malay/English check', () => {
    const offenders = sourceFiles(SRC).filter((p) => /locale(\.value)?\s*===\s*'my'\s*\?/.test(readFileSync(p, 'utf-8')))
    expect(offenders.map((p) => p.slice(SRC.length))).toEqual([])
  })
})
