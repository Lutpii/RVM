import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { registerClearAuth } from '@/services/api'
import { resolveCachedPoints } from '@/utils/resolveCachedPoints'
import { resetIdleActivity, clearIdleActivity } from '@/utils/idleActivity'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('rvm_user') || 'null'))
  const token = ref(localStorage.getItem('rvm_token') || null)

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')

  function setAuth(userData, tokenValue) {
    // Restore cached points if backend returns a lower value (e.g. a kiosk
    // session just ended and logout raced ahead of the backend committing the
    // points update) — see resolveCachedPoints for why this only trusts a
    // short, recent window rather than any cached value.
    const raw = localStorage.getItem(`rvm_pts_${userData.id}`)
    if (raw !== null) {
      const resolved = resolveCachedPoints(raw, userData.total_points)
      if (resolved !== userData.total_points) {
        userData = { ...userData, total_points: resolved }
      }
      localStorage.removeItem(`rvm_pts_${userData.id}`)
    }
    user.value  = userData
    token.value = tokenValue
    localStorage.setItem('rvm_user', JSON.stringify(userData))
    localStorage.setItem('rvm_token', tokenValue)
    api.defaults.headers.common['Authorization'] = `Bearer ${tokenValue}`
    // A fresh session is "active right now" regardless of whatever idle-clock
    // timestamp (or none at all) was left over from before — otherwise a
    // stale value can make useIdleLogout think 30+ minutes already passed
    // and force-expire the session the instant it arms.
    resetIdleActivity()
  }

  // Used when entering the kiosk flow (see router/index.js) — a kiosk screen
  // must never inherit a regular Bearer session left in this browser's
  // storage (e.g. from testing both the phone and kiosk flows on one
  // machine). Unlike clearAuth(), this never touches localStorage: it only
  // clears this tab's in-memory/axios state, so a real user's own login is
  // untouched if they navigate back to their own dashboard.
  function suspendForKiosk() {
    user.value  = null
    token.value = null
    delete api.defaults.headers.common['Authorization']
  }

  // Undoes suspendForKiosk() when navigating back out of the kiosk flow —
  // re-reads whatever this browser's own login (if any) is from localStorage,
  // since suspendForKiosk() never touched storage, only the reactive state.
  function rehydrate() {
    const storedUser  = JSON.parse(localStorage.getItem('rvm_user') || 'null')
    const storedToken = localStorage.getItem('rvm_token') || null
    user.value  = storedUser
    token.value = storedToken
    if (storedToken) {
      api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
    }
  }

  function clearAuth() {
    // Save points per-user before clearing so re-login restores them if backend is stale.
    // setAuth() only honors this within CACHE_MAX_AGE_MS of savedAt.
    if (user.value?.id != null && user.value?.total_points != null) {
      localStorage.setItem(`rvm_pts_${user.value.id}`, JSON.stringify({
        points: user.value.total_points, savedAt: Date.now(),
      }))
    }
    user.value  = null
    token.value = null
    localStorage.removeItem('rvm_user')
    localStorage.removeItem('rvm_token')
    delete api.defaults.headers.common['Authorization']
    clearIdleActivity()
  }

  async function login(credentials) {
    const res = await api.post('/auth/login', credentials)
    if (res.data.success) {
      setAuth(res.data.user, res.data.token)
    }
    return res.data
  }

  // Never calls setAuth() — OTP verification is mandatory before an account
  // is usable at all (see AuthController::register(), which intentionally
  // stopped issuing a token here). verifyOtp() below is what actually starts
  // the session, whether reached from the register or login flow.
  async function register(data) {
    const res = await api.post('/auth/register', data)
    return res.data
  }

  async function loginWithGoogle() {
    // Reached through the Vite dev proxy, which rewrites the Host header to
    // its own backend target — the backend can't infer a caller-reachable
    // address from that alone (e.g. a phone on the same hotspot), so this
    // browser's own known-good hostname is passed through explicitly.
    //
    // `origin` (scheme+host+port) lets the backend send the browser back to
    // wherever this login actually started — localhost, a LAN IP, a phone on
    // the hotspot — instead of one hardcoded FRONTEND_URL that only matches
    // whichever of those was true when .env was last edited. The backend only
    // honors this for a private-network/localhost origin (see
    // AuthController::sanitizeFrontendOrigin) — never a public one.
    const res = await api.get('/auth/google/redirect', {
      params: { host: window.location.hostname, frontend: window.location.origin },
    })
    if (res.data.url) {
      window.location.href = res.data.url
    }
  }

  // identifier is { phone } or { email } — whichever the account was
  // registered/verifying with (backend sends the OTP via WhatsApp for a
  // phone, email otherwise).
  async function sendOtp(identifier) {
    const res = await api.post('/auth/send-otp', identifier)
    return res.data
  }

  async function verifyOtp(identifier, otp) {
    const res = await api.post('/auth/verify-otp', { ...identifier, otp })
    if (res.data.success) {
      setAuth(res.data.user, res.data.token)
    }
    return res.data
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {}
    clearAuth()
  }

  async function fetchMe() {
    try {
      if (token.value) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
        const res = await api.get('/auth/me')
        if (res.data.success) {
          user.value = res.data.user
          localStorage.setItem('rvm_user', JSON.stringify(res.data.user))
        }
      }
    } catch {
      // 401 is already handled by the Axios interceptor (clears token + redirects).
      // For network errors, keep existing auth state so points are not lost.
    }
  }

  function updatePoints(newTotal) {
    if (!user.value) return
    user.value = { ...user.value, total_points: newTotal }
    localStorage.setItem('rvm_user', JSON.stringify(user.value))
  }

  // Initialize auth header on store creation
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  // Register clearAuth so the Axios interceptor can clear Pinia state on 401
  registerClearAuth(clearAuth)

  return {
    user, token, isLoggedIn, isAdmin,
    login, register, loginWithGoogle, sendOtp, verifyOtp, logout, fetchMe, setAuth, clearAuth, suspendForKiosk, rehydrate, updatePoints,
  }
})
