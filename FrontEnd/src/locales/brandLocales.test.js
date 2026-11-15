// The product is called "Ultra RVM" (Prof Kamarul's emails and the SWCorp
// Exhibition Showcase Plan, Nov 2026). Every user-visible brand spot must use
// it in English, Bahasa Melayu and Bahasa Indonesia. Machine codes such as
// RVM-001 are identifiers, not the brand, and are deliberately not covered.
import { describe, it, expect } from 'vitest'
import { readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import { messages } from './index.js'
import { adminUi } from './adminUi.js'
import { content as privacy } from '../views/privacyContent.js'

const read = (rel) => readFileSync(fileURLToPath(new URL(rel, import.meta.url)), 'utf-8')
const LANGS = ['en', 'my', 'id']

describe('Ultra RVM brand', () => {
  it('app.name is "Ultra RVM" in every language', () => {
    for (const l of LANGS) expect(messages[l].app.name).toBe('Ultra RVM')
  })

  it('taglines are unchanged', () => {
    expect(messages.en.app.tagline).toBe('Smart Recycling')
    expect(messages.my.app.tagline).toBe('Kitar Semula Pintar')
    expect(messages.id.app.tagline).toBe('Daur Ulang Pintar')
  })

  it('admin sidebar title names Ultra RVM', () => {
    expect(adminUi.en.sidebarTitle).toBe('Ultra RVM Admin')
    expect(adminUi.my.sidebarTitle).toBe('Admin Ultra RVM')
    expect(adminUi.id.sidebarTitle).toBe('Admin Ultra RVM')
  })

  it('version footer names Ultra RVM', () => {
    for (const l of LANGS) expect(messages[l].settings.appVersion).toBe('Ultra RVM · v{version}')
  })

  it('voucher image title names Ultra RVM', () => {
    expect(messages.en.rewards.voucherImageTitle).toBe('Ultra RVM Reward Voucher')
    expect(messages.my.rewards.voucherImageTitle).toBe('Baucar Ganjaran Ultra RVM')
    expect(messages.id.rewards.voucherImageTitle).toBe('Voucher Hadiah Ultra RVM')
  })

  it('router tab titles name Ultra RVM, including the fallback', () => {
    const src = read('../router/index.js')
    expect(src).toContain("'Ultra RVM Kiosk'")
    expect(src).toContain("'Ultra RVM - Smart Recycling'")
    expect(src).toContain("|| 'Ultra RVM'")
    expect(src).not.toMatch(/'(Scan QR - |Guest Recycling - |Session Summary - )?RVM Kiosk'/)
  })

  it('page tab titles like "Login - ..." end with Ultra RVM, never a bare RVM', () => {
    const src = read('../router/index.js')
    expect(src).toContain("'Login - Ultra RVM'")
    expect(src).toContain("'Admin Panel - Ultra RVM'")
    expect(src).not.toMatch(/(?<!Ultra )RVM'/)
  })

  it('index.html title and description name Ultra RVM', () => {
    const html = read('../../index.html')
    expect(html).toContain('<title>Ultra RVM - Smart Recycling</title>')
    expect(html).toContain('content="Ultra RVM - Smart Recycling System"')
  })

  it('landing header shows Ultra RVM', () => {
    const vue = read('../views/LandingView.vue')
    expect(vue).toContain('>Ultra RVM</span>')
    expect(vue).toContain('aria-label="Ultra RVM, Reverse Vending Machine"')
  })

  it('privacy policy names Ultra RVM in every language', () => {
    for (const l of LANGS) {
      const text = JSON.stringify(privacy[l])
      expect(text).toContain('Ultra RVM (Reverse Vending Machine)')
      expect(text).not.toMatch(/(?<!Ultra )RVM administrators|Pentadbir RVM|Administrator RVM/)
    }
  })
})
