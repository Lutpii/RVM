import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000,
})

// Kiosk builds only: the machine's API key (php artisan rvm:machine-key <code>)
// identifies this machine on /hardware/* calls and counts as a heartbeat.
// Empty in normal web builds, so phones/laptops never send it.
const MACHINE_KEY = import.meta.env.VITE_MACHINE_KEY || ''
if (MACHINE_KEY) api.defaults.headers.common['X-Machine-Key'] = MACHINE_KEY
export const hasMachineKey = MACHINE_KEY !== ''

// Registered by auth store so the interceptor can clear Pinia state on 401
let _clearAuthFn = null
export function registerClearAuth(fn) { _clearAuthFn = fn }

// Sends the user's chosen UI language on every request so the backend can
// localize response messages (login/OTP/session errors, step messages,
// etc.) — read fresh per-request rather than cached, since the user can
// switch language mid-session via UserSettingsView without a page reload.
api.interceptors.request.use((config) => {
  config.headers['X-App-Locale'] = localStorage.getItem('rvm_lang') || 'en'
  return config
})

const KIOSK_TOKEN_STORAGE_KEY = 'rvm_kiosk_token'

function readStoredKioskToken() {
  if (typeof sessionStorage === 'undefined') return null
  try {
    return sessionStorage.getItem(KIOSK_TOKEN_STORAGE_KEY)
  } catch {
    return null
  }
}

const storedKioskToken = readStoredKioskToken()
let _kioskMode = !!storedKioskToken
if (storedKioskToken) {
  api.defaults.headers.common['X-Kiosk-Token'] = storedKioskToken
}

export function setKioskToken(token) {
  _kioskMode = !!token
  if (token) {
    api.defaults.headers.common['X-Kiosk-Token'] = token
    try { sessionStorage.setItem(KIOSK_TOKEN_STORAGE_KEY, token) } catch { /* unavailable */ }
  } else {
    delete api.defaults.headers.common['X-Kiosk-Token']
    try { sessionStorage.removeItem(KIOSK_TOKEN_STORAGE_KEY) } catch { /* unavailable */ }
  }
}

// Response interceptor — skip login redirect when kiosk token is active
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // A suspended account (A08) is signed out the same way as an expired one,
    // but lands on the login page with an explanation.
    const suspended = error.response?.status === 403 && error.response?.data?.account_suspended === true
    if ((error.response?.status === 401 || suspended) && !_kioskMode) {
      if (_clearAuthFn) {
        _clearAuthFn()
      } else {
        localStorage.removeItem('rvm_token')
        localStorage.removeItem('rvm_user')
      }
      window.location.hash = suspended ? '/login?error=account_suspended' : '/login'
    }
    return Promise.reject(error)
  }
)

export default api
