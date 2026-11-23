import { describe, it, expect } from 'vitest'
import { createI18n } from 'vue-i18n'
import { messages } from '@/locales'
import { rewardCategoryKey, rewardCategoryLabel, rewardItemCategoryLabel } from './rewardCategory.js'

const tFor = (locale) => createI18n({ legacy: false, locale, fallbackLocale: 'en', messages }).global.t

describe('rewardCategoryKey', () => {
  it('matches the live Malay categories regardless of case and spaces', () => {
    expect(rewardCategoryKey(' Baucar Makanan ')).toBe('food')
    expect(rewardCategoryKey('E-Dompet')).toBe('ewallet')
    expect(rewardCategoryKey('baucar minuman')).toBe('drink')
    expect(rewardCategoryKey('Barangan')).toBe('merch')
    expect(rewardCategoryKey('Baucar Belanja')).toBe('shopping')
  })
  it('matches the English and Indonesian names too', () => {
    expect(rewardCategoryKey('E-Wallet')).toBe('ewallet')
    expect(rewardCategoryKey('dompet digital')).toBe('ewallet')
    expect(rewardCategoryKey('Voucher Makanan')).toBe('food')
  })
  it('returns null for a category it does not know', () => {
    expect(rewardCategoryKey('Voucher Game')).toBeNull()
    expect(rewardCategoryKey(null)).toBeNull()
  })
})

describe('rewardCategoryLabel', () => {
  it('shows a known category in the chosen language', () => {
    expect(rewardCategoryLabel('Barangan', tFor('en'))).toBe('Merchandise')
    expect(rewardCategoryLabel('E-Wallet', tFor('my'))).toBe('E-Dompet')
  })
  it('shows an unknown category exactly as the admin typed it', () => {
    expect(rewardCategoryLabel('Voucher Game', tFor('en'))).toBe('Voucher Game')
  })
})

describe('rewardItemCategoryLabel', () => {
  it('prefers the admin-typed translation for the current language', () => {
    const item = { category: 'Baucar Makanan', category_en: 'Meal Deals' }
    expect(rewardItemCategoryLabel(item, 'en', tFor('en'))).toBe('Meal Deals')
  })
  it('uses the built-in label for a known category without a translation', () => {
    const item = { category: 'Baucar Makanan', category_en: '' }
    expect(rewardItemCategoryLabel(item, 'en', tFor('en'))).toBe('Food Voucher')
  })
  it('translates a new category the built-in list does not know', () => {
    const item = { category: 'Tiket Wayang', category_id: 'Tiket Bioskop' }
    expect(rewardItemCategoryLabel(item, 'id', tFor('id'))).toBe('Tiket Bioskop')
  })
  it('shows a new category as typed when it has no translation', () => {
    const item = { category: 'Tiket Wayang' }
    expect(rewardItemCategoryLabel(item, 'en', tFor('en'))).toBe('Tiket Wayang')
  })
})
