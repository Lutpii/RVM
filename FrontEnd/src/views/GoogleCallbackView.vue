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

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const error  = ref('')

onMounted(() => {
  const token   = route.query.token
  const userRaw = route.query.user
  const err     = route.query.error

  if (err || !token || !userRaw) {
    error.value = 'Google sign-in failed. Please try again.'
    return
  }

  try {
    const user = JSON.parse(decodeURIComponent(userRaw))
    auth.setAuth(user, token)

    const redirect = sessionStorage.getItem('rvm_post_login_redirect')
    sessionStorage.removeItem('rvm_post_login_redirect')
    router.replace(redirect || '/dashboard')
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
