import { describe, it, expect } from 'vitest'
import { rewardText } from './rewardText'

// Reward cards are written in Malay (the base name/description/category
// columns); *_en and *_id hold optional translations.
const item = {
  name: 'Baucar GrabFood RM5',
  name_en: 'GrabFood Voucher RM5',
  name_id: 'Voucher GrabFood RM5',
  description: 'Baucar GrabFood bernilai RM5.',
  description_en: 'A GrabFood voucher worth RM5.',
  description_id: null,
  category: 'Baucar Makanan',
  category_en: 'Food Voucher',
  category_id: '   ',
}

describe('rewardText', () => {
  it('returns the Malay base text for the my locale', () => {
    expect(rewardText(item, 'name', 'my')).toBe('Baucar GrabFood RM5')
  })

  it('returns the English translation for the en locale', () => {
    expect(rewardText(item, 'name', 'en')).toBe('GrabFood Voucher RM5')
    expect(rewardText(item, 'category', 'en')).toBe('Food Voucher')
  })

  it('returns the Indonesian translation for the id locale', () => {
    expect(rewardText(item, 'name', 'id')).toBe('Voucher GrabFood RM5')
  })

  it('falls back to Malay when the translation is missing or blank', () => {
    expect(rewardText(item, 'description', 'id')).toBe('Baucar GrabFood bernilai RM5.')
    expect(rewardText(item, 'category', 'id')).toBe('Baucar Makanan')
  })

  it('falls back to Malay for an unknown locale', () => {
    expect(rewardText(item, 'name', 'fr')).toBe('Baucar GrabFood RM5')
  })

  it('returns an empty string when the item or field is missing', () => {
    expect(rewardText(null, 'name', 'en')).toBe('')
    expect(rewardText({ name: 'X' }, 'description', 'en')).toBe('')
  })
})
