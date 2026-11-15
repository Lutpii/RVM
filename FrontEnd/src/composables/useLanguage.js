// One place for the app's languages. The choice is stored in
// localStorage.rvm_lang (also sent to the backend as X-App-Locale) and is
// shared by the Account page, the landing page and both kiosks.
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export const LANGUAGES = [
  { code: 'en', short: 'EN', label: 'English' },
  { code: 'my', short: 'MY', label: 'Bahasa Melayu' },
  { code: 'id', short: 'ID', label: 'Bahasa Indonesia' },
]

const CODES = LANGUAGES.map((l) => l.code)
const INTL = { en: 'en-GB', my: 'ms-MY', id: 'id-ID' }
const STORAGE_KEY = 'rvm_lang'

export const isLanguage = (code) => CODES.includes(code)

export function resolveStoredLanguage(storage) {
  try {
    const stored = storage?.getItem(STORAGE_KEY)
    return isLanguage(stored) ? stored : 'en'
  } catch {
    return 'en'
  }
}

export const intlLocale = (code) => INTL[code] || INTL.en

// Our Malay code is 'my' (kept for stored preferences); HTML wants ISO 'ms'.
export const htmlLang = (code) => (code === 'my' ? 'ms' : code)

export function applyLanguage(code, { locale, storage, doc } = {}) {
  if (!isLanguage(code)) return false
  locale.value = code
  try { storage?.setItem(STORAGE_KEY, code) } catch { /* private mode: choice lasts this visit */ }
  if (doc?.documentElement) doc.documentElement.lang = htmlLang(code)
  return true
}

const browserStorage = () => { try { return window.localStorage } catch { return undefined } }

export function useLanguage() {
  const { locale } = useI18n()
  const currentLanguage = computed(() => LANGUAGES.find((l) => l.code === locale.value) || LANGUAGES[0])
  const setLanguage = (code) =>
    applyLanguage(code, { locale, storage: browserStorage(), doc: typeof document === 'undefined' ? undefined : document })
  return { locale, languages: LANGUAGES, currentLanguage, setLanguage }
}
