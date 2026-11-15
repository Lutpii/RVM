import { describe, it, expect } from 'vitest'
import { ref } from 'vue'
import { LANGUAGES, resolveStoredLanguage, intlLocale, applyLanguage, htmlLang } from './useLanguage.js'

const fakeStorage = (value) => {
  const data = { rvm_lang: value }
  return { getItem: (k) => data[k] ?? null, setItem: (k, v) => { data[k] = v }, data }
}

describe('LANGUAGES', () => {
  it('lists English, Bahasa Melayu and Bahasa Indonesia in that order', () => {
    expect(LANGUAGES).toEqual([
      { code: 'en', short: 'EN', label: 'English' },
      { code: 'my', short: 'MY', label: 'Bahasa Melayu' },
      { code: 'id', short: 'ID', label: 'Bahasa Indonesia' },
    ])
  })
})

describe('resolveStoredLanguage', () => {
  it('keeps a supported stored language', () => {
    expect(resolveStoredLanguage(fakeStorage('id'))).toBe('id')
    expect(resolveStoredLanguage(fakeStorage('my'))).toBe('my')
  })
  it('falls back to English for unknown, missing or unreadable values', () => {
    expect(resolveStoredLanguage(fakeStorage('ms'))).toBe('en')
    expect(resolveStoredLanguage(fakeStorage('xx'))).toBe('en')
    expect(resolveStoredLanguage(fakeStorage(undefined))).toBe('en')
    expect(resolveStoredLanguage({ getItem: () => { throw new Error('blocked') } })).toBe('en')
    expect(resolveStoredLanguage(undefined)).toBe('en')
  })
})

describe('intlLocale', () => {
  it('maps each language to its Intl locale', () => {
    expect(intlLocale('id')).toBe('id-ID')
    expect(intlLocale('my')).toBe('ms-MY')
    expect(intlLocale('en')).toBe('en-GB')
    expect(intlLocale('xx')).toBe('en-GB')
  })
})

describe('htmlLang', () => {
  it('uses the ISO code for Malay', () => {
    expect(htmlLang('my')).toBe('ms')
    expect(htmlLang('id')).toBe('id')
    expect(htmlLang('en')).toBe('en')
  })
})

describe('applyLanguage', () => {
  it('sets the i18n locale, the stored choice and the html lang', () => {
    const locale = ref('en')
    const storage = fakeStorage('en')
    const doc = { documentElement: { lang: 'en' } }
    expect(applyLanguage('id', { locale, storage, doc })).toBe(true)
    expect(locale.value).toBe('id')
    expect(storage.data.rvm_lang).toBe('id')
    expect(doc.documentElement.lang).toBe('id')
  })
  it('ignores an unknown code', () => {
    const locale = ref('en')
    const storage = fakeStorage('en')
    expect(applyLanguage('xx', { locale, storage, doc: undefined })).toBe(false)
    expect(locale.value).toBe('en')
    expect(storage.data.rvm_lang).toBe('en')
  })
  it('still switches when storage is blocked', () => {
    const locale = ref('en')
    const storage = { setItem: () => { throw new Error('blocked') } }
    expect(applyLanguage('my', { locale, storage })).toBe(true)
    expect(locale.value).toBe('my')
  })
})
