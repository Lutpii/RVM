import { describe, it, expect } from 'vitest'
import { readFileSync } from 'node:fs'
import { messages } from './index'

describe('CO2 is always labelled on phone session screens', () => {
  for (const locale of Object.keys(messages)) {
    it(`phoneSession strings with a carbon value mention CO2 in "${locale}"`, () => {
      const bad = Object.entries(messages[locale].phoneSession)
        .filter(([, v]) => typeof v === 'string' && /\{(carbon|kg)\}/.test(v) && !v.includes('CO2'))
      expect(bad).toEqual([])
    })
  }

  it('phoneSession.summary has the labelled form', () => {
    expect(messages.en.phoneSession.summary).toBe('{items} items · +{points} pts · CO2 saved {carbon} kg')
  })

  for (const file of ['../components/ActiveSessionSheet.vue', '../views/SessionConnectedView.vue']) {
    it(`${file} has no bare kg in its template`, () => {
      const src = readFileSync(new URL(file, import.meta.url), 'utf8')
      const template = src.split('<script')[0]
      expect(template).not.toMatch(/\}\}\s*kg</)
    })
  }
})
