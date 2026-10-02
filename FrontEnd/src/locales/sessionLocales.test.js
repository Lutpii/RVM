import { describe, it, expect } from 'vitest'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import { messages } from './index.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const COMPACTOR_KEYS = [
  'compactorEmpty', 'compactorEmptying', 'compactorContains', 'droppingItem', 'machineFinishing',
  'switchTitle', 'switchBody', 'switchContinue', 'switchTakeBack', 'switchAutoIn',
  'stageCompacting', 'stageTilting', 'stageDropping', 'takeItemBack',
  'flapOccupied', 'flapOccupiedHint', 'machineError', 'machineErrorHint',
  'stepMaterialSwitch', 'stepCompacting', 'stepDropping', 'stepFlapCheck', 'stepMachineError',
]

describe('session.* translation keys', () => {
  const vueSource = fs.readFileSync(path.resolve(__dirname, '../views/RvmSessionView.vue'), 'utf-8')
  const usedKeys = [...new Set([...vueSource.matchAll(/t\('session\.([A-Za-z]+)/g)].map((m) => m[1]))]
  const required = [...new Set([...usedKeys, ...COMPACTOR_KEYS])]

  for (const locale of Object.keys(messages)) {
    it(`are all defined in the "${locale}" locale`, () => {
      const missing = required.filter((key) => messages[locale].session?.[key] === undefined)
      expect(missing, `locale "${locale}" is missing: ${missing.join(', ')}`).toEqual([])
    })
  }
})
