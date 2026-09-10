import { describe, it, expect } from 'vitest'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import { messages } from './index.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

describe('settings.* translation keys used in UserSettingsView.vue', () => {
  const vueSource = fs.readFileSync(
    path.resolve(__dirname, '../views/UserSettingsView.vue'),
    'utf-8'
  )
  const usedKeys = [...new Set(
    [...vueSource.matchAll(/settings\.[A-Za-z]+/g)].map((m) => m[0])
  )]

  it('finds at least one settings.* key referenced in the view (sanity check)', () => {
    expect(usedKeys.length).toBeGreaterThan(0)
  })

  for (const locale of Object.keys(messages)) {
    it(`are all defined in the "${locale}" locale`, () => {
      const missing = usedKeys.filter((key) => {
        const [, subKey] = key.split('.')
        return messages[locale].settings?.[subKey] === undefined
      })
      expect(missing, `locale "${locale}" is missing: ${missing.join(', ')}`).toEqual([])
    })
  }
})
