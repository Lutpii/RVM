import { describe, it, expect } from 'vitest'
import { REWARD_TRANSLATION_FIELDS, rewardTranslationFields } from './rewardTranslations'

describe('rewardTranslationFields', () => {
  it('lists the six optional translation columns', () => {
    expect(REWARD_TRANSLATION_FIELDS).toEqual([
      'name_en', 'name_id', 'description_en', 'description_id', 'category_en', 'category_id',
    ])
  })

  it('trims values and sends an empty string for a blank or missing one', () => {
    // '' is how the admin clears a translation (the backend turns it into null).
    expect(rewardTranslationFields({ name_en: '  GrabFood RM5  ', name_id: null, category_en: '   ' })).toEqual({
      name_en: 'GrabFood RM5', name_id: '', description_en: '', description_id: '', category_en: '', category_id: '',
    })
  })
})
