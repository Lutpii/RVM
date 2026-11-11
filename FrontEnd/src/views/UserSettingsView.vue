<template>
  <div class="acct-page">

    <!-- ══ MAIN: one scroll page (layout after the DANA "Me" page, RVM look) ══ -->
    <template v-if="view === 'main'">
      <header class="acct-hero">
        <div class="hero-row">
          <button type="button" class="hero-avatar" :aria-label="$t('settings.changePicture')" @click="openAvatarSheet">
            <UserAvatar :user="auth.user" :size="80" />
            <span class="hero-avatar-edit"><PhPencilSimple :size="13" weight="bold" aria-hidden="true" /></span>
            <span class="hero-role">{{ auth.isAdmin ? $t('settings.admin') : $t('settings.recycler') }}</span>
          </button>
          <div class="hero-info">
            <button type="button" class="hero-name" @click="openView('info')">
              <span>{{ auth.user?.name }}</span>
              <PhCaretRight :size="16" weight="bold" aria-hidden="true" />
            </button>
            <span class="hero-email">{{ maskEmail(auth.user?.email) }}</span>
            <!-- Admins don't recycle, so Scan / Points history aren't theirs. -->
            <div v-if="!auth.isAdmin" class="hero-pills">
              <RouterLink to="/scan" class="hero-pill"><PhScan :size="15" weight="bold" aria-hidden="true" /> {{ $t('settings.scanShort') }}</RouterLink>
              <RouterLink to="/activity" class="hero-pill"><PhClockCounterClockwise :size="15" weight="bold" aria-hidden="true" /> {{ $t('settings.pointsHistory') }}</RouterLink>
            </div>
          </div>
        </div>
      </header>

      <!-- Admin: the summary card (all zeros for them) becomes a shortcut to the panel. -->
      <section v-if="auth.isAdmin" class="acct-summary admin-card" :aria-label="$t('nav.admin')">
        <span class="admin-card-icon"><PhGear :size="26" aria-hidden="true" /></span>
        <div class="admin-card-text">
          <strong>{{ $t('nav.admin') }}</strong>
          <span>{{ $t('settings.adminPanelHint') }}</span>
        </div>
        <RouterLink to="/admin" class="admin-card-btn">
          {{ $t('settings.openAdminPanel') }} <PhCaretRight :size="16" weight="bold" aria-hidden="true" />
        </RouterLink>
      </section>

      <section v-else class="acct-summary" :aria-label="$t('settings.yourPoints')">
        <div class="sum-tiles">
          <div class="sum-tile">
            <span class="sum-icon sum-icon-green"><PhCoins :size="22" aria-hidden="true" /></span>
            <span class="sum-value">{{ num(summary.total_points) }}</span>
            <span class="sum-label">{{ $t('settings.points') }}</span>
            <span class="sum-sub sum-sub-green">≈ RM {{ pointsToMoney(summary.total_points) }}</span>
          </div>
          <div class="sum-tile">
            <span class="sum-icon sum-icon-blue"><PhRecycle :size="22" aria-hidden="true" /></span>
            <span class="sum-value">{{ num(summary.items_recycled) }}</span>
            <span class="sum-label">{{ $t('settings.itemsRecycled') }}</span>
          </div>
          <div class="sum-tile">
            <span class="sum-icon sum-icon-teal"><PhLeaf :size="22" aria-hidden="true" /></span>
            <span class="sum-value">{{ carbonText }}</span>
            <span class="sum-label">{{ $t('settings.carbonSaved') }}</span>
          </div>
        </div>
        <div class="sum-month">
          <span class="sum-month-title">{{ $t('settings.thisMonth') }}</span>
          <div class="sum-month-row">
            <div class="sum-flow">
              <span class="flow-icon flow-in"><PhArrowDown :size="16" weight="bold" aria-hidden="true" /></span>
              <span class="flow-text">
                <span class="flow-label">{{ $t('settings.earned') }}</span>
                <span class="flow-value">{{ signed(summary.earned_this_month, '+') }} pts</span>
              </span>
            </div>
            <div class="sum-flow sum-flow-right">
              <span class="flow-icon flow-out"><PhArrowUp :size="16" weight="bold" aria-hidden="true" /></span>
              <span class="flow-text">
                <span class="flow-label">{{ $t('settings.redeemed') }}</span>
                <span class="flow-value">{{ signed(summary.redeemed_this_month, '−') }} pts</span>
              </span>
            </div>
          </div>
        </div>
      </section>

      <nav v-if="!auth.isAdmin" class="menu-group" :aria-label="$t('settings.sectionRewards')">
        <h2 class="menu-title">{{ $t('settings.sectionRewards') }}</h2>
        <div class="menu-card">
          <RouterLink to="/rewards" class="menu-row">
            <PhTicket class="menu-icon icon-purple" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.myVouchers') }}</span>
            <span v-if="summary.active_vouchers > 0" class="menu-badge">{{ $t('settings.activeVouchers', { n: summary.active_vouchers }) }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </RouterLink>
          <button type="button" class="menu-row" @click="openView('redeem')">
            <PhWallet class="menu-icon icon-green" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.redeemEwallet') }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </button>
          <button type="button" class="menu-row" @click="openView('history')">
            <PhReceipt class="menu-icon icon-orange" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.redemptionHistory') }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </button>
        </div>
      </nav>

      <nav class="menu-group" :aria-label="$t('settings.sectionPreferences')">
        <h2 class="menu-title">{{ $t('settings.sectionPreferences') }}</h2>
        <div class="menu-card">
          <button type="button" class="menu-row" @click="openView('language')">
            <PhTranslate class="menu-icon icon-blue" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.language') }}</span>
            <span class="menu-value">{{ locale === 'my' ? 'Bahasa Melayu' : 'English' }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </button>
          <button type="button" class="menu-row" role="switch" :aria-checked="theme === 'dark'" @click="toggleTheme()">
            <PhMoonStars class="menu-icon icon-purple" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.darkMode') }}</span>
            <span :class="['switch', { on: theme === 'dark' }]" aria-hidden="true"><span class="switch-knob"></span></span>
          </button>
        </div>
      </nav>

      <nav class="menu-group" :aria-label="$t('settings.sectionAccount')">
        <h2 class="menu-title">{{ $t('settings.sectionAccount') }}</h2>
        <div class="menu-card">
          <button type="button" class="menu-row" @click="openView('profile')">
            <PhUserCircle class="menu-icon icon-blue" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.editProfile') }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </button>
          <button type="button" class="menu-row" @click="openView('info')">
            <PhInfo class="menu-icon icon-teal" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.accountInfo') }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </button>
          <RouterLink to="/privacy" class="menu-row">
            <PhShieldCheck class="menu-icon icon-muted" :size="22" aria-hidden="true" />
            <span class="menu-label">{{ $t('settings.privacyPolicy') }}</span>
            <PhCaretRight class="menu-chevron" :size="18" aria-hidden="true" />
          </RouterLink>
        </div>
      </nav>

      <div class="acct-footer">
        <button type="button" class="logout-btn" @click="handleLogout">
          <PhSignOut :size="19" aria-hidden="true" /> {{ $t('nav.logout') }}
        </button>
        <span class="app-version">{{ $t('settings.appVersion', { version: appVersion }) }}</span>
      </div>
    </template>

    <!-- ══ SUB-PAGES: opened from the menu, browser Back returns here ══ -->
    <template v-else>
      <div class="sub-bar">
        <button type="button" class="sub-back" :aria-label="$t('settings.back')" @click="goBack">
          <PhCaretLeft :size="22" weight="bold" aria-hidden="true" />
        </button>
        <h1 class="sub-title">{{ subTitle }}</h1>
      </div>

      <div class="sub-content">
        <!-- Edit profile & password -->
        <template v-if="view === 'profile'">
          <div class="form-card">
            <div class="card-header">
              <h3 class="form-title">{{ $t('settings.profileInfo') }}</h3>
              <button v-if="!editingProfile" class="edit-btn" @click="startEditProfile">
                <PhPencilSimple :size="14" aria-hidden="true" /> {{ $t('settings.edit') }}
              </button>
              <div v-else class="edit-actions">
                <button class="cancel-btn" @click="cancelEditProfile">{{ $t('settings.cancel') }}</button>
                <button class="save-btn-sm" @click="saveProfile" :disabled="savingProfile">
                  {{ savingProfile ? '...' : $t('settings.save') }}
                </button>
              </div>
            </div>
            <div v-if="!editingProfile" class="info-rows">
              <div class="info-row">
                <span class="info-label">{{ $t('auth.name') }}</span>
                <span class="info-value">{{ auth.user?.name || '—' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">{{ $t('auth.email') }}</span>
                <span class="info-value">{{ auth.user?.email || '—' }}</span>
              </div>
            </div>
            <div v-else class="edit-form">
              <div class="form-group">
                <label class="form-label" for="pf-name">{{ $t('auth.name') }}</label>
                <input id="pf-name" v-model="profileForm.name" type="text" class="form-input" :placeholder="$t('auth.name')" />
              </div>
              <div class="form-group">
                <label class="form-label" for="pf-email">{{ $t('auth.email') }}</label>
                <input id="pf-email" v-model="profileForm.email" type="email" class="form-input" :placeholder="$t('auth.email')" />
              </div>
              <div v-if="profileMsg" :class="['msg', profileSuccess ? 'msg-ok' : 'msg-err']">{{ profileMsg }}</div>
            </div>
          </div>

          <!-- Hidden for Google-only accounts: they never set a current password. -->
          <div v-if="auth.user?.has_password" class="form-card">
            <div class="card-header">
              <h3 class="form-title">{{ $t('settings.changePassword') }}</h3>
              <button v-if="!editingPassword" class="edit-btn" @click="editingPassword = true">
                <PhLock :size="14" aria-hidden="true" /> {{ $t('settings.change') }}
              </button>
              <button v-else class="cancel-btn" @click="cancelEditPassword">{{ $t('settings.cancel') }}</button>
            </div>
            <div v-if="!editingPassword" class="info-rows">
              <div class="info-row">
                <span class="info-label">{{ $t('settings.password') }}</span>
                <span class="info-value muted">••••••••</span>
              </div>
            </div>
            <div v-else class="edit-form">
              <div class="form-group">
                <label class="form-label" for="pw-cur">{{ $t('settings.currentPassword') }}</label>
                <input id="pw-cur" v-model="pwForm.current" type="password" class="form-input" autocomplete="current-password" />
              </div>
              <div class="form-group">
                <label class="form-label" for="pw-new">{{ $t('settings.newPassword') }}</label>
                <input id="pw-new" v-model="pwForm.newPw" type="password" class="form-input" autocomplete="new-password" />
              </div>
              <div class="form-group">
                <label class="form-label" for="pw-conf">{{ $t('settings.confirmNewPassword') }}</label>
                <input id="pw-conf" v-model="pwForm.confirm" type="password" class="form-input" autocomplete="new-password" />
              </div>
              <div v-if="pwMsg" :class="['msg', pwSuccess ? 'msg-ok' : 'msg-err']">{{ pwMsg }}</div>
              <button class="save-btn full-btn" @click="changePassword" :disabled="savingPw">
                {{ savingPw ? $t('settings.saving') : $t('settings.updatePassword') }}
              </button>
            </div>
          </div>
        </template>

        <!-- Redeem to e-wallet -->
        <template v-else-if="view === 'redeem'">
          <div class="rate-row">
            <div class="rate-card">
              <PhArrowsLeftRight class="rate-icon" aria-hidden="true" />
              <div class="rate-info">
                <strong>{{ $t('settings.conversionRate') }}</strong>
                <span>{{ conversionRate.points }} pts = RM {{ conversionRate.rm.toFixed(2) }}</span>
              </div>
            </div>
            <div class="rate-card">
              <PhCoins class="rate-icon" aria-hidden="true" />
              <div class="rate-info">
                <strong>{{ $t('settings.available') }}</strong>
                <span>{{ num(auth.user?.total_points || 0) }} pts</span>
              </div>
            </div>
          </div>
          <div class="form-card">
            <h3 class="form-title">{{ $t('settings.redeemTitle') }}</h3>
            <p class="form-hint">{{ $t('settings.redeemHint') }}</p>
            <div class="form-group">
              <label class="form-label" for="rd-pts">{{ $t('settings.pointsToRedeem') }}</label>
              <input id="rd-pts" v-model.number="redeemPoints" type="number" class="form-input"
                :min="minRedeem" :max="auth.user?.total_points || 0" :step="conversionRate.points" />
              <span class="input-hint">{{ $t('settings.minRedeem') }}: {{ minRedeem }} pts</span>
            </div>
            <div class="redeem-preview" v-if="redeemPoints >= minRedeem && redeemPoints <= (auth.user?.total_points || 0)">
              <div class="preview-col">
                <span class="preview-num">{{ redeemPoints }}</span>
                <span class="preview-lbl">{{ $t('settings.pts') }}</span>
              </div>
              <span class="preview-arrow" aria-hidden="true">→</span>
              <div class="preview-col">
                <span class="preview-num green">RM {{ pointsToMoney(redeemPoints) }}</span>
                <span class="preview-lbl">{{ $t('settings.cash') }}</span>
              </div>
            </div>
            <button class="save-btn full-btn redeem-btn" @click="openEwalletModal"
              :disabled="redeemPoints < minRedeem || redeeming || redeemPoints > (auth.user?.total_points || 0)">
              {{ $t('settings.redeemBtn') }}
            </button>
          </div>
        </template>

        <!-- Redemption history -->
        <template v-else-if="view === 'history'">
          <div v-if="loadingHistory" class="loading-placeholder"><div class="spinner-sm"></div></div>
          <div v-else-if="redemptionHistory.length === 0" class="empty-state">
            <p>{{ $t('settings.noRedemptions') }}</p>
          </div>
          <div v-else class="activity-list">
            <div v-for="r in redemptionHistory" :key="r.id" class="activity-item">
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
        </template>

        <!-- Language -->
        <template v-else-if="view === 'language'">
          <div class="menu-card" role="radiogroup" :aria-label="$t('settings.language')">
            <button v-for="opt in languages" :key="opt.code" type="button" class="menu-row"
              role="radio" :aria-checked="locale === opt.code" @click="setLang(opt.code)">
              <span class="menu-label">{{ opt.label }}</span>
              <PhCheck v-if="locale === opt.code" class="menu-check" :size="20" weight="bold" aria-hidden="true" />
            </button>
          </div>
          <p class="sub-hint">{{ $t('settings.languageHint') }}</p>
        </template>

        <!-- Account info + danger zone -->
        <template v-else-if="view === 'info'">
          <div class="menu-card">
            <div class="info-line"><span class="info-label">{{ $t('auth.name') }}</span><span class="info-value">{{ auth.user?.name || '—' }}</span></div>
            <div class="info-line"><span class="info-label">{{ $t('auth.email') }}</span><span class="info-value">{{ auth.user?.email || '—' }}</span></div>
            <div class="info-line"><span class="info-label">{{ $t('settings.accountType') }}</span><span class="info-value">{{ auth.isAdmin ? $t('settings.admin') : $t('settings.recycler') }}</span></div>
            <div class="info-line"><span class="info-label">{{ $t('settings.memberSince') }}</span><span class="info-value">{{ formatDate(auth.user?.created_at) }}</span></div>
          </div>
          <p class="sub-hint">{{ $t('settings.editNameHint') }}</p>
          <div class="danger-card">
            <h3 class="danger-title">{{ $t('settings.dangerZone') }}</h3>
            <p class="danger-text">{{ $t('settings.deleteHint') }}</p>
            <button type="button" class="delete-btn" @click="confirmDelete = true">
              <PhTrash :size="18" aria-hidden="true" /> {{ $t('settings.deleteAccount') }}
            </button>
          </div>
        </template>
      </div>
    </template>

    <div class="bottom-space"></div>

    <!-- ══ Profile picture sheet ══ -->
    <div v-if="showAvatarSheet" class="sheet-overlay" @click.self="closeAvatarSheet">
      <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="avatar-sheet-title">
        <div class="sheet-handle" aria-hidden="true"></div>
        <div class="sheet-head">
          <h2 id="avatar-sheet-title" class="sheet-title">{{ $t('settings.profilePicture') }}</h2>
          <button type="button" class="sheet-close" :aria-label="$t('settings.close')" @click="closeAvatarSheet">
            <PhX :size="22" weight="bold" aria-hidden="true" />
          </button>
        </div>
        <div class="sheet-preview">
          <UserAvatar :user="auth.user" :value="draftValue" :size="84" />
          <div class="sheet-preview-text">
            <strong>{{ auth.user?.name }}</strong>
            <span>{{ $t('settings.pictureHint') }}</span>
          </div>
        </div>
        <h3 class="sheet-label">{{ $t('settings.pictureIcon') }}</h3>
        <div class="pick-grid" role="radiogroup" :aria-label="$t('settings.pictureIcon')">
          <button v-for="ic in AVATAR_ICONS" :key="ic.key" type="button" role="radio"
            :aria-checked="draft.icon === ic.key" :aria-label="ic.label"
            :class="['pick-icon', { on: draft.icon === ic.key }]"
            :style="draft.icon === ic.key ? { background: draftHex, '--ring': draftHex } : null"
            @click="draft.icon = ic.key">
            <component :is="ic.icon" :size="24" aria-hidden="true" />
          </button>
        </div>
        <h3 class="sheet-label">{{ $t('settings.pictureBackground') }}</h3>
        <div class="pick-grid" role="radiogroup" :aria-label="$t('settings.pictureBackground')">
          <button v-for="c in AVATAR_COLOURS" :key="c.key" type="button" role="radio"
            :aria-checked="draft.colour === c.key" :aria-label="c.label"
            :class="['pick-colour', { on: draft.colour === c.key }]"
            :style="{ background: c.hex, '--ring': c.hex }"
            @click="draft.colour = c.key"></button>
        </div>
        <button type="button" class="sheet-save" :disabled="savingAvatar" @click="saveAvatar(draftValue)">
          {{ savingAvatar ? $t('settings.saving') : $t('settings.savePicture') }}
        </button>
        <button type="button" class="sheet-secondary" :disabled="savingAvatar" @click="saveAvatar(null)">
          {{ $t('settings.useInitial') }}
        </button>
        <span class="sheet-credit">Icons: Phosphor Icons (MIT)</span>
      </div>
    </div>

    <!-- E-wallet modal -->
    <div v-if="showEwalletModal" class="modal-overlay" @click.self="closeEwalletModal">
      <div class="modal">
        <button class="modal-close-btn" @click="closeEwalletModal" :disabled="redeeming" :aria-label="$t('settings.cancel')">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>{{ $t('settings.ewalletModalTitle') }}</h3>
        <div class="form-group">
          <label class="form-label" for="ew-prov">{{ $t('settings.ewalletProviderLabel') }}</label>
          <select id="ew-prov" v-model="ewalletProvider" class="form-input">
            <option value="" disabled>{{ $t('settings.ewalletProviderPlaceholder') }}</option>
            <option v-for="p in ewalletProviders" :key="p" :value="p">{{ p }}</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="ew-acc">{{ $t('settings.ewalletAccountLabel') }}</label>
          <input id="ew-acc" v-model="ewalletAccount" type="text" class="form-input" :placeholder="$t('settings.ewalletAccountPlaceholder')" />
        </div>
        <div v-if="redeemMsg" class="msg msg-err">{{ redeemMsg }}</div>
        <div class="confirm-btns">
          <button class="cancel-btn-lg" @click="closeEwalletModal" :disabled="redeeming">{{ $t('settings.cancel') }}</button>
          <button class="ewallet-confirm-btn" @click="redeemNow" :disabled="redeeming || !ewalletProvider || !ewalletAccount">
            {{ redeeming ? $t('settings.processing') : $t('settings.redeemBtn') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete account: the button stays disabled until the user types their
         own email exactly (GitHub's "type the repo name" pattern), because a
         plain "Are you sure?" is too easy to click through for a full delete. -->
    <div v-if="confirmDelete" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal delete-modal">
        <button class="modal-close-btn" @click="closeDeleteModal" :disabled="deletingAccount" :aria-label="$t('settings.cancel')">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>{{ $t('settings.deleteAccount') }}</h3>
        <p class="delete-warning">{{ $t('settings.deleteConfirm') }}</p>
        <p class="delete-instruction">
          {{ $t('settings.deleteTypeToConfirm') }} <strong>{{ auth.user?.email }}</strong>
        </p>
        <input v-model="deleteConfirmText" type="text" class="delete-confirm-input" :placeholder="auth.user?.email"
          autocomplete="off" autocapitalize="off" spellcheck="false" :aria-label="$t('settings.deleteTypeToConfirm')" />
        <div class="confirm-btns">
          <button class="cancel-btn-lg" @click="closeDeleteModal" :disabled="deletingAccount">{{ $t('settings.cancel') }}</button>
          <button class="delete-btn-confirm" @click="deleteAccount" :disabled="deletingAccount || deleteConfirmText !== auth.user?.email">
            {{ deletingAccount ? $t('settings.deleting') : $t('settings.confirmYes') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'
import UserAvatar from '@/components/UserAvatar.vue'
import { AVATAR_ICONS, AVATAR_COLOURS, DEFAULT_AVATAR, parseAvatar, formatAvatar, maskEmail } from '@/utils/avatarIcons.js'
import { version as appVersion } from '../../package.json'
import {
  PhPencilSimple, PhCaretRight, PhCaretLeft, PhScan, PhClockCounterClockwise,
  PhCoins, PhRecycle, PhLeaf, PhArrowDown, PhArrowUp, PhTicket, PhWallet, PhReceipt,
  PhTranslate, PhMoonStars, PhUserCircle, PhInfo, PhShieldCheck, PhGear, PhSignOut,
  PhLock, PhArrowsLeftRight, PhCheck, PhTrash, PhX,
} from '@phosphor-icons/vue'

const router      = useRouter()
const route       = useRoute()
const auth        = useAuthStore()
const theme       = inject('theme')
const toggleTheme = inject('toggleTheme')
const showToast   = inject('showToast')
const { locale, t } = useI18n()

// ── Sub-pages live in ?view= so the phone's Back button returns to the menu ──
const SUB_VIEWS = ['profile', 'redeem', 'history', 'language', 'info']
const view = computed(() => (SUB_VIEWS.includes(route.query.view) ? route.query.view : 'main'))
const subTitle = computed(() => ({
  profile: t('settings.editProfile'),
  redeem: t('settings.redeemEwallet'),
  history: t('settings.redemptionHistory'),
  language: t('settings.language'),
  info: t('settings.accountInfo'),
}[view.value] || ''))

function openView(v) {
  router.push({ query: { ...route.query, view: v } })
  window.scrollTo?.(0, 0)
}
function goBack() {
  if (window.history.state?.back) router.back()
  else router.replace({ query: {} })
}

// ── Summary card ──────────────────────────────────────────────────────────────
const summary = ref({
  total_points: auth.user?.total_points ?? 0,
  items_recycled: 0,
  carbon_saved_kg: auth.user?.total_carbon_saved ?? 0,
  earned_this_month: 0,
  redeemed_this_month: 0,
  active_vouchers: 0,
})
const carbonText = computed(() => {
  const kg = Number(summary.value.carbon_saved_kg) || 0
  return `${kg >= 100 ? Math.round(kg) : kg.toFixed(1)} kg`
})
function num(n) { return (Number(n) || 0).toLocaleString('en-MY') }
// "+513" / "−100", but a plain "0" (a sign on zero reads like a change).
function signed(n, sign) { return (Number(n) || 0) > 0 ? sign + num(n) : '0' }

async function loadSummary() {
  try {
    const res = await api.get('/user/account-summary')
    if (res.data?.summary) summary.value = { ...summary.value, ...res.data.summary }
  } catch { /* keep what auth.user already gave us */ }
}

// ── Profile picture ───────────────────────────────────────────────────────────
const showAvatarSheet = ref(false)
const savingAvatar = ref(false)
const draft = reactive({ ...DEFAULT_AVATAR })
const draftValue = computed(() => formatAvatar(draft.icon, draft.colour))
const draftHex = computed(() => AVATAR_COLOURS.find(c => c.key === draft.colour)?.hex)

function openAvatarSheet() {
  const current = parseAvatar(auth.user?.avatar_icon)
  draft.icon = current?.icon || DEFAULT_AVATAR.icon
  draft.colour = current?.colour || DEFAULT_AVATAR.colour
  showAvatarSheet.value = true
}
function closeAvatarSheet() {
  if (!savingAvatar.value) showAvatarSheet.value = false
}
async function saveAvatar(value) {
  savingAvatar.value = true
  try {
    const res = await api.put('/user/profile', { avatar_icon: value })
    const updated = { ...auth.user, avatar_icon: res.data?.user?.avatar_icon ?? value }
    auth.user = updated
    localStorage.setItem('rvm_user', JSON.stringify(updated))
    showAvatarSheet.value = false
    showToast?.(t('settings.pictureSaved'))
  } catch (e) {
    showToast?.(e.response?.data?.message || t('settings.savedFail'), 'error')
  } finally {
    savingAvatar.value = false
  }
}

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
const minRedeem         = ref(500)
const redeemPoints      = ref(500)
const redeeming         = ref(false)
const redeemMsg         = ref('')
const redemptionHistory = ref([])
const loadingHistory    = ref(true)
const showEwalletModal  = ref(false)
const ewalletProvider   = ref('')
const ewalletAccount    = ref('')
const ewalletProviders  = ["Touch 'n Go eWallet", 'GrabPay', 'Boost', 'ShopeePay']

function pointsToMoney(pts) {
  const rate = conversionRate.value
  return (((Number(pts) || 0) / rate.points) * rate.rm).toFixed(2)
}

function openEwalletModal() {
  ewalletProvider.value = ''
  ewalletAccount.value = ''
  redeemMsg.value = ''
  showEwalletModal.value = true
}

function closeEwalletModal() {
  if (redeeming.value) return
  showEwalletModal.value = false
}

async function redeemNow() {
  redeemMsg.value = ''
  if (!ewalletProvider.value || !ewalletAccount.value) return
  if (redeemPoints.value < minRedeem.value || redeemPoints.value > (auth.user?.total_points || 0)) return
  redeeming.value = true
  try {
    const res = await api.post('/user/redeem', {
      points: redeemPoints.value,
      ewallet_provider: ewalletProvider.value,
      ewallet_account: ewalletAccount.value,
    })
    if (res.data.success) {
      auth.updatePoints(res.data.total_points)
      showToast?.(res.data.message || t('settings.redeemOk'))
      showEwalletModal.value = false
      redeemPoints.value = minRedeem.value
      await Promise.all([loadRedemptionHistory(), loadSummary()])
    } else {
      redeemMsg.value = res.data.message || t('settings.redeemFail')
    }
  } catch (e) {
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
const languages = [
  { code: 'en', label: 'English' },
  { code: 'my', label: 'Bahasa Melayu' },
]
function setLang(lang) {
  locale.value = lang
  localStorage.setItem('rvm_lang', lang)
}

// ── Account ───────────────────────────────────────────────────────────────────
const confirmDelete     = ref(false)
const deletingAccount   = ref(false)
const deleteConfirmText = ref('')

function closeDeleteModal() {
  if (deletingAccount.value) return
  confirmDelete.value = false
  deleteConfirmText.value = ''
}

async function handleLogout() {
  // Captured before logout() clears the auth store: a regular web logout has
  // already wiped name/theme by the time GoodbyeView mounts, so pass them on.
  const name = auth.user?.name
  await auth.logout()
  router.push({ path: '/thank-you', query: { redirect: '/', name, theme: theme.value } })
}

async function deleteAccount() {
  deletingAccount.value = true
  try {
    await api.delete('/user/account')
    auth.clearAuth()
    router.push('/')
  } catch (e) {
    showToast(e.response?.data?.message || t('settings.deleteAccountFailed'), 'error')
  } finally {
    confirmDelete.value    = false
    deletingAccount.value  = false
    deleteConfirmText.value = ''
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
  // Fresh user data from the DB first (created_at, role, avatar_icon...).
  await auth.fetchMe()
  summary.value.total_points = auth.user?.total_points ?? summary.value.total_points

  try {
    const res = await api.get('/user/reward-rate')
    if (res.data?.rate) conversionRate.value = res.data.rate
    if (res.data?.min_points) minRedeem.value = res.data.min_points
    if (redeemPoints.value < minRedeem.value) redeemPoints.value = minRedeem.value
  } catch { /* use defaults */ }

  // Admins see the Admin Panel card instead of the points summary.
  if (!auth.isAdmin) await Promise.all([loadSummary(), loadRedemptionHistory()])
})
</script>

<style scoped>
/* Corner radius follows the DANA reference: 10px cards and buttons, pills
   and avatars fully round. */
.acct-page { background: var(--bg-primary); min-height: 100%; }

/* ── Header ── */
.acct-hero { background: var(--grad-header); padding: 26px 20px 60px; color: #fff; }
.hero-row { display: flex; align-items: center; gap: 16px; max-width: 640px; margin: 0 auto; }
.hero-avatar {
  position: relative; flex-shrink: 0; padding: 0; border: 0; background: none; cursor: pointer;
  border-radius: 50%; box-shadow: 0 0 0 2px rgba(255,255,255,0.9);
}
.hero-avatar:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }
.hero-avatar-edit {
  position: absolute; right: -2px; top: -2px; width: 24px; height: 24px; border-radius: 50%;
  background: #fff; color: var(--accent-blue); display: flex; align-items: center; justify-content: center;
  box-shadow: 0 1px 4px rgba(0,0,0,0.25);
}
.hero-role {
  position: absolute; left: 50%; bottom: -9px; transform: translateX(-50%);
  padding: 2px 8px; border-radius: 999px; background: #fff; color: #15803d;
  font-size: 11px; font-weight: 700; white-space: nowrap; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.hero-info { display: flex; flex-direction: column; gap: 6px; min-width: 0; flex: 1; }
.hero-name {
  display: inline-flex; align-items: center; gap: 4px; align-self: flex-start; max-width: 100%;
  padding: 0; border: 0; background: none; color: #fff; cursor: pointer;
  font: inherit; font-size: 19px; font-weight: 700; text-align: left;
}
.hero-name span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hero-email { font-size: 13px; opacity: 0.92; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hero-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 2px; }
.hero-pill {
  min-height: 38px; padding: 0 14px; box-sizing: border-box;
  display: inline-flex; align-items: center; gap: 6px;
  border: 1.5px solid rgba(255,255,255,0.8); border-radius: 999px;
  color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap;
}
.hero-pill:hover { background: rgba(255,255,255,0.12); }

/* ── Summary card ── */
.acct-summary {
  position: relative; margin: -40px 16px 0; max-width: 640px;
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.10);
}
@media (min-width: 672px) { .acct-summary { margin-left: auto; margin-right: auto; } }
.sum-tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); padding: 18px 8px 16px; }
.sum-tile { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; padding: 0 4px; }
.sum-tile + .sum-tile { border-left: 1px solid var(--border); }
.sum-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.sum-icon-green { background: rgba(34,197,94,0.14); color: #16a34a; }
.sum-icon-blue  { background: rgba(78,110,242,0.14); color: var(--accent-blue); }
.sum-icon-teal  { background: rgba(20,184,166,0.15); color: #0f9488; }
.sum-value { font-size: 18px; font-weight: 700; color: var(--text-primary); font-variant-numeric: tabular-nums; }
.sum-label { font-size: 12px; font-weight: 600; color: var(--text-primary); }
.sum-sub { font-size: 12px; font-weight: 600; }
.sum-sub-green { color: #15803d; }
[data-theme="dark"] .sum-sub-green { color: #4ade80; }
.sum-month { border-top: 1px solid var(--border); padding: 12px 16px 14px; display: flex; flex-direction: column; gap: 10px; }
.sum-month-title { font-size: 12px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-muted); }
.sum-month-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.sum-flow { display: flex; align-items: center; gap: 10px; }
.sum-flow-right { padding-left: 14px; border-left: 1px solid var(--border); }
.flow-icon { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.flow-in  { background: rgba(34,197,94,0.14); color: #16a34a; }
.flow-out { background: rgba(245,158,11,0.16); color: #d97706; }
.flow-text { display: flex; flex-direction: column; }
.flow-label { font-size: 13px; color: var(--text-muted); }
.flow-value { font-size: 16px; font-weight: 700; color: var(--text-primary); font-variant-numeric: tabular-nums; }

/* ── Admin shortcut card (in place of the summary for admins) ── */
.admin-card { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 12px 14px; align-items: center; padding: 16px; }
.admin-card-icon { width: 48px; height: 48px; border-radius: 10px; background: rgba(78,110,242,0.14); color: var(--accent-blue); display: flex; align-items: center; justify-content: center; }
.admin-card-text { display: flex; flex-direction: column; gap: 2px; }
.admin-card-text strong { font-size: 16px; color: var(--text-primary); }
.admin-card-text span { font-size: 13px; line-height: 1.4; color: var(--text-muted); }
.admin-card-btn {
  grid-column: 1 / -1; min-height: 48px; border-radius: 10px; background: var(--accent-blue); color: #fff;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  font-size: 15px; font-weight: 700; text-decoration: none;
}
.admin-card-btn:hover { filter: brightness(1.08); }

/* ── Menu lists ── */
.menu-group { margin: 20px 16px 0; }
@media (min-width: 672px) { .menu-group { max-width: 640px; margin-left: auto; margin-right: auto; } }
.menu-title { margin: 0 4px 8px; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-muted); }
.menu-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.menu-row {
  width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 14px;
  min-height: 56px; padding: 0 16px; border: 0; background: none; cursor: pointer;
  color: var(--text-primary); font: inherit; text-align: left; text-decoration: none;
}
.menu-row + .menu-row { border-top: 1px solid var(--border); }
.menu-row:hover { background: var(--bg-hover); }
.menu-row:focus-visible { outline: 2px solid var(--accent-blue); outline-offset: -2px; }
.menu-icon { flex-shrink: 0; }
.icon-purple { color: #a855f7; }
.icon-green  { color: #16a34a; }
.icon-orange { color: #d97706; }
.icon-blue   { color: var(--accent-blue); }
.icon-teal   { color: #0f9488; }
.icon-muted  { color: var(--text-muted); }
.menu-label { flex: 1; font-size: 16px; font-weight: 500; }
.menu-value { font-size: 14px; color: var(--text-muted); }
.menu-badge { padding: 2px 9px; border-radius: 999px; background: rgba(78,110,242,0.14); color: var(--accent-blue); font-size: 13px; font-weight: 700; }
.menu-chevron { color: var(--text-muted); flex-shrink: 0; }
.menu-check { color: var(--accent-blue); }

.switch { width: 46px; height: 28px; border-radius: 999px; background: #cbd5e1; position: relative; flex-shrink: 0; transition: background .2s; }
.switch.on { background: var(--accent-blue); }
.switch-knob { position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.3); transition: left .2s; }
.switch.on .switch-knob { left: 21px; }

/* ── Footer ── */
/* Same 640px column as the menu cards, centred (the margin here used to
   override the desktop centring and pushed Logout to the left). */
.acct-footer {
  margin: 24px auto 0; padding: 0 16px; max-width: 672px; box-sizing: border-box;
  display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.logout-btn {
  width: 100%; min-height: 48px; border-radius: 10px; border: 1.5px solid var(--accent-red);
  background: transparent; color: var(--accent-red); font: inherit; font-size: 16px; font-weight: 600;
  display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;
}
.logout-btn:hover { background: rgba(239,68,68,0.08); }
/* Desktop: a full 640px red bar is too loud, so a compact centred button. */
@media (min-width: 768px) {
  .logout-btn { width: auto; min-width: 220px; min-height: 44px; padding: 0 28px; font-size: 15px; }
}
.app-version { font-size: 12px; color: var(--text-muted); }
.bottom-space { height: 40px; }

/* ── Sub-pages ── */
.sub-bar {
  position: sticky; top: 0; z-index: 5; display: flex; align-items: center; gap: 6px;
  padding: 10px 8px; background: var(--bg-card); border-bottom: 1px solid var(--border);
}
.sub-back {
  width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
  border: 0; border-radius: 10px; background: none; color: var(--text-primary); cursor: pointer;
}
.sub-back:hover { background: var(--bg-hover); }
.sub-title { margin: 0; font-size: 18px; font-weight: 700; color: var(--text-primary); }
.sub-content { padding: 16px; display: flex; flex-direction: column; gap: 14px; max-width: 640px; margin: 0 auto; box-sizing: border-box; }
.sub-hint { margin: 0 4px; font-size: 13px; line-height: 1.5; color: var(--text-muted); }
.info-line { display: flex; justify-content: space-between; align-items: center; gap: 12px; min-height: 52px; padding: 0 16px; }
.info-line + .info-line { border-top: 1px solid var(--border); }
.danger-card { background: var(--bg-card); border: 1px solid rgba(239,68,68,0.35); border-radius: 10px; padding: 16px; display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
.danger-title { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--accent-red); }
.danger-text { margin: 0; font-size: 14px; line-height: 1.5; color: var(--text-secondary); }
.delete-btn {
  min-height: 48px; border-radius: 10px; border: 0; background: #dc2626; color: #fff;
  font: inherit; font-size: 16px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;
}
.delete-btn:hover { background: #b91c1c; }

/* ── Form cards (sub-pages) ── */
.form-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 16px; }
.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.form-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin: 0; }
.form-hint { font-size: 12px; color: var(--text-muted); margin: 6px 0 14px; }
.form-group { margin-bottom: 12px; }
.form-label { display: block; font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
.form-input {
  width: 100%; box-sizing: border-box; min-height: 44px; padding: 10px 12px; border-radius: 10px;
  background: var(--bg-primary); border: 1px solid var(--border); color: var(--text-primary); font-size: 14px;
}
.form-input:focus { outline: none; border-color: var(--accent-blue); }
.input-hint { display: block; font-size: 11px; color: var(--text-muted); margin-top: 4px; }
.info-rows { display: flex; flex-direction: column; }
.info-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 13px; color: var(--text-muted); font-weight: 500; }
.info-value { font-size: 14px; color: var(--text-primary); font-weight: 600; text-align: right; word-break: break-word; }
.info-value.muted { color: var(--text-muted); letter-spacing: 2px; }
.edit-btn, .cancel-btn, .save-btn-sm {
  display: inline-flex; align-items: center; gap: 5px; min-height: 36px; padding: 0 14px;
  border-radius: 999px; font-size: 12px; font-weight: 700; cursor: pointer;
}
.edit-btn { background: var(--bg-hover); border: 1px solid var(--border); color: var(--text-secondary); }
.edit-btn:hover { border-color: var(--accent-blue); color: var(--accent-blue); }
.edit-actions { display: flex; gap: 8px; }
.cancel-btn { background: none; border: 1px solid var(--border); color: var(--text-muted); }
.save-btn-sm { background: var(--accent-blue); border: none; color: #fff; }
.save-btn-sm:disabled { opacity: 0.5; cursor: not-allowed; }
.full-btn {
  width: 100%; min-height: 48px; border: none; border-radius: 10px; margin-top: 14px;
  font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.full-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.save-btn { background: var(--accent-blue); color: #fff; }
.redeem-btn { background: #16a34a; }
.msg { padding: 10px 12px; border-radius: 10px; font-size: 13px; margin-top: 12px; }
.msg-ok  { background: rgba(34,197,94,0.15); color: var(--accent-green); border: 1px solid rgba(34,197,94,0.3); }
.msg-err { background: rgba(239,68,68,0.15); color: var(--accent-red); border: 1px solid rgba(239,68,68,0.3); }
.rate-row { display: flex; gap: 10px; }
.rate-card { flex: 1; display: flex; align-items: center; gap: 10px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 12px; }
.rate-icon { font-size: 20px; color: var(--text-secondary); }
.rate-info { display: flex; flex-direction: column; }
.rate-info strong { font-size: 11px; color: var(--text-secondary); font-weight: 600; }
.rate-info span { font-size: 13px; color: var(--text-primary); font-weight: 700; margin-top: 2px; }
.redeem-preview { display: flex; align-items: center; justify-content: center; gap: 16px; background: var(--bg-hover); border-radius: 10px; padding: 16px; }
.preview-col { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.preview-num { font-size: 20px; font-weight: 700; color: var(--text-primary); }
.preview-num.green { color: var(--accent-green); }
.preview-lbl { font-size: 11px; color: var(--text-muted); text-transform: uppercase; }
.preview-arrow { font-size: 20px; color: var(--text-muted); }
.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.empty-state { text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px; background: var(--bg-card); border-radius: 10px; border: 1px solid var(--border); }
.activity-list { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.activity-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; }
.activity-item + .activity-item { border-top: 1px solid var(--border); }
.activity-info { flex: 1; display: flex; flex-direction: column; }
.activity-desc { font-size: 14px; color: var(--text-primary); }
.activity-time { font-size: 12px; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; gap: 6px; }
.activity-pts { font-size: 14px; font-weight: 700; }
.pts-green { color: var(--accent-green); }
.status-badge { padding: 1px 7px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.badge-pending   { background: rgba(234,179,8,0.2); color: var(--accent-yellow); }
.badge-approved, .badge-completed { background: rgba(34,197,94,0.2); color: var(--accent-green); }
.badge-rejected  { background: rgba(239,68,68,0.2); color: var(--accent-red); }
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Profile picture sheet ── */
.sheet-overlay {
  position: fixed; inset: 0; z-index: 1100; background: rgba(15,23,42,0.55);
  display: flex; align-items: flex-end; justify-content: center;
}
.sheet {
  width: 100%; max-width: 520px; max-height: 92dvh; overflow-y: auto; box-sizing: border-box;
  background: var(--bg-card); border-radius: 16px 16px 0 0;
  padding: 10px 20px max(24px, env(safe-area-inset-bottom));
  display: flex; flex-direction: column; gap: 16px; box-shadow: 0 -8px 30px rgba(0,0,0,0.25);
}
@media (min-width: 600px) { .sheet-overlay { align-items: center; } .sheet { border-radius: 16px; } }
.sheet-handle { align-self: center; width: 40px; height: 5px; border-radius: 999px; background: #cbd5e1; }
.sheet-head { display: flex; align-items: center; justify-content: space-between; }
.sheet-title { margin: 0; font-size: 19px; font-weight: 700; color: var(--text-primary); }
.sheet-close { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: none; color: var(--text-muted); cursor: pointer; }
.sheet-close:hover { background: var(--bg-hover); }
.sheet-preview { display: flex; align-items: center; gap: 16px; }
.sheet-preview-text { display: flex; flex-direction: column; gap: 2px; }
.sheet-preview-text strong { font-size: 16px; color: var(--text-primary); }
.sheet-preview-text span { font-size: 13px; color: var(--text-muted); }
.sheet-label { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-muted); }
.pick-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; justify-items: center; margin-top: -6px; }
.pick-icon, .pick-colour {
  width: 48px; height: 48px; border-radius: 50%; border: 0; padding: 0; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.pick-icon { background: var(--bg-hover); color: var(--text-secondary); }
.pick-icon.on { color: #fff; }
.pick-icon.on, .pick-colour.on { box-shadow: 0 0 0 3px var(--bg-card), 0 0 0 5px var(--ring); }
.pick-icon:focus-visible, .pick-colour:focus-visible { outline: 2px solid var(--accent-blue); outline-offset: 3px; }
.sheet-save {
  min-height: 48px; border: 0; border-radius: 10px; background: var(--accent-blue); color: #fff;
  font: inherit; font-size: 16px; font-weight: 600; cursor: pointer;
}
.sheet-secondary { min-height: 44px; border: 0; background: none; color: var(--accent-blue); font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: -8px; }
.sheet-save:disabled, .sheet-secondary:disabled { opacity: 0.6; cursor: wait; }
.sheet-credit { text-align: center; font-size: 11px; color: var(--text-muted); margin-top: -8px; }

/* ── Modals (e-wallet, delete) ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.55);
  display: flex; align-items: flex-start; justify-content: center; z-index: 1100;
  padding: max(24px, env(safe-area-inset-top)) 20px max(24px, env(safe-area-inset-bottom));
  overflow-y: auto; overscroll-behavior: contain;
}
.modal {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 28px;
  width: 420px; max-width: 100%; margin: auto; position: relative; box-sizing: border-box;
  box-shadow: 0 18px 48px rgba(0,0,0,0.28);
}
.modal h3 { font-size: 17px; font-weight: 700; color: var(--text-primary); padding-right: 32px; margin: 0 0 14px; }
.modal-close-btn { position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; font-size: 18px; line-height: 0; }
.modal-close-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.delete-warning { font-size: 13px; color: var(--text-primary); margin-bottom: 10px; }
.delete-instruction { font-size: 13px; color: var(--text-secondary); margin-bottom: 10px; word-break: break-all; }
.delete-confirm-input { width: 100%; min-height: 44px; padding: 10px 12px; border-radius: 10px; background: var(--bg-primary); border: 1px solid var(--border); color: var(--text-primary); font-size: 14px; box-sizing: border-box; }
.delete-confirm-input:focus { outline: none; border-color: var(--accent-red); }
.confirm-btns { display: flex; gap: 10px; margin-top: 18px; }
.cancel-btn-lg, .delete-btn-confirm, .ewallet-confirm-btn { flex: 1; min-height: 44px; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; }
.cancel-btn-lg { background: var(--bg-hover); border: 1px solid var(--border); color: var(--text-primary); }
.delete-btn-confirm { background: var(--accent-red); border: none; color: #fff; }
.ewallet-confirm-btn { background: #16a34a; border: none; color: #fff; }
.delete-btn-confirm:disabled, .ewallet-confirm-btn:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 380px) {
  .sum-value { font-size: 16px; }
  .sum-label { font-size: 11px; }
  .menu-label { font-size: 15px; }
  .pick-grid { gap: 6px; }
  .pick-icon, .pick-colour { width: 44px; height: 44px; }
  .rate-row { flex-direction: column; }
}
</style>
