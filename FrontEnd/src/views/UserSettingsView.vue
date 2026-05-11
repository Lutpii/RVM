<template>
  <div class="settings-page">

    <!-- Top nav -->
    <div class="top-nav">
      <div class="nav-left">
        <button class="back-btn" @click="router.push('/dashboard')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
          </svg>
        </button>
        <span class="nav-title">{{ $t('settings.title') }}</span>
      </div>
    </div>

    <!-- Profile hero -->
    <div class="profile-hero">
      <div class="profile-bg"></div>
      <div class="avatar-large">{{ auth.user?.name?.charAt(0)?.toUpperCase() }}</div>
      <div class="profile-meta">
        <strong>{{ auth.user?.name }}</strong>
        <span class="role-badge">{{ auth.user?.role === 'admin' ? '⚙️ Admin' : '♻️ Recycler' }}</span>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button v-for="tab in tabs" :key="tab.key"
        :class="['tab-btn', activeTab === tab.key ? 'tab-active' : '']"
        @click="activeTab = tab.key">
        <span class="tab-icon">{{ tab.icon }}</span>
        <span class="tab-label">{{ $t(tab.label) }}</span>
      </button>
    </div>

    <!-- ── PROFILE TAB ── -->
    <div v-if="activeTab === 'profile'" class="tab-content">

      <!-- Profile Info Card -->
      <div class="form-card">
        <div class="card-header">
          <h3 class="form-title">{{ $t('settings.profileInfo') }}</h3>
          <button v-if="!editingProfile" class="edit-btn" @click="startEditProfile">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            {{ $t('settings.edit') }}
          </button>
          <div v-else class="edit-actions">
            <button class="cancel-btn" @click="cancelEditProfile">{{ $t('settings.cancel') }}</button>
            <button class="save-btn-sm" @click="saveProfile" :disabled="savingProfile">
              {{ savingProfile ? '...' : $t('settings.save') }}
            </button>
          </div>
        </div>

        <!-- View mode -->
        <div v-if="!editingProfile" class="info-rows">
          <div class="info-row">
            <span class="info-label">{{ $t('auth.name') }}</span>
            <span class="info-value">{{ auth.user?.name || '—' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">{{ $t('auth.email') }}</span>
            <span class="info-value">{{ auth.user?.email || '—' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">{{ $t('auth.phone') }}</span>
            <span class="info-value">{{ auth.user?.phone || '—' }}</span>
          </div>
        </div>

        <!-- Edit mode -->
        <div v-else class="edit-form">
          <div class="form-group">
            <label class="form-label">{{ $t('auth.name') }}</label>
            <input v-model="profileForm.name" type="text" class="form-input" :placeholder="$t('auth.name')" />
          </div>
          <div class="form-group">
            <label class="form-label">{{ $t('auth.email') }}</label>
            <input v-model="profileForm.email" type="email" class="form-input" :placeholder="$t('auth.email')" />
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">{{ $t('auth.phone') }}</label>
            <input v-model="profileForm.phone" type="tel" class="form-input" :placeholder="$t('auth.phone')" />
          </div>
          <div v-if="profileMsg" :class="['msg', profileSuccess ? 'msg-ok' : 'msg-err']" style="margin-top:12px;margin-bottom:0">{{ profileMsg }}</div>
        </div>
      </div>

      <!-- Password Card -->
      <div class="form-card">
        <div class="card-header">
          <h3 class="form-title">{{ $t('settings.changePassword') }}</h3>
          <button v-if="!editingPassword" class="edit-btn" @click="editingPassword = true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            {{ $t('settings.change') }}
          </button>
          <button v-else class="cancel-btn" @click="cancelEditPassword">{{ $t('settings.cancel') }}</button>
        </div>

        <!-- Placeholder when collapsed -->
        <div v-if="!editingPassword" class="info-rows">
          <div class="info-row">
            <span class="info-label">{{ $t('settings.password') }}</span>
            <span class="info-value muted">••••••••</span>
          </div>
        </div>

        <!-- Password form -->
        <div v-else class="edit-form">
          <div class="form-group">
            <label class="form-label">{{ $t('settings.currentPassword') }}</label>
            <input v-model="pwForm.current" type="password" class="form-input" autocomplete="current-password" />
          </div>
          <div class="form-group">
            <label class="form-label">{{ $t('settings.newPassword') }}</label>
            <input v-model="pwForm.newPw" type="password" class="form-input" autocomplete="new-password" />
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">{{ $t('settings.confirmNewPassword') }}</label>
            <input v-model="pwForm.confirm" type="password" class="form-input" autocomplete="new-password" />
          </div>
          <div v-if="pwMsg" :class="['msg', pwSuccess ? 'msg-ok' : 'msg-err']" style="margin-top:12px;margin-bottom:0">{{ pwMsg }}</div>
          <button class="save-btn full-btn" @click="changePassword" :disabled="savingPw" style="margin-top:14px">
            {{ savingPw ? $t('settings.saving') : $t('settings.updatePassword') }}
          </button>
        </div>
      </div>

    </div>

    <!-- ── REWARDS TAB ── -->
    <div v-if="activeTab === 'rewards'" class="tab-content">

      <div class="points-card">
        <div class="points-bg"></div>
        <p class="points-label">{{ $t('settings.yourPoints') }}</p>
        <div class="points-number">{{ auth.user?.total_points || 0 }}</div>
        <p class="points-sub">≈ RM {{ pointsToMoney(auth.user?.total_points || 0) }}</p>
      </div>

      <div class="rate-row">
        <div class="rate-card">
          <span class="rate-icon">💱</span>
          <div class="rate-info">
            <strong>{{ $t('settings.conversionRate') }}</strong>
            <span>{{ conversionRate.points }} pts = RM {{ conversionRate.rm.toFixed(2) }}</span>
          </div>
        </div>
        <div class="rate-card">
          <span class="rate-icon">💰</span>
          <div class="rate-info">
            <strong>{{ $t('settings.minRedeem') }}</strong>
            <span>{{ minRedeem }} pts min</span>
          </div>
        </div>
      </div>

      <div class="form-card">
        <h3 class="form-title">{{ $t('settings.redeemTitle') }}</h3>
        <p class="form-hint">{{ $t('settings.redeemHint') }}</p>
        <div class="form-group">
          <label class="form-label">{{ $t('settings.pointsToRedeem') }}</label>
          <input v-model.number="redeemPoints" type="number" class="form-input"
            :min="minRedeem" :max="auth.user?.total_points || 0" :step="conversionRate.points" />
          <span class="input-hint">{{ $t('settings.available') }}: {{ auth.user?.total_points || 0 }} pts</span>
        </div>
        <div class="redeem-preview" v-if="redeemPoints >= minRedeem && redeemPoints <= (auth.user?.total_points || 0)">
          <div class="preview-col">
            <span class="preview-num">{{ redeemPoints }}</span>
            <span class="preview-lbl">{{ $t('settings.pts') }}</span>
          </div>
          <span class="preview-arrow">→</span>
          <div class="preview-col">
            <span class="preview-num green">RM {{ pointsToMoney(redeemPoints) }}</span>
            <span class="preview-lbl">{{ $t('settings.cash') }}</span>
          </div>
        </div>
        <div v-if="redeemMsg" :class="['msg', redeemSuccess ? 'msg-ok' : 'msg-err']">{{ redeemMsg }}</div>
        <button class="save-btn full-btn redeem-btn"
          @click="redeemNow"
          :disabled="redeemPoints < minRedeem || redeeming || redeemPoints > (auth.user?.total_points || 0)">
          {{ redeeming ? $t('settings.processing') : $t('settings.redeemBtn') }}
        </button>
      </div>

      <div class="section-pad">
        <h3 class="section-title">{{ $t('settings.redemptionHistory') }}</h3>
        <div v-if="loadingHistory" class="loading-placeholder"><div class="spinner-sm"></div></div>
        <div v-else-if="redemptionHistory.length === 0" class="empty-state">
          <p>{{ $t('settings.noRedemptions') }}</p>
        </div>
        <div v-else class="activity-list">
          <div v-for="r in redemptionHistory" :key="r.id" class="activity-item">
            <div class="activity-dot dot-blue"></div>
            <div class="activity-info">
              <span class="activity-desc">{{ r.points_used }} pts → RM {{ Number(r.amount).toFixed(2) }}</span>
              <span class="activity-time">
                {{ formatTime(r.created_at) }}
                <span :class="['status-badge', `badge-${r.status}`]">{{ r.status }}</span>
              </span>
            </div>
            <span class="activity-pts pts-green">RM {{ Number(r.amount).toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── PREFERENCES TAB ── -->
    <div v-if="activeTab === 'preferences'" class="tab-content">

      <div class="form-card">
        <h3 class="form-title">{{ $t('settings.language') }}</h3>
        <p class="form-hint">{{ $t('settings.languageHint') }}</p>
        <div class="toggle-group">
          <button :class="['toggle-opt', locale === 'en' ? 'toggle-active' : '']" @click="setLang('en')">
            🇺🇸 English
          </button>
          <button :class="['toggle-opt', locale === 'my' ? 'toggle-active' : '']" @click="setLang('my')">
            🇲🇾 Bahasa Melayu
          </button>
        </div>
      </div>

      <div class="form-card">
        <h3 class="form-title">{{ $t('settings.theme') }}</h3>
        <p class="form-hint">{{ $t('settings.themeHint') }}</p>
        <div class="toggle-group">
          <button :class="['toggle-opt', theme === 'light' ? 'toggle-active' : '']" @click="setTheme('light')">
            ☀️ {{ $t('settings.themeLight') }}
          </button>
          <button :class="['toggle-opt', theme === 'dark' ? 'toggle-active' : '']" @click="setTheme('dark')">
            🌙 {{ $t('settings.themeDark') }}
          </button>
        </div>
      </div>

    </div>

    <!-- ── ACCOUNT TAB ── -->
    <div v-if="activeTab === 'account'" class="tab-content">

      <!-- Account info -->
      <div class="form-card">
        <h3 class="form-title">{{ $t('settings.accountInfo') }}</h3>
        <div class="info-rows">
          <div class="info-row">
            <span class="info-label">{{ $t('settings.accountId') }}</span>
            <span class="info-value mono">#{{ auth.user?.id }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">{{ $t('settings.accountRole') }}</span>
            <span class="info-value">{{ auth.user?.role === 'admin' ? 'Admin' : 'User' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">{{ $t('settings.memberSince') }}</span>
            <span class="info-value">{{ formatDate(auth.user?.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Logout -->
      <div class="form-card">
        <h3 class="form-title">{{ $t('settings.session') }}</h3>
        <p class="form-hint">{{ $t('settings.logoutHint') }}</p>
        <button class="full-btn logout-btn-full" @click="handleLogout">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          {{ $t('nav.logout') }}
        </button>
      </div>

      <!-- Danger zone -->
      <div class="form-card danger-card">
        <h3 class="form-title danger-title">{{ $t('settings.dangerZone') }}</h3>
        <p class="form-hint">{{ $t('settings.deleteHint') }}</p>

        <div v-if="!confirmDelete">
          <button class="full-btn delete-btn" @click="confirmDelete = true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6M14 11v6"/>
              <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
            {{ $t('settings.deleteAccount') }}
          </button>
        </div>

        <div v-else class="confirm-delete-box">
          <p class="confirm-msg">{{ $t('settings.deleteConfirm') }}</p>
          <div class="confirm-btns">
            <button class="cancel-btn-lg" @click="confirmDelete = false">{{ $t('settings.cancel') }}</button>
            <button class="delete-btn-confirm" @click="deleteAccount" :disabled="deletingAccount">
              {{ deletingAccount ? $t('settings.deleting') : $t('settings.confirmYes') }}
            </button>
          </div>
        </div>
      </div>

    </div>

    <div style="height: 40px"></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const router      = useRouter()
const auth        = useAuthStore()
const theme       = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale, t } = useI18n()

const activeTab = ref('profile')

const tabs = [
  { key: 'profile',     icon: '👤', label: 'settings.tabProfile'     },
  { key: 'rewards',     icon: '💎', label: 'settings.tabRewards'     },
  { key: 'preferences', icon: '🎨', label: 'settings.tabPreferences' },
  { key: 'account',     icon: '🔐', label: 'settings.tabAccount'     },
]

// ── Profile ───────────────────────────────────────────────────────────────────
const editingProfile = ref(false)
const profileForm    = reactive({ name: '', email: '', phone: '' })
const savingProfile  = ref(false)
const profileMsg     = ref('')
const profileSuccess = ref(false)

function startEditProfile() {
  profileForm.name  = auth.user?.name  || ''
  profileForm.email = auth.user?.email || ''
  profileForm.phone = auth.user?.phone || ''
  profileMsg.value  = ''
  editingProfile.value = true
}

function cancelEditProfile() {
  editingProfile.value = false
  profileMsg.value     = ''
}

async function saveProfile() {
  savingProfile.value = true
  profileMsg.value    = ''
  try {
    const res = await api.put('/user/profile', {
      name:  profileForm.name,
      email: profileForm.email,
      phone: profileForm.phone,
    })
    if (res.data.success) {
      const updated = { ...auth.user, ...res.data.user }
      auth.user = updated
      localStorage.setItem('rvm_user', JSON.stringify(updated))
      profileSuccess.value = true
      profileMsg.value     = t('settings.savedOk')
      setTimeout(() => { editingProfile.value = false; profileMsg.value = '' }, 1200)
    } else {
      profileSuccess.value = false
      profileMsg.value = res.data.message || t('settings.savedFail')
    }
  } catch (e) {
    profileSuccess.value = false
    profileMsg.value = e.response?.data?.message || t('settings.savedFail')
  } finally {
    savingProfile.value = false
  }
}

// ── Password ──────────────────────────────────────────────────────────────────
const editingPassword = ref(false)
const pwForm    = reactive({ current: '', newPw: '', confirm: '' })
const savingPw  = ref(false)
const pwMsg     = ref('')
const pwSuccess = ref(false)

function cancelEditPassword() {
  editingPassword.value = false
  pwForm.current = ''
  pwForm.newPw   = ''
  pwForm.confirm = ''
  pwMsg.value    = ''
}

async function changePassword() {
  pwMsg.value = ''
  if (pwForm.newPw !== pwForm.confirm) {
    pwSuccess.value = false
    pwMsg.value = t('settings.pwMismatch')
    return
  }
  if (pwForm.newPw.length < 6) {
    pwSuccess.value = false
    pwMsg.value = t('settings.pwTooShort')
    return
  }
  savingPw.value = true
  try {
    const res = await api.put('/user/password', {
      current_password:          pwForm.current,
      new_password:              pwForm.newPw,
      new_password_confirmation: pwForm.confirm,
    })
    if (res.data.success) {
      pwSuccess.value = true
      pwMsg.value = t('settings.pwUpdated')
      setTimeout(() => cancelEditPassword(), 1500)
    } else {
      pwSuccess.value = false
      pwMsg.value = res.data.message || t('settings.savedFail')
    }
  } catch (e) {
    pwSuccess.value = false
    pwMsg.value = e.response?.data?.message || t('settings.pwWrong')
  } finally {
    savingPw.value = false
  }
}

// ── Rewards ───────────────────────────────────────────────────────────────────
const conversionRate    = ref({ points: 100, rm: 0.10 })
const minRedeem         = 500
const redeemPoints      = ref(500)
const redeeming         = ref(false)
const redeemMsg         = ref('')
const redeemSuccess     = ref(false)
const redemptionHistory = ref([])
const loadingHistory    = ref(true)

function pointsToMoney(pts) {
  const rate = conversionRate.value
  return ((pts / rate.points) * rate.rm).toFixed(2)
}

async function redeemNow() {
  redeemMsg.value = ''
  if (redeemPoints.value < minRedeem || redeemPoints.value > (auth.user?.total_points || 0)) return
  redeeming.value = true
  try {
    const res = await api.post('/user/redeem', { points: redeemPoints.value })
    if (res.data.success) {
      redeemSuccess.value = true
      redeemMsg.value = `✅ RM ${pointsToMoney(redeemPoints.value)} ${t('settings.redeemOk')}`
      auth.updatePoints((auth.user?.total_points || 0) - redeemPoints.value)
      redeemPoints.value = minRedeem
      await loadRedemptionHistory()
    } else {
      redeemSuccess.value = false
      redeemMsg.value = res.data.message || t('settings.redeemFail')
    }
  } catch (e) {
    redeemSuccess.value = false
    redeemMsg.value = e.response?.data?.message || t('settings.redeemFail')
  } finally {
    redeeming.value = false
  }
}

async function loadRedemptionHistory() {
  try {
    const res = await api.get('/user/redemptions')
    redemptionHistory.value = res.data.redemptions || []
  } catch {
    redemptionHistory.value = []
  } finally {
    loadingHistory.value = false
  }
}

// ── Preferences ───────────────────────────────────────────────────────────────
function setLang(lang) {
  locale.value = lang
  localStorage.setItem('rvm_lang', lang)
}

function setTheme(t) {
  if (theme.value !== t) toggleTheme()
}

// ── Account ───────────────────────────────────────────────────────────────────
const confirmDelete  = ref(false)
const deletingAccount = ref(false)

async function handleLogout() {
  await auth.logout()
  router.push('/')
}

async function deleteAccount() {
  deletingAccount.value = true
  try {
    await api.delete('/user/account')
    await auth.logout()
    router.push('/')
  } catch {
    confirmDelete.value   = false
    deletingAccount.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function formatDate(ts) {
  if (!ts) return '—'
  return new Date(ts).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(async () => {
  // Fetch fresh user data from DB first (populates created_at, role, etc.)
  await auth.fetchMe()

  try {
    const res = await api.get('/user/reward-rate')
    if (res.data?.rate) conversionRate.value = res.data.rate
  } catch { /* use default */ }

  await loadRedemptionHistory()
})
</script>

<style scoped>
.settings-page { min-height: 100vh; background: var(--bg-primary); }

/* ── Top nav ── */
.top-nav {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  position: sticky; top: 0; z-index: 10;
}
.nav-left { display: flex; align-items: center; gap: 10px; }
.back-btn {
  background: var(--bg-card); border: 1px solid var(--border);
  color: var(--text-primary); padding: 7px 10px; border-radius: 8px;
  cursor: pointer; display: flex; align-items: center;
}
.back-btn:hover { background: var(--bg-hover); }
.nav-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }

/* ── Profile hero ── */
.profile-hero {
  background: var(--grad-header);
  padding: 24px 20px 28px;
  display: flex; flex-direction: column; align-items: center; gap: 10px;
  position: relative; overflow: hidden;
}
.profile-bg {
  position: absolute; inset: 0;
  background: radial-gradient(circle at 70% 50%, rgba(255,255,255,0.12) 0%, transparent 60%);
}
.avatar-large {
  width: 72px; height: 72px; border-radius: 50%;
  background: rgba(255,255,255,0.25);
  color: white; display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 28px; border: 3px solid rgba(255,255,255,0.5);
  position: relative; z-index: 1;
}
.profile-meta { text-align: center; position: relative; z-index: 1; }
.profile-meta strong { display: block; color: white; font-size: 18px; font-weight: 700; }
.role-badge { color: rgba(255,255,255,0.85); font-size: 13px; }

/* ── Tabs ── */
.tabs {
  display: flex;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  overflow-x: auto;
}
.tabs::-webkit-scrollbar { display: none; }
.tab-btn {
  flex: 1; min-width: 72px; padding: 10px 4px;
  background: none; border: none;
  color: var(--text-muted); font-size: 11px; font-weight: 600;
  cursor: pointer; border-bottom: 2px solid transparent;
  transition: all 0.2s; display: flex; flex-direction: column; align-items: center; gap: 3px;
}
.tab-btn:hover { color: var(--text-primary); }
.tab-icon { font-size: 18px; }
.tab-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.4px; }
.tab-active { color: var(--accent-blue) !important; border-bottom-color: var(--accent-blue) !important; }

/* ── Tab content ── */
.tab-content { padding: 16px; display: flex; flex-direction: column; gap: 14px; }

/* ── Form card ── */
.form-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
}
.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.form-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin: 0; }
.form-hint { font-size: 12px; color: var(--text-muted); margin-bottom: 14px; }
.form-group { margin-bottom: 12px; }
.form-label { display: block; font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
.form-input {
  width: 100%; box-sizing: border-box;
  padding: 10px 12px; border-radius: 8px;
  background: var(--bg-primary); border: 1px solid var(--border);
  color: var(--text-primary); font-size: 14px;
  transition: border-color 0.2s;
}
.form-input:focus { outline: none; border-color: var(--accent-blue); }
.input-hint { display: block; font-size: 11px; color: var(--text-muted); margin-top: 4px; }

/* ── View mode rows ── */
.info-rows { display: flex; flex-direction: column; gap: 0; }
.info-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 0; border-bottom: 1px solid var(--border);
}
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 12px; color: var(--text-muted); font-weight: 500; }
.info-value { font-size: 14px; color: var(--text-primary); font-weight: 500; text-align: right; }
.info-value.muted { color: var(--text-muted); letter-spacing: 2px; }
.info-value.mono { font-family: monospace; font-size: 13px; }

/* ── Edit / action buttons ── */
.edit-btn {
  display: flex; align-items: center; gap: 5px;
  background: var(--bg-hover); border: 1px solid var(--border);
  color: var(--text-secondary); padding: 5px 12px; border-radius: 20px;
  cursor: pointer; font-size: 12px; font-weight: 600;
  transition: all 0.2s;
}
.edit-btn:hover { border-color: var(--accent-blue); color: var(--accent-blue); }
.edit-actions { display: flex; gap: 8px; }
.cancel-btn {
  background: none; border: 1px solid var(--border);
  color: var(--text-muted); padding: 5px 12px; border-radius: 20px;
  cursor: pointer; font-size: 12px; font-weight: 600;
}
.cancel-btn:hover { background: var(--bg-hover); }
.save-btn-sm {
  background: var(--accent-blue); border: none; color: white;
  padding: 5px 14px; border-radius: 20px;
  cursor: pointer; font-size: 12px; font-weight: 700;
  transition: opacity 0.2s;
}
.save-btn-sm:disabled { opacity: 0.5; cursor: not-allowed; }
.save-btn-sm:not(:disabled):hover { opacity: 0.88; }

/* ── Full-width buttons ── */
.full-btn {
  width: 100%; padding: 12px; border: none; border-radius: 8px;
  font-size: 14px; font-weight: 700; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: opacity 0.2s;
}
.full-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.full-btn:not(:disabled):hover { opacity: 0.88; }
.save-btn { background: var(--accent-blue); color: white; }
.redeem-btn { background: var(--accent-green); color: white; }
.logout-btn-full { background: var(--bg-hover); color: var(--text-primary); border: 1px solid var(--border); }
.logout-btn-full:hover { background: var(--accent-red) !important; color: white; border-color: var(--accent-red); opacity: 1 !important; }
.delete-btn { background: rgba(239,68,68,0.1); color: var(--accent-red); border: 1px solid rgba(239,68,68,0.3); }
.delete-btn:hover { background: var(--accent-red) !important; color: white; opacity: 1 !important; }

/* ── Messages ── */
.msg { padding: 10px 12px; border-radius: 8px; font-size: 13px; }
.msg-ok  { background: rgba(34,197,94,0.15); color: var(--accent-green); border: 1px solid rgba(34,197,94,0.3); }
.msg-err { background: rgba(239,68,68,0.15); color: var(--accent-red); border: 1px solid rgba(239,68,68,0.3); }

/* ── Points card ── */
.points-card {
  background: var(--grad-header); border-radius: var(--radius);
  padding: 24px 20px; text-align: center;
  position: relative; overflow: hidden;
}
.points-bg { position: absolute; inset: 0; background: radial-gradient(circle at 70% 50%, rgba(255,255,255,0.1) 0%, transparent 60%); }
.points-label { color: rgba(255,255,255,0.8); font-size: 13px; margin-bottom: 6px; position: relative; z-index: 1; }
.points-number { color: white; font-size: 52px; font-weight: 800; line-height: 1; letter-spacing: -2px; position: relative; z-index: 1; }
.points-sub { color: rgba(255,255,255,0.7); font-size: 14px; margin-top: 6px; position: relative; z-index: 1; }

/* ── Rate row ── */
.rate-row { display: flex; gap: 10px; }
.rate-card {
  flex: 1; display: flex; align-items: center; gap: 10px;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 12px;
}
.rate-icon { font-size: 20px; }
.rate-info { display: flex; flex-direction: column; }
.rate-info strong { font-size: 11px; color: var(--text-secondary); font-weight: 600; }
.rate-info span { font-size: 13px; color: var(--text-primary); font-weight: 700; margin-top: 2px; }

/* ── Redeem preview ── */
.redeem-preview {
  display: flex; align-items: center; justify-content: center; gap: 16px;
  background: var(--bg-hover); border-radius: 10px;
  padding: 16px; margin-bottom: 14px;
}
.preview-col { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.preview-num { font-size: 22px; font-weight: 800; color: var(--text-primary); }
.preview-num.green { color: var(--accent-green); }
.preview-lbl { font-size: 11px; color: var(--text-muted); text-transform: uppercase; }
.preview-arrow { font-size: 20px; color: var(--text-muted); }

/* ── Preferences toggles ── */
.toggle-group { display: flex; gap: 10px; }
.toggle-opt {
  flex: 1; padding: 12px 8px; border-radius: 10px;
  background: var(--bg-primary); border: 2px solid var(--border);
  color: var(--text-secondary); font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; text-align: center;
}
.toggle-opt:hover { border-color: var(--accent-blue); color: var(--text-primary); }
.toggle-active { background: var(--accent-blue) !important; border-color: var(--accent-blue) !important; color: white !important; }

/* ── Danger zone ── */
.danger-card { border-color: rgba(239,68,68,0.3); }
.danger-title { color: var(--accent-red); }
.confirm-delete-box { background: rgba(239,68,68,0.08); border-radius: 10px; padding: 14px; }
.confirm-msg { font-size: 13px; color: var(--text-primary); margin-bottom: 14px; font-weight: 500; }
.confirm-btns { display: flex; gap: 10px; }
.cancel-btn-lg {
  flex: 1; padding: 10px; border-radius: 8px;
  background: var(--bg-hover); border: 1px solid var(--border);
  color: var(--text-primary); font-size: 13px; font-weight: 600; cursor: pointer;
}
.delete-btn-confirm {
  flex: 1; padding: 10px; border-radius: 8px;
  background: var(--accent-red); border: none;
  color: white; font-size: 13px; font-weight: 700; cursor: pointer;
}
.delete-btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Activity ── */
.section-pad { padding: 4px 0; }
.section-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; }
.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.empty-state { text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px; background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border); }
.activity-list { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.activity-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--border); }
.activity-item:last-child { border-bottom: none; }
.activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-blue { background: var(--accent-blue); }
.activity-info { flex: 1; display: flex; flex-direction: column; }
.activity-desc { font-size: 13px; color: var(--text-primary); }
.activity-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; gap: 6px; }
.activity-pts  { font-size: 14px; font-weight: 700; }
.pts-green { color: var(--accent-green); }
.status-badge { padding: 1px 7px; border-radius: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.badge-pending   { background: rgba(234,179,8,0.2);  color: var(--accent-yellow); }
.badge-approved  { background: rgba(34,197,94,0.2);  color: var(--accent-green);  }
.badge-completed { background: rgba(34,197,94,0.2);  color: var(--accent-green);  }
.badge-rejected  { background: rgba(239,68,68,0.2);  color: var(--accent-red);    }

.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Responsive ── */
@media (max-width: 480px) {
  .top-nav { padding: 10px 12px; }
  .profile-hero { padding: 18px 16px 22px; }
  .avatar-large { width: 60px; height: 60px; font-size: 22px; }
  .profile-meta strong { font-size: 16px; }
  .tab-content { padding: 12px; gap: 12px; }
  .form-card { padding: 14px; }
  .points-number { font-size: 40px; }
  .rate-row { flex-direction: column; }
  .preview-num { font-size: 18px; }
  .toggle-group { flex-direction: column; }
}
</style>
