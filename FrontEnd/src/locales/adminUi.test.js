// Admin panel text: every key the admin views use must exist, with the same
// placeholders, in English, Bahasa Melayu and Bahasa Indonesia.
import { describe, it, expect } from 'vitest'
import { readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import { adminUi } from './adminUi.js'

const read = (rel) => readFileSync(fileURLToPath(new URL(rel, import.meta.url)), 'utf-8')
const SOURCES = ['../views/AdminView.vue', '../components/admin/MaintenanceHistoryModal.vue'].map(read).join('\n')
const used = [...new Set([...SOURCES.matchAll(/adminUi\.([A-Za-z0-9_]+)['"]/g)].map((m) => m[1]))]
  .filter((k) => !k.endsWith('_')) // 'status_' + s etc.: prefixes of dynamic keys
const placeholders = (s) => [...String(s).matchAll(/\{(\w+)\}/g)].map((m) => m[1]).sort().join(',')

describe('adminUi messages', () => {
  it('finds the keys used by the admin views (sanity check)', () => {
    expect(used.length).toBeGreaterThan(200)
  })
  for (const lang of ['en', 'my', 'id']) {
    it(`"${lang}" defines every key the admin views use`, () => {
      expect(used.filter((k) => typeof adminUi[lang][k] !== 'string' || !adminUi[lang][k].trim())).toEqual([])
    })
    it(`"${lang}" keeps the English placeholders`, () => {
      const bad = Object.keys(adminUi.en).filter((k) => placeholders(adminUi[lang][k]) !== placeholders(adminUi.en[k]))
      expect(bad).toEqual([])
    })
  }
  it('has the dynamic status, transaction and audit keys', () => {
    for (const k of ['status_active', 'status_completed', 'tx_mat_aluminum', 'tx_putInBin', 'audit_update_user']) {
      expect(adminUi.id[k]).toBeTruthy()
    }
  })
})
