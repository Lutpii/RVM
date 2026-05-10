<template>
  <div class="auth-page">
    <div class="auth-card">
      <!-- Header -->
      <div class="auth-header">
        <RouterLink to="/" class="back-btn">←</RouterLink>
        <div class="header-controls">
          <button class="ctrl-btn" @click="toggleTheme()">{{ theme === 'dark' ? '☀️' : '🌙' }}</button>
          <button class="ctrl-btn" @click="toggleLang">{{ locale === 'en' ? 'MY' : 'EN' }}</button>
        </div>
        <h1>{{ $t('app.name') }}</h1>
        <p>{{ $t('auth.loginTitle') }}</p>
      </div>

      <div class="auth-body">
        <!-- Google Login -->
        <button class="google-btn" @click="handleGoogle" :disabled="loading">
          <svg width="18" height="18" viewBox="0 0 18 18">
            <path fill="#4285F4"
              d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" />
            <path fill="#34A853"
              d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" />
            <path fill="#FBBC05"
              d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" />
            <path fill="#EA4335"
              d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" />
          </svg>
          {{ $t('auth.googleBtn') }}
        </button>

        <div class="separator"><span>{{ $t('auth.orSeparator') }}</span></div>

        <!-- Tab switcher -->
        <div class="tab-group">
          <button :class="['tab', { active: loginMethod === 'email' }]" @click="loginMethod = 'email'">Email</button>
          <button :class="['tab', { active: loginMethod === 'phone' }]" @click="loginMethod = 'phone'">WhatsApp</button>
        </div>

        <!-- Email Login -->
        <form v-if="loginMethod === 'email'" @submit.prevent="handleEmailLogin" class="auth-form">
          <div class="form-group">
            <label>{{ $t('auth.email') }}</label>
            <input v-model="form.email" type="email" :placeholder="$t('auth.email')" required />
          </div>
          <div class="form-group">
            <label>{{ $t('auth.password') }}</label>
            <div class="password-wrap">
              <input v-model="form.password" :type="showPwd ? 'text' : 'password'" :placeholder="$t('auth.password')"
                required />
              <button type="button" class="pwd-toggle" @click="showPwd = !showPwd">{{ showPwd ? '🙈' : '👁' }}</button>
            </div>
          </div>
          <div v-if="error" class="error-msg">{{ error }}</div>
          <button type="submit" class="submit-btn" :disabled="loading">
            <span v-if="loading" class="spinner"></span>
            {{ loading ? '...' : $t('auth.loginBtn') }}
          </button>
        </form>

        <!-- WhatsApp OTP Login -->
        <div v-else class="auth-form">
          <div v-if="!otpSent">
            <div class="form-group">
              <label>{{ $t('auth.phone') }}</label>
              <input v-model="form.phone" type="tel" placeholder="e.g. 0812345678" />
            </div>
            <div v-if="error" class="error-msg">{{ error }}</div>
            <button class="submit-btn whatsapp-btn" @click="handleSendOtp" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              {{ loading ? '...' : $t('auth.sendOtp') }}
            </button>
          </div>
          <div v-else>
            <p class="otp-info">{{ $t('auth.otpSent') }}</p>
            <div class="form-group">
              <label>OTP Code</label>
              <input v-model="form.otp" type="text" maxlength="6" placeholder="Enter 6-digit OTP" class="otp-input" />
            </div>
            <div v-if="error" class="error-msg">{{ error }}</div>
            <button class="submit-btn" @click="handleVerifyOtp" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              {{ loading ? '...' : $t('auth.verifyOtp') }}
            </button>
            <button class="resend-btn" @click="otpSent = false">Resend OTP</button>
          </div>
        </div>

        <!-- Register link -->
        <p class="switch-link">
          {{ $t('auth.noAccount') }}
          <RouterLink to="/register">{{ $t('auth.registerBtn') }}</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const theme = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale } = useI18n()

const loginMethod = ref('email')
const form = ref({ email: '', password: '', phone: '', otp: '' })
const loading = ref(false)
const error = ref('')
const otpSent = ref(false)
const showPwd = ref(false)

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

async function handleEmailLogin() {
  loading.value = true
  error.value = ''
  try {
    const res = await auth.login({ email: form.value.email, password: form.value.password })
    if (res.success) {
      router.push(route.query.redirect || '/dashboard')
    } else {
      error.value = res.message || 'Login failed.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed.'
  } finally {
    loading.value = false
  }
}

async function handleGoogle() {
  try { await auth.loginWithGoogle() } catch { }
}

async function handleSendOtp() {
  if (!form.value.phone) { error.value = 'Please enter phone number.'; return }
  loading.value = true
  error.value = ''
  try {
    const res = await auth.sendOtp(form.value.phone)
    if (res.success) { otpSent.value = true }
    else { error.value = res.message }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to send OTP.'
  } finally {
    loading.value = false
  }
}

async function handleVerifyOtp() {
  loading.value = true
  error.value = ''
  try {
    const res = await auth.verifyOtp(form.value.phone, form.value.otp)
    if (res.success) { router.push(route.query.redirect || '/dashboard') }
    else { error.value = res.message }
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid OTP.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  padding: 20px;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--bg-secondary);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow);
}

.auth-header {
  background: var(--grad-header);
  padding: 24px 20px 20px;
  position: relative;
  text-align: center;
}

.back-btn {
  position: absolute;
  left: 16px;
  top: 16px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  font-size: 14px;
}

.header-controls {
  position: absolute;
  right: 16px;
  top: 12px;
  display: flex;
  gap: 6px;
}

.ctrl-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 4px 10px;
  border-radius: 16px;
  cursor: pointer;
  font-size: 12px;
}

.auth-header h1 {
  color: white;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 6px;
}

.auth-header p {
  color: rgba(255, 255, 255, 0.85);
  font-size: 14px;
}

.auth-body {
  padding: 24px 20px;
}

.google-btn {
  width: 100%;
  padding: 12px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  color: var(--text-primary);
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: background 0.2s;
}

.google-btn:hover {
  background: var(--bg-hover);
}

.separator {
  text-align: center;
  position: relative;
  margin: 16px 0;
  color: var(--text-muted);
  font-size: 12px;
}

.separator::before,
.separator::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 42%;
  height: 1px;
  background: var(--border);
}

.separator::before {
  left: 0;
}

.separator::after {
  right: 0;
}

.tab-group {
  display: flex;
  background: var(--bg-card);
  border-radius: 8px;
  padding: 3px;
  margin-bottom: 16px;
}

.tab {
  flex: 1;
  padding: 8px;
  border: none;
  background: transparent;
  color: var(--text-secondary);
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.tab.active {
  background: var(--accent-blue);
  color: white;
  font-weight: 600;
}

.form-group {
  margin-bottom: 14px;
}

.form-group label {
  display: block;
  font-size: 13px;
  color: var(--text-secondary);
  margin-bottom: 6px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 11px 14px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 15px;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus {
  border-color: var(--accent-blue);
}

.password-wrap {
  position: relative;
}

.pwd-toggle {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
}

.error-msg {
  background: rgba(239, 68, 68, 0.1);
  color: var(--accent-red);
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 12px;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.submit-btn {
  width: 100%;
  padding: 13px;
  background: var(--accent-blue);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: opacity 0.2s;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.submit-btn:not(:disabled):hover {
  opacity: 0.9;
}

.whatsapp-btn {
  background: #25D366;
}

.otp-info {
  color: var(--accent-green);
  font-size: 13px;
  margin-bottom: 14px;
  text-align: center;
}

.otp-input {
  text-align: center;
  font-size: 24px !important;
  letter-spacing: 8px;
  font-weight: 700;
}

.resend-btn {
  width: 100%;
  padding: 10px;
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-secondary);
  cursor: pointer;
  margin-top: 8px;
  font-size: 13px;
}

.switch-link {
  text-align: center;
  margin-top: 16px;
  font-size: 14px;
  color: var(--text-secondary);
}

.switch-link a {
  color: var(--accent-blue);
  text-decoration: none;
  font-weight: 500;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ── Responsive ── */
@media (max-width: 480px) {
  .auth-page { padding: 12px; align-items: flex-start; padding-top: 20px; }
  .auth-card { border-radius: 12px; }
  .auth-header { padding: 20px 16px 14px; }
  .auth-header h1 { font-size: 20px; }
  .auth-body { padding: 16px; }
  .otp-inputs input { width: 36px; height: 44px; font-size: 20px; }
}

@media (max-width: 360px) {
  .auth-page { padding: 8px; }
  .auth-header h1 { font-size: 18px; }
  .otp-inputs input { width: 32px; height: 40px; font-size: 18px; }
}
</style>
