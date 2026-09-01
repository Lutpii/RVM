<template>
  <div class="callback-page">
    <div v-if="error" class="error-box">
      <p>{{ error }}</p>
      <RouterLink to="/login">Back to Login</RouterLink>
    </div>
    <div v-else class="loading-box">
      <div class="spinner"></div>
      <p>Signing you in...</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const error  = ref('')

onMounted(async () => {
  const code = route.query.code
  const err  = route.query.error

  if (err || !code) {
    error.value = 'Google sign-in failed. Please try again.'
    return
  }

  // The backend hands us a short-lived, single-use code rather than the real
  // token — this call redeems it. A code that's already been used (e.g. this
  // link was opened by someone else first) fails here instead of silently
  // signing this browser in as whoever redeemed it.
  try {
    const res = await api.post('/auth/google/exchange', { code })
    if (!res.data.success) throw new Error(res.data.message || 'exchange failed')

    auth.setAuth(res.data.user, res.data.token)

    const redirect = sessionStorage.getItem('rvm_post_login_redirect')
    sessionStorage.removeItem('rvm_post_login_redirect')
    router.replace({ path: '/welcome', query: { redirect: redirect || '/dashboard' } })
  } catch {
    error.value = 'Google sign-in failed. Please try again.'
  }
})
</script>

<style scoped>
.callback-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
}

.loading-box, .error-box {
  text-align: center;
  color: var(--text-primary);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.error-box p { margin-bottom: 12px; color: var(--accent-red); }
.error-box a { color: var(--accent-blue); text-decoration: none; }
</style>
