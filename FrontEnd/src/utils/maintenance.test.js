import { describe, expect, it } from 'vitest'
import { isMaintenance, maintenanceText } from './maintenance.js'
import { LANGUAGES } from '../composables/useLanguage.js'

describe('isMaintenance', () => {
  it('blocks production builds while the switch is on', () => {
    expect(isMaintenance({ PROD: true }, true)).toBe(true)
  })

  it('lets production builds through once the switch is off', () => {
    expect(isMaintenance({ PROD: true }, false)).toBe(false)
  })

  it('never blocks local dev unless previewing', () => {
    expect(isMaintenance({ PROD: false }, true)).toBe(false)
    expect(isMaintenance({ PROD: false, VITE_MAINTENANCE_PREVIEW: 'true' }, false)).toBe(true)
  })

  it('lets one deploy opt out with VITE_MAINTENANCE_MODE=false', () => {
    expect(isMaintenance({ PROD: true, VITE_MAINTENANCE_MODE: 'false' }, true)).toBe(false)
  })
})

describe('maintenanceText', () => {
  it('has every string in every app language', () => {
    for (const { code } of LANGUAGES) {
      expect(Object.keys(maintenanceText[code]).sort()).toEqual(['body', 'safe', 'tab', 'title'])
    }
  })
})
