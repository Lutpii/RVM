import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { registerClearAuth } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('rvm_user') || 'null'))
  const token = ref(localStorage.getItem('rvm_token') || null)

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')

  function setAuth(userData, tokenValue) {
    // Restore cached points if backend returns a lower value (e.g. session just ended)
    const cachedPts = localStorage.getItem(`rvm_pts_${userData.id}`)
    if (cachedPts !== null) {
      const cached = parseInt(cachedPts)
      if (!isNaN(cached) && cached > (userData.total_points ?? 0)) {
        userData = { ...userData, total_points: cached }
      }
      localStorage.removeItem(`rvm_pts_${userData.id}`)
    }
    user.value  = userData
    token.value = tokenValue
    localStorage.setItem('rvm_user', JSON.stringify(userData))
    localStorage.setItem('rvm_token', tokenValue)
    api.defaults.headers.common['Authorization'] = `Bearer ${tokenValue}`
  }

  function clearAuth() {
    // Save points per-user before clearing so re-login restores them if backend is stale
    if (user.value?.id != null && user.value?.total_points != null) {
      localStorage.setItem(`rvm_pts_${user.value.id}`, user.value.total_points)
    }
    user.value  = null
    token.value = null
    localStorage.removeItem('rvm_user')
    localStorage.removeItem('rvm_token')
    delete api.defaults.headers.common['Authorization']
  }

  async function login(credentials) {
    const res = await api.post('/auth/login', credentials)
    if (res.data.success) {
      setAuth(res.data.user, res.data.token)
    }
    return res.data
  }

  async function register(data) {
    const res = await api.post('/auth/register', data)
    if (res.data.success) {
      setAuth(res.data.user, res.data.token)
    }
    return res.data
  }

  async function loginWithGoogle() {
    const res = await api.get('/auth/google/redirect')
    if (res.data.url) {
      window.location.href = res.data.url
    }
  }

  async function sendOtp(phone) {
    const res = await api.post('/auth/send-otp', { phone })
    return res.data
  }

  async function verifyOtp(phone, otp) {
    const res = await api.post('/auth/verify-otp', { phone, otp })
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
    login, register, loginWithGoogle, sendOtp, verifyOtp, logout, fetchMe, setAuth, clearAuth, updatePoints,
  }
})
