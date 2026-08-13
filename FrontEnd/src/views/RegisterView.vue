<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <RouterLink to="/" class="back-btn">←</RouterLink>
        <div class="header-controls">
          <button class="ctrl-btn" @click="toggleTheme()">{{ theme === 'dark' ? '☀️' : '🌙' }}</button>
          <button class="ctrl-btn" @click="toggleLang">{{ locale === 'en' ? 'MY' : 'EN' }}</button>
        </div>
        <h1>{{ $t('app.name') }}</h1>
        <p>{{ $t('auth.registerTitle') }}</p>
      </div>

      <div class="auth-body">
        <!-- Google -->
        <button class="google-btn" @click="handleGoogle" :disabled="loading">
          <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/><path fill="#FBBC05" d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
          {{ $t('auth.googleBtn') }}
        </button>

        <div class="separator"><span>{{ $t('auth.orSeparator') }}</span></div>

        <form @submit.prevent="handleRegister" class="auth-form">
          <div class="form-group">
            <label>{{ $t('auth.name') }}</label>
            <input v-model="form.name" type="text" :placeholder="$t('auth.name')" required />
          </div>

          <div class="form-group">
            <label>{{ $t('auth.email') }}</label>
            <input v-model="form.email" type="email" :placeholder="$t('auth.email')" />
          </div>

          <div class="form-group">
            <label>{{ $t('auth.phone') }} <span class="optional">(for WhatsApp OTP)</span></label>
            <input v-model="form.phone" type="tel" placeholder="e.g. 0812345678" />
          </div>

          <div class="form-group">
            <label>{{ $t('auth.password') }}</label>
            <div class="password-wrap">
              <input v-model="form.password" :type="showPwd ? 'text' : 'password'" :placeholder="$t('auth.password')" required minlength="6" />
              <button type="button" class="pwd-toggle" @click="showPwd = !showPwd">{{ showPwd ? '🙈' : '👁' }}</button>
            </div>
            <span class="field-hint">{{ $t('auth.passwordHint') }}</span>
          </div>

          <div class="form-group">
            <label>{{ $t('auth.confirmPassword') }}</label>
            <input v-model="form.password_confirmation" :type="showPwd ? 'text' : 'password'" :placeholder="$t('auth.confirmPassword')" required />
          </div>

          <!-- Password strength -->
          <div class="password-strength" v-if="form.password">
            <div class="strength-bar">
              <div :class="['strength-fill', strengthClass]" :style="{ width: strengthWidth }"></div>
            </div>
            <span :class="['strength-label', strengthClass]">{{ strengthLabel }}</span>
          </div>

          <div v-if="error" class="error-msg">{{ error }}</div>
          <div v-if="success" class="success-msg">{{ success }}</div>

          <button type="submit" class="submit-btn" :disabled="loading">
            <span v-if="loading" class="spinner"></span>
            {{ loading ? '...' : $t('auth.registerBtn') }}
          </button>
        </form>

        <!-- OTP verification step -->
        <div v-if="showOtp" class="otp-section">
          <div class="separator"><span>Verify WhatsApp</span></div>
          <p class="otp-info">{{ $t('auth.otpSent') }}</p>
          <div class="form-group">
            <label>OTP Code</label>
            <input v-model="otpCode" type="text" maxlength="6" placeholder="Enter 6-digit OTP" class="otp-input" />
          </div>
          <button class="submit-btn whatsapp-btn" @click="handleVerifyOtp" :disabled="loading">
            {{ $t('auth.verifyOtp') }}
          </button>
          <button type="button" class="resend-btn" @click="handleResendOtp" :disabled="loading">
            Resend OTP
          </button>
        </div>

        <p class="switch-link">
          {{ $t('auth.hasAccount') }}
          <RouterLink to="/login">{{ $t('auth.loginBtn') }}</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted, watch } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'

const router      = useRouter()
const route       = useRoute()
const auth        = useAuthStore()
const theme       = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale }  = useI18n()

const form    = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error   = ref('')
const success = ref('')
const showPwd = ref(false)
const showOtp = ref(false)
const otpCode = ref('')

// Draft persistence so an accidental refresh doesn't lose what the user typed.
// Password is intentionally excluded — never park plaintext passwords in browser storage.
const DRAFT_KEY = 'rvm_register_draft'

function clearDraft() {
  sessionStorage.removeItem(DRAFT_KEY)
}

watch(() => [form.value.name, form.value.email, form.value.phone], ([name, email, phone]) => {
  sessionStorage.setItem(DRAFT_KEY, JSON.stringify({ name, email, phone }))
})

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

const passwordStrength = computed(() => {
  const p = form.value.password
  if (!p) return 0
  let score = 0
  if (p.length >= 6)  score++
  if (p.length >= 10) score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  return score
})

const strengthClass = computed(() => {
  const s = passwordStrength.value
  if (s <= 1) return 'weak'
  if (s <= 3) return 'medium'
  return 'strong'
})

const strengthWidth = computed(() => {
  return (passwordStrength.value / 5 * 100) + '%'
})

const strengthLabel = computed(() => {
  const map = { weak: 'Weak', medium: 'Medium', strong: 'Strong' }
  return map[strengthClass.value]
})

async function handleRegister() {
  error.value   = ''
  success.value = ''

  if (!form.value.email && !form.value.phone) {
    error.value = 'Please provide either email or phone number.'
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match.'
    return
  }

  loading.value = true
  try {
    const res = await auth.register({
      name:                  form.value.name,
      email:                 form.value.email || undefined,
      phone:                 form.value.phone || undefined,
      password:              form.value.password,
      password_confirmation: form.value.password_confirmation,
    })

    if (res.success) {
      if (form.value.phone) {
        showOtp.value  = true
        success.value  = 'Account created! Please verify your WhatsApp.'
      } else {
        clearDraft()
        success.value = 'Account created! Redirecting to dashboard...'
        setTimeout(() => {
          router.push({ path: '/welcome', query: { redirect: route.query.redirect || '/dashboard' } })
        }, 1200)
      }
    } else {
      error.value = res.message || 'Registration failed.'
    }
  } catch (e) {
    const errs = e.response?.data?.errors
    if (errs) {
      error.value = Object.values(errs).flat().join(' ')
    } else {
      error.value = e.response?.data?.message || 'Registration failed.'
    }
  } finally {
    loading.value = false
  }
}

async function handleGoogle() {
  try { await auth.loginWithGoogle() } catch {}
}

async function handleVerifyOtp() {
  loading.value = true
  error.value   = ''
  try {
    const res = await auth.verifyOtp(form.value.phone, otpCode.value)
    if (res.success) {
      clearDraft()
      router.push({ path: '/welcome', query: { redirect: route.query.redirect || '/dashboard' } })
    } else {
      error.value = res.message
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid OTP.'
  } finally {
    loading.value = false
  }
}

async function handleResendOtp() {
  loading.value = true
  error.value   = ''
  success.value = ''
  try {
    const res = await auth.sendOtp(form.value.phone)
    if (res.success) {
      success.value = 'A new OTP has been sent to your WhatsApp.'
    } else {
      error.value = res.message || 'Failed to resend OTP.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to resend OTP.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Restore whatever was typed before an accidental refresh (name/email/phone only).
  const draftRaw = sessionStorage.getItem(DRAFT_KEY)
  if (draftRaw) {
    try {
      const draft = JSON.parse(draftRaw)
      form.value.name  = draft.name  || form.value.name
      form.value.email = draft.email || form.value.email
      form.value.phone = draft.phone || form.value.phone
    } catch { /* ignore corrupt draft */ }
  }

  // Resume the "waiting for OTP" state after an accidental refresh — the account
  // (and auth token) already exists at this point, only phone verification is pending.
  if (auth.isLoggedIn && auth.user?.phone && !auth.user?.is_verified) {
    form.value.name  = auth.user.name  || form.value.name
    form.value.email = auth.user.email || form.value.email
    form.value.phone = auth.user.phone
    showOtp.value = true
    success.value = 'Account created! Please verify your WhatsApp.'
  }
})
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  padding: 20px;
  box-sizing: border-box;
  overflow-x: hidden;
}
.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--bg-secondary);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow);
  box-sizing: border-box;
}
.auth-header {
  background: var(--grad-header);
  padding: 24px 20px 20px;
  position: relative;
  text-align: center;
}
.back-btn { position: absolute; left: 16px; top: 16px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
.header-controls { position: absolute; right: 16px; top: 12px; display: flex; gap: 6px; }
.ctrl-btn { background: rgba(255,255,255,0.2); border: none; color: white; padding: 4px 10px; border-radius: 16px; cursor: pointer; font-size: 12px; }
.auth-header h1 { color: white; font-size: 20px; font-weight: 700; margin-bottom: 6px; }
.auth-header p  { color: rgba(255,255,255,0.85); font-size: 14px; }
.auth-body { padding: 24px 20px; box-sizing: border-box; }
.google-btn {
  width: 100%; padding: 12px; background: var(--bg-card);
  border: 1px solid var(--border); border-radius: var(--radius);
  color: var(--text-primary); font-size: 15px; font-weight: 500;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: background 0.2s;
}
.google-btn:hover { background: var(--bg-hover); }
.separator { text-align: center; position: relative; margin: 16px 0; color: var(--text-muted); font-size: 12px; }
.separator::before, .separator::after { content: ''; position: absolute; top: 50%; width: 42%; height: 1px; background: var(--border); }
.separator::before { left: 0; } .separator::after { right: 0; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; font-weight: 500; }
.optional { color: var(--text-muted); font-size: 11px; font-weight: 400; }
.form-group input {
  width: 100%; padding: 11px 14px; background: var(--bg-card);
  border: 1px solid var(--border); border-radius: 8px;
  color: var(--text-primary); font-size: 15px; outline: none; transition: border-color 0.2s;
  box-sizing: border-box;
}
.form-group input:focus { border-color: var(--accent-blue); }
.password-wrap { position: relative; }
.pwd-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; }
.password-strength { margin-bottom: 12px; }
.strength-bar { height: 4px; background: var(--border); border-radius: 2px; margin-bottom: 4px; }
.strength-fill { height: 100%; border-radius: 2px; transition: width 0.3s, background 0.3s; }
.strength-fill.weak   { background: var(--accent-red); }
.strength-fill.medium { background: var(--accent-yellow); }
.strength-fill.strong { background: var(--accent-green); }
.strength-label { font-size: 11px; }
.strength-label.weak   { color: var(--accent-red); }
.strength-label.medium { color: var(--accent-yellow); }
.strength-label.strong { color: var(--accent-green); }
.field-hint { display: block; font-size: 11px; color: var(--text-muted); margin-top: 4px; }
.error-msg   { background: rgba(239,68,68,0.1); color: var(--accent-red); padding: 8px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 12px; border: 1px solid rgba(239,68,68,0.2); }
.success-msg { background: rgba(34,197,94,0.1); color: var(--accent-green); padding: 8px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 12px; border: 1px solid rgba(34,197,94,0.2); }
.submit-btn {
  width: 100%; padding: 13px; background: var(--accent-blue); color: white;
  border: none; border-radius: 8px; font-size: 15px; font-weight: 600;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: opacity 0.2s;
}
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.whatsapp-btn { background: #25D366; margin-top: 8px; }
.resend-btn {
  width: 100%; padding: 10px; margin-top: 8px;
  background: transparent; border: none;
  color: var(--accent-blue); font-size: 13px; font-weight: 500;
  cursor: pointer; text-decoration: underline;
}
.resend-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.otp-section { margin-top: 16px; }
.otp-info { color: var(--accent-green); font-size: 13px; margin-bottom: 12px; text-align: center; }
.otp-input { text-align: center !important; font-size: 24px !important; letter-spacing: 8px; font-weight: 700; }
.switch-link { text-align: center; margin-top: 16px; font-size: 14px; color: var(--text-secondary); }
.switch-link a { color: var(--accent-blue); text-decoration: none; font-weight: 500; }
.spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Responsive ── */
@media (max-width: 480px) {
  .auth-page { padding: 12px; align-items: flex-start; padding-top: 20px; }
  .auth-card { border-radius: 12px; }
  .auth-header { padding: 20px 16px 14px; }
  .auth-header h1 { font-size: 20px; }
  .auth-body { padding: 16px; }
  .otp-input { font-size: 18px !important; letter-spacing: 4px; }
}

@media (max-width: 360px) {
  .auth-page { padding: 8px; }
  .auth-header h1 { font-size: 18px; }
}
</style>
