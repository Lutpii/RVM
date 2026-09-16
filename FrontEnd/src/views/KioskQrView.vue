<template>
  <div class="kiosk-qr" data-theme="light">
    <div class="kiosk-bg"></div>

    <!-- Header -->
    <div class="qr-header">
      <button class="back-btn" @click="$router.push(`/kiosk/${machineCode}`)">← {{ $t('kioskQr.back') }}</button>
      <span class="machine-label">{{ machineCode }}</span>
    </div>

    <!-- WAITING — show QR -->
    <div v-if="state === 'waiting'" class="qr-content">
      <h2 class="qr-title">{{ $t('kioskQr.title') }}</h2>
      <p class="qr-sub">{{ $t('kioskQr.subtitle') }}</p>

      <div class="qr-box">
        <div v-if="loadingQr" class="qr-loading">
          <div class="spinner"></div>
          <span>{{ $t('kioskQr.generating') }}</span>
        </div>
        <img
          v-else-if="qrSvgSrc"
          :src="qrSvgSrc"
          class="qr-image"
          :alt="$t('kioskQr.qrAlt')"
        />
        <div v-else class="qr-error">{{ $t('kioskQr.failedLoad') }}</div>

        <!-- Animated scan corners -->
        <div class="corner tl"></div>
        <div class="corner tr"></div>
        <div class="corner bl"></div>
        <div class="corner br"></div>
      </div>

      <div class="qr-url" v-if="currentToken">{{ $t('kioskQr.tokenLabel') }}: {{ currentToken }}</div>

      <div class="qr-steps">
        <div class="step"><span class="step-num">1</span> {{ $t('kioskQr.step1') }}</div>
        <div class="step"><span class="step-num">2</span> {{ $t('kioskQr.step2') }}</div>
        <div class="step"><span class="step-num">3</span> {{ $t('kioskQr.step3') }}</div>
      </div>

      <div class="timer-bar">
        <div class="timer-fill" :style="{ transform: 'scaleX(' + (timerPct / 100) + ')' }"></div>
      </div>
      <p class="timer-text">{{ $t('kioskQr.expiresIn', { seconds: expiresInSec }) }}</p>

      <div class="guest-divider">
        <span class="guest-divider-line"></span>
        <span class="guest-divider-text">{{ $t('kioskQr.or') }}</span>
        <span class="guest-divider-line"></span>
      </div>

      <button class="guest-btn" @click="startAsGuest">
        <svg class="guest-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg>
        {{ $t('kioskQr.continueGuest') }}
        <span class="guest-btn-note">{{ $t('kioskQr.pointsDonated') }}</span>
      </button>
    </div>

    <!-- SCANNED — session active -->
    <div v-else-if="state === 'scanned'" class="qr-content scanned-content">
      <div class="success-ring">
        <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 12 10 18 20 6"/></svg>
      </div>
      <h2 class="qr-title green">{{ $t('kioskQr.sessionStarted') }}</h2>
      <p class="qr-sub" v-if="scannedUser">
        {{ $t('kioskQr.welcomeLabel') }} <strong>{{ scannedUser }}</strong>
      </p>
      <p class="qr-sub">{{ $t('kioskQr.redirecting') }}</p>
    </div>

    <!-- EXPIRED -->
    <div v-else-if="state === 'expired'" class="qr-content">
      <PhHourglass class="expired-icon" weight="regular" />
      <h2 class="qr-title">{{ $t('kioskQr.expired') }}</h2>
      <p class="qr-sub">{{ $t('kioskQr.generatingNew') }}</p>
    </div>

    <div class="kiosk-footer">
      <BrandFooter class="brand-logo" />
      <span>UMPSA &nbsp;·&nbsp; Eco Smart Campus</span>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api, { setKioskToken } from '@/services/api'
import { useRvmStore } from '@/store/rvm'
import { PhHourglass } from '@phosphor-icons/vue'
import BrandFooter from '@/components/BrandFooter.vue'

const router      = useRouter()
const route       = useRoute()
const rvm         = useRvmStore()
const setTheme    = inject('setTheme')
const { t }       = useI18n()
const machineCode = route.params.machineCode || 'RVM-001'

const state          = ref('waiting') // waiting | scanned | expired
const loadingQr      = ref(true)
const qrSvgSrc       = ref('')
const scanUrl        = ref('')
const currentToken   = ref('')
const scannedUser    = ref('')
const QR_REFRESH_SECONDS = 60
const QR_SCREEN_SECONDS  = 120

const expiresInSec   = ref(QR_REFRESH_SECONDS)
const timerPct       = ref(100)

let pollInterval  = null
let timerInterval = null
let screenTimeout = null
let isActive      = false
let screenDeadline = 0
let machineData   = null  // cached from generate response

async function generateQr() {
  loadingQr.value  = true
  qrSvgSrc.value   = ''
  state.value      = 'waiting'
  expiresInSec.value = QR_REFRESH_SECONDS
  timerPct.value   = 100

  try {
    const res = await api.get(`/qr/generate/${machineCode}`)
    if (res.data.success) {
      currentToken.value = res.data.token
      scanUrl.value      = res.data.scan_url
      qrSvgSrc.value = `data:image/svg+xml;base64,${res.data.qr_svg}`
      if (res.data.machine) machineData = res.data.machine
      startPoll()
      startTimer()
    }
  } catch {
    qrSvgSrc.value = ''
  } finally {
    loadingQr.value = false
  }
}

function startPoll() {
  clearInterval(pollInterval)
  pollInterval = setInterval(async () => {
    if (!currentToken.value || !isActive) return
    try {
      const res = await api.get(`/qr/status/${currentToken.value}`)
      if (!isActive) return
      if (res.data.status === 'scanned') {
        scannedUser.value = res.data.user_name || t('kioskQr.defaultUserName')
        state.value = 'scanned'
        clearIntervals()
        clearTimeout(screenTimeout)
        isActive = false

        // Apply the scanned user's theme for the rest of this kiosk session —
        // default light if they've never set a preference. Landing/QR screens
        // stay light regardless, via their own data-theme="light" attribute
        // (see .kiosk-qr's root element) — independent of this app-wide theme.
        const scannedTheme = res.data.theme_preference || 'light'
        setTheme(scannedTheme)

        if (res.data.kiosk_token) {
          // Authenticated kiosk session — real user, real API calls
          setKioskToken(res.data.kiosk_token)
          rvm.isGuest = false
          rvm.guestMachineCode = machineCode

          if (res.data.session) {
            rvm.setSession(res.data.session)
            rvm.setMachine(res.data.session.machine)
            rvm.setStep('bin_check')
          } else {
            // kiosk_token ready but session not created yet (phone still processing)
            // Start/resume the session from the kiosk using kiosk auth
            const md = machineData
            if (md) {
              try {
                const sessionRes = await api.post('/sessions/start', {
                  machine_id: md.id,
                  qr_token:   currentToken.value,
                })
                if (sessionRes.data.success) {
                  rvm.setSession(sessionRes.data.session)
                  rvm.setMachine(md)
                  rvm.setStep('bin_check')
                }
              } catch (e) {
                const errData = e.response?.data
                if (errData?.session_code) {
                  try {
                    const showRes = await api.get(`/sessions/${errData.session_code}`)
                    if (showRes.data.success) {
                      rvm.setSession(showRes.data.session)
                      rvm.setMachine(showRes.data.session.machine || md)
                      rvm.setStep('bin_check')
                    }
                  } catch { /* proceed anyway, session page will handle */ }
                }
              }
            }
          }

          // Real logged-in user — show the welcome splash before the session starts.
          router.push({
            path: '/welcome',
            query: { redirect: `/kiosk/${machineCode}/session`, name: scannedUser.value, theme: scannedTheme },
          })
          return
        }

        // No kiosk token — pure guest mode
        const md = machineData ? {
          id: machineData.id,
          name: machineData.name,
          location: '',
          aluminum_level: machineData.bins?.aluminum ?? 0,
          plastic_level:  machineData.bins?.plastic  ?? 0,
          glass_level:    machineData.bins?.glass    ?? 0,
          paper_level:    machineData.bins?.paper    ?? 0,
        } : null
        rvm.startGuestSession(machineCode, md)
        if (rvm.session) rvm.session.user_name = t('kioskQr.guestName')
        // scannedTheme already defaulted to 'light' above (guests have no saved preference).
        router.push({
          path: '/welcome',
          query: { redirect: `/kiosk/${machineCode}/session`, name: t('kioskQr.guestName'), theme: scannedTheme },
        })
      } else if (res.data.status === 'expired') {
        handleExpiry()
      }
    } catch { /* ignore */ }
  }, 2000)
}

function startTimer() {
  clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    if (Date.now() >= screenDeadline) {
      returnToKiosk()
      return
    }

    expiresInSec.value -= 1
    timerPct.value = (expiresInSec.value / QR_REFRESH_SECONDS) * 100
    if (expiresInSec.value <= 0) handleExpiry()
  }, 1000)
}

function handleExpiry() {
  clearIntervals()

  if (Date.now() >= screenDeadline) {
    returnToKiosk()
    return
  }

  state.value = 'expired'
  generateQr()
}

function returnToKiosk() {
  clearIntervals()
  clearTimeout(screenTimeout)
  isActive = false
  router.replace(`/kiosk/${machineCode}`)
}

function clearIntervals() {
  clearInterval(pollInterval)
  clearInterval(timerInterval)
}

function startAsGuest() {
  clearIntervals()
  clearTimeout(screenTimeout)
  const md = machineData ? {
    id: machineData.id,
    name: machineData.name,
    location: '',
    aluminum_level: machineData.bins?.aluminum ?? 0,
    plastic_level:  machineData.bins?.plastic  ?? 0,
    glass_level:    machineData.bins?.glass    ?? 0,
    paper_level:    machineData.bins?.paper    ?? 0,
  } : null
  rvm.startGuestSession(machineCode, md)
  if (rvm.session) rvm.session.user_name = t('kioskQr.guestName')
  // Guests default to light mode.
  setTheme('light')
  router.push({
    path: '/welcome',
    query: { redirect: `/kiosk/${machineCode}/session`, name: t('kioskQr.guestName'), theme: 'light' },
  })
}

onMounted(() => {
  isActive = true
  screenDeadline = Date.now() + (QR_SCREEN_SECONDS * 1000)
  screenTimeout = setTimeout(returnToKiosk, QR_SCREEN_SECONDS * 1000)
  // Always wait for a real QR scan — a kiosk screen must never silently
  // start a session as whoever happens to be logged into this browser (e.g.
  // a leftover phone session from testing both flows on one machine). The
  // only legitimate way to authenticate a kiosk session is scanning the QR
  // with a phone, which hands this screen a kiosk_token (see handleScanned()
  // above).
  generateQr()
})
onBeforeUnmount(() => {
  isActive = false
  clearIntervals()
  clearTimeout(screenTimeout)
})
</script>

<style scoped>
.kiosk-qr {
  min-height: 100vh;
  min-height: 100dvh;
  height: 100vh;
  height: 100dvh;
  background: var(--bg-primary);
  color: var(--text-primary);
  display: flex;
  flex-direction: column;
  align-items: center;
  /* .qr-header is position:absolute (below) so it doesn't count as a flex
     child here - this column is really just .qr-content + .kiosk-footer.
     space-between + a real gap floor: on a short screen the gap shrinks
     toward that floor instead of the two ever touching/overlapping
     (confirmed by screenshot at 1280x800 before this existed - logo/text
     drawn right on top of the guest button); on a taller screen the extra
     room goes into growing that gap, which is what gives
     KioskLandingView's absolute-positioned footer its own generous
     breathing room above it - same visual effect, without needing this
     view's much taller content to risk the same overlap. */
  justify-content: space-between;
  gap: 32px;
  position: relative;
  /* Room for .qr-header, now that it's absolute and no longer a flex
     sibling reserving its own space. */
  padding-top: 68px;
  /* auto, not hidden: on a kiosk touchscreen short/dense enough that the
     compact media query below still doesn't make everything fit (the QR
     code itself included), this scrolls instead of silently clipping
     content off the bottom of the screen with no way to reach it. */
  overflow-y: auto;
  overflow-x: hidden;
}

.kiosk-bg {
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% -10%, rgba(78,110,242,0.12) 0%, transparent 60%);
}

.qr-header {
  /* Pinned at the top independent of .kiosk-qr's flex layout below (Back
     button + machine code should stay put regardless of how much/little
     room the content+footer split takes up), not a flex child that
     would otherwise eat into the space-between budget. */
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 32px;
}
.back-btn {
  background: rgba(0,0,0,0.04);
  border: 1px solid rgba(0,0,0,0.08);
  color: rgba(26,32,44,0.65);
  padding: 8px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
}
.back-btn:hover { background: rgba(0,0,0,0.08); }
.machine-label {
  color: rgba(26,32,44,0.4);
  font-size: 13px;
  font-family: monospace;
}

.qr-content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 13px;
  padding: 20px 40px 40px;
  text-align: center;
  max-width: 520px;
  width: 100%;
}

.kiosk-footer {
  /* Same size/color/gap as KioskLandingView's footer - position:static
     (not absolute) is the one deliberate difference, so it's a real flex
     child of .kiosk-qr (spaced out by justify-content:space-between
     above) instead of pinned to a fixed bottom offset that this view's
     taller content can run into. */
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  color: var(--text-muted);
  font-size: 13px;
  text-align: center;
  white-space: nowrap;
  padding-bottom: max(12px, env(safe-area-inset-bottom));
}

.brand-logo {
  --brand-logo-height: 96px;
  max-width: calc(100vw - 40px);
}

.qr-title {
  font-size: 36px;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0;
}
.qr-title.green { color: var(--accent-green); }
.qr-sub {
  font-size: 16px;
  color: rgba(26,32,44,0.6);
  margin: 0;
}

/* QR Box */
.qr-box {
  position: relative;
  width: 260px;
  height: 260px;
  background: var(--bg-card);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 60px rgba(78,110,242,0.3);
}
.qr-image {
  width: 220px;
  height: 220px;
}
.qr-loading, .qr-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #64748b;
  font-size: 14px;
}
.spinner {
  width: 36px; height: 36px;
  border: 3px solid var(--border);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.corner {
  position: absolute;
  width: 24px; height: 24px;
  border-color: var(--accent-blue);
  border-style: solid;
  border-width: 0;
}
.corner.tl { top: -2px; left: -2px; border-top-width: 3px; border-left-width: 3px; border-radius: 4px 0 0 0; }
.corner.tr { top: -2px; right: -2px; border-top-width: 3px; border-right-width: 3px; border-radius: 0 4px 0 0; }
.corner.bl { bottom: -2px; left: -2px; border-bottom-width: 3px; border-left-width: 3px; border-radius: 0 0 0 4px; }
.corner.br { bottom: -2px; right: -2px; border-bottom-width: 3px; border-right-width: 3px; border-radius: 0 0 4px 0; }

.qr-url {
  font-family: monospace;
  font-size: 14px;
  /* var(--text-muted), not a hand-picked low-alpha rgba: this view is
     pinned to data-theme="light" today, but the low-opacity gray this
     used to be (~30% black on white) fell well under WCAG AA contrast -
     genuinely hard to read, not just faint by design. The token is
     already tuned for real contrast against --bg-primary and stays
     correct if this view's theme ever changes. */
  color: var(--text-muted);
  word-break: break-all;
  max-width: 320px;
}

/* Steps */
.qr-steps {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  /* Just wide enough that the longest step ("Scan this QR code with your
     phone") doesn't wrap to 2 lines at 14px - a 260px box (matching
     .qr-box) forced exactly that. Kept fairly narrow (not much wider than
     that) rather than very wide, since .step is left-aligned (see its own
     comment): a too-wide box would visibly shift the 1/2/3 column away
     from center even though the block itself is centered by this
     container's parent. */
  max-width: 300px;
}
.step {
  display: flex;
  align-items: center;
  /* NOT justify-content:center: each step's text is a different length,
     so centering every row independently put the 1/2/3 badges at 3
     different x-positions instead of a straight column - the "1-3 tidak
     sejajar" bug. Left-aligned rows keep the badges lined up; the whole
     .qr-steps block is still centered as a unit under the QR code via
     its container's align-items:center + constrained max-width. */
  gap: 12px;
  color: rgba(26,32,44,0.55);
  font-size: 14px;
}
.step-num {
  width: 24px; height: 24px;
  border-radius: 50%;
  background: rgba(78,110,242,0.3);
  border: 1px solid rgba(78,110,242,0.5);
  color: #93b4fb;
  font-size: 12px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

/* Timer */
.timer-bar {
  width: 260px;
  height: 4px;
  background: rgba(0,0,0,0.08);
  border-radius: 2px;
  overflow: hidden;
}
.timer-fill {
  height: 100%;
  width: 100%;
  transform-origin: left;
  background: linear-gradient(90deg, var(--accent-blue), var(--accent-green));
  transition: transform 1s linear;
  border-radius: 2px;
}
.timer-text {
  color: rgba(26,32,44,0.4);
  font-size: 12px;
  margin: 0;
}

/* Scanned state */
.scanned-content { padding-top: 60px; }
.success-ring {
  width: 120px; height: 120px;
  border-radius: 50%;
  background: rgba(34,197,94,0.15);
  border: 3px solid var(--accent-green);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 40px rgba(34,197,94,0.3);
  animation: pop 0.4s ease;
}
@keyframes pop { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.success-icon { width: 52px; height: 52px; color: var(--accent-green); }
.redirect-hint { color: rgba(26,32,44,0.4); font-size: 14px; margin: 0; }

/* Expired */
.expired-icon { font-size: 64px; }

/* Guest section */
.guest-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 260px;
  margin: 0;
}
.guest-divider-line {
  flex: 1;
  height: 1px;
  background: rgba(0,0,0,0.1);
}
.guest-divider-text {
  color: rgba(26,32,44,0.35);
  font-size: 12px;
}

.guest-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  width: 260px;
  padding: 14px 20px;
  background: rgba(0,0,0,0.02);
  border: 1px dashed rgba(0,0,0,0.15);
  border-radius: 12px;
  color: rgba(26,32,44,0.7);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.guest-btn:hover {
  background: rgba(0,0,0,0.05);
  border-color: rgba(0,0,0,0.3);
  color: rgba(26,32,44,0.95);
}
.guest-btn-icon { width: 22px; height: 22px; margin-bottom: 2px; }
.guest-btn-note {
  font-size: 11px;
  font-weight: 400;
  color: rgba(34,197,94,0.75);
  letter-spacing: 0.02em;
}

/* Compact layout for real kiosk touchscreens - 900px, not 650px: this
   view's content (title+subtitle+QR+token+3 steps+timer+guest button+
   footer) measures ~970px tall at full/base size with a 2-line title
   (e.g. the Malay translation), so a common 1280x800 10" panel was
   actually still hitting the "base" styles meant for a full desktop
   monitor and overflowing by ~170px - confirmed by measuring
   .kiosk-qr's scrollHeight in a real browser. 900px comfortably covers
   the resolutions actual kiosk touch panels ship at; only genuine
   full-height desktop monitors see the spacious base layout now. */
@media (max-height: 900px) {
  /* Pulled up (small padding-top/gap, flex-start not the base rule's
     centering effect from space-between having more headroom to grow
     into) so the title sits close to the header instead of near mid
     -screen, and there's a comfortable margin below the shortest tested
     real kiosk resolution (1024x600) before anything would need to
     scroll - matches KioskLandingView never needing to scroll either. */
  .kiosk-qr { padding-top: 40px; gap: 8px; }
  .qr-header { padding: 6px 16px; }

  .qr-content { max-width: 760px; gap: 8px; padding: 0 24px 0; }
  .qr-title { font-size: 22px; }
  .qr-sub { font-size: 12px; }

  .qr-box { width: 150px; height: 150px; margin: 2px 0; }
  .qr-image { width: 126px; height: 126px; }
  .qr-url { font-size: 12px; max-width: 240px; }

  /* Kept vertical (this view's base column layout, top-to-bottom 1-2-3).
     Wide enough (like the base rule above) that the longest step doesn't
     wrap to 2 lines at this font-size; .step's justify-content:center
     (base rule, cascades here) keeps each row centered regardless. */
  .qr-steps { max-width: 300px; gap: 6px; }
  .step { font-size: 12px; }
  .step-num { width: 18px; height: 18px; font-size: 10px; }

  .timer-bar, .guest-divider { width: 220px; }
  .timer-text { font-size: 11px; }
  .guest-btn {
    width: auto;
    min-width: 280px;
    min-height: 40px;
    flex-direction: row;
    justify-content: center;
    padding: 6px 16px;
    gap: 8px;
  }
  .guest-btn-icon { width: 16px; height: 16px; margin-bottom: 0; }
  .guest-btn-note { margin-left: 2px; font-size: 10px; }

  /* Same logo/text size as KioskLandingView's compact footer. */
  .kiosk-footer { gap: 3px; font-size: 11px; padding-bottom: max(6px, env(safe-area-inset-bottom)); }
  .kiosk-footer .brand-logo { --brand-logo-height: 90px; }

  .scanned-content { padding-top: 20px; }
  .success-ring { width: 80px; height: 80px; }
  .success-icon { width: 36px; height: 36px; }
}
</style>
