<template>
  <div class="session-page">
    <!-- Demo kiosk session (exhibition, no physical machine). A copy of
         RvmSessionView.vue's screens with every hardware / camera / compactor /
         AI-service branch removed: the visitor picks the item (K5), the capture
         and the AI decision are simulated and labelled SIMULATED, and the points
         are saved for real (the backend marks them DEMO via the demo machine).
         The "DEMO MODE" banner is rendered globally by App.vue. -->
    <!-- RVM Header -->
    <div class="rvm-header">
      <div class="header-controls">
        <button class="ctrl-btn" @click="toggleTheme()">
          <svg v-if="theme === 'dark'" class="ctrl-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
          <svg v-else class="ctrl-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
        </button>
        <button class="ctrl-btn" @click="toggleLang">{{ locale === 'en' ? 'MY' : 'EN' }}</button>
      </div>
      <h1 class="rvm-title">{{ machineName || $t('app.name') }}</h1>
      <p class="welcome-text" v-if="auth.user">{{ $t('session.welcomeLabel') }} {{ auth.user.name }}</p>
      <p class="welcome-text" v-else-if="rvm.session?.user_name && !rvm.isGuest">{{ $t('session.welcomeLabel') }} {{ rvm.session.user_name }}</p>
      <p class="welcome-text guest-label" v-else-if="rvm.isGuest">{{ $t('session.guestLabel') }}</p>
      <div class="header-badges">
        <div class="badge points-badge">
          <span class="badge-label">{{ rvm.isGuest ? $t('session.donating') : $t('session.totalPoints') }}</span>
          <span class="badge-value points-animate">{{ displayPoints }}</span>
        </div>
        <div class="badge status-badge">
          <span class="badge-label">{{ $t('session.carbonSavedLabel') }}</span>
          <span class="badge-value">{{ sessionCarbon.toFixed(3) }} kg CO2</span>
        </div>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="progress-bar">
      <div class="progress-fill" :style="{ transform: 'scaleX(' + (progressWidth / 100) + ')' }"></div>
    </div>

    <!-- Main Step Content -->
    <div class="rvm-body">
      <Transition name="step-transition" mode="out-in">

        <!-- K4 BIN CHECK step (simulated, always passes) -->
        <div v-if="rvm.currentStep === 'bin_check'" key="bin_check" class="step-content centered">
          <div class="spinner-lg"></div>
          <h2 class="step-status">{{ $t('session.checkingBin') }}</h2>
          <span class="sim-badge">{{ $t('demoKiosk.simulated') }}</span>
        </div>

        <!-- K5 CHOOSE step (replaces the real kiosk's insert step) -->
        <div v-else-if="rvm.currentStep === 'choose'" key="choose" class="step-content centered">
          <h2 class="step-status">{{ $t('demoKiosk.chooseTitle') }}</h2>
          <p class="step-sub">{{ $t('demoKiosk.chooseHint') }}</p>
          <div class="demo-tiles">
            <button
              v-for="item in DEMO_ITEMS"
              :key="item.id"
              class="demo-tile min-h-kiosk-touch"
              :class="{ 'demo-tile-reject': !item.accepted }"
              :disabled="flowBusy"
              @click="chooseItem(item)"
            >
              <svg class="demo-tile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(item.id)"></svg>
              <span class="demo-tile-name">{{ itemLabel(item.id) }}</span>
            </button>
          </div>
        </div>

        <!-- K6 CAMERA step (simulated: no camera, a 3-2-1 countdown then a flash) -->
        <div v-else-if="rvm.currentStep === 'camera'" key="camera" class="step-content centered">
          <div class="camera-container">
            <div class="camera-sim">
              <svg class="camera-sim-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(chosenId)"></svg>
            </div>
            <div class="scan-overlay">
              <div class="scan-corners"></div>
              <div class="scan-line"></div>
            </div>
            <div class="camera-countdown camera-countdown-corner" v-if="cameraCountdown > 0">{{ cameraCountdown }}</div>
            <div class="camera-flash" v-if="cameraFlash"></div>
          </div>
          <h2 class="step-status">{{ $t('session.capturingImage') }}</h2>
          <p class="step-sub" v-if="cameraCountdown > 0">{{ $t('session.autoCaptureIn', { seconds: cameraCountdown }) }}</p>
          <span class="sim-badge">{{ $t('demoKiosk.simCapture') }}</span>
        </div>

        <!-- K7 AI CLASSIFY step (simulated) -->
        <div v-else-if="rvm.currentStep === 'classify'" key="classify" class="step-content centered">
          <div class="ai-spinner">
            <div class="ai-ring"></div>
          </div>
          <h2 class="step-status">{{ $t('session.aiProcessing') }}</h2>
          <p class="step-sub">{{ $t('session.classifying') }}</p>
          <span class="sim-badge">{{ $t('demoKiosk.simAi') }}</span>
          <div class="ai-dots">
            <span></span><span></span><span></span>
          </div>
        </div>

        <!-- K8a VALIDATE step - Valid -->
        <div v-else-if="rvm.currentStep === 'validate_ok'" key="validate_ok" class="step-content centered">
          <div class="result-icon valid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 12 10 18 20 6"/></svg>
          </div>
          <h2 class="step-status green">{{ $t('session.itemValid') }}</h2>
          <p class="step-sub">
            {{ $t('session.classified') }}:
            <svg v-if="materialIconSvg(chosenId)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(chosenId)"></svg>
            <strong>{{ itemLabel(chosenId) }}</strong>
          </p>
          <p class="step-sub" v-if="aiConfidence > 0">
            {{ $t('session.confidence', { percent: (aiConfidence * 100).toFixed(1) }) }}
            <span class="sim-badge inline">{{ $t('demoKiosk.simulated') }}</span>
          </p>
        </div>

        <!-- WEIGH step (points calculation) -->
        <div v-else-if="rvm.currentStep === 'weigh'" key="weigh" class="step-content centered">
          <div class="points-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:100%;height:100%">
              <circle cx="12" cy="8" r="5"/>
              <path d="M8.5 12.5 7 21l5-3 5 3-1.5-8.5"/>
            </svg>
          </div>
          <h2 class="step-status">{{ $t('session.weighing') }}</h2>
          <div class="points-display" v-if="itemPoints > 0">
            <span class="points-value">+{{ itemPoints }}</span>
            <span class="points-preview">{{ $t('session.pointsEarned') }}</span>
          </div>
        </div>

        <!-- K9 COMPLETE step -->
        <div v-else-if="rvm.currentStep === 'complete'" key="complete" class="step-content centered">
          <div class="result-icon valid success-pulse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 12 10 18 20 6"/></svg>
          </div>
          <h2 class="step-status green">{{ $t('session.success') }}</h2>
          <div class="result-box">
            <p>
              {{ $t('session.material') }}:
              <svg v-if="materialIconSvg(chosenId)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(chosenId)"></svg>
              {{ itemLabel(chosenId) }}
            </p>
            <p class="earned-text">{{ rvm.isGuest ? $t('session.pointsDonatedLabel') : $t('session.pointsEarned') }}: +{{ itemPoints }}</p>
            <p class="carbon-text"><PhGlobe class="carbon-icon" weight="regular" /> {{ $t('session.carbonSavedLabel') }}: {{ itemCarbon.toFixed(3) }} kg CO2</p>
          </div>
          <div class="action-buttons">
            <button class="end-btn min-h-kiosk-touch" @click="confirmEndSession">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="1.5"/></svg>
              {{ $t('session.endSession') }}
            </button>
            <button class="recycle-btn min-h-kiosk-touch" @click="recycleAgain">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
              {{ $t('session.recycleAnother') }}
            </button>
          </div>
        </div>

        <!-- K8b REJECTED step (glass: recognised, but this machine does not take it) -->
        <div v-else-if="rvm.currentStep === 'item_rejected'" key="item_rejected" class="step-content centered">
          <div class="return-anim">
            <svg class="return-item" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="8" y1="8" x2="16" y2="16"/>
              <line x1="16" y1="8" x2="8" y2="16"/>
            </svg>
            <div class="return-slot"></div>
          </div>
          <h2 class="step-status red">{{ $t('session.itemRejected') }}</h2>
          <p class="step-sub">
            {{ $t('session.classified') }}:
            <svg v-if="materialIconSvg(chosenId)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(chosenId)"></svg>
            <strong>{{ itemLabel(chosenId) }}</strong>
            <template v-if="aiConfidence > 0"> · {{ (aiConfidence * 100).toFixed(1) }}%</template>
            <span class="sim-badge inline">{{ $t('demoKiosk.simulated') }}</span>
          </p>
          <div class="result-box">
            <p>{{ $t('demoKiosk.glassRejected') }}</p>
            <p>{{ $t('session.pointsEarned') }}: +0</p>
          </div>
          <div class="action-buttons">
            <button class="end-btn min-h-kiosk-touch" @click="confirmEndSession">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="1.5"/></svg>
              {{ $t('session.endSession') }}
            </button>
            <button class="recycle-btn min-h-kiosk-touch" @click="recycleAgain">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
              {{ $t('session.retryAnother') }}
            </button>
          </div>
        </div>

      </Transition>
    </div>

    <!-- Footer step indicator -->
    <div class="rvm-footer">
      {{ $t('session.currentStep') }}: <strong class="step-label">{{ currentStepLabel }}</strong>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'
import { useRvmStore } from '@/store/rvm'
import api from '@/services/api'
import { PhGlobe } from '@phosphor-icons/vue'
import { materialIconSvg } from '@/utils/materialIcons'
import { retryOnce, newIdempotencyKey } from '@/utils/retryOnce'
import { kioskBase } from '@/utils/kioskBase'
import { DEMO_ITEMS, simulatedConfidence, resumeStep } from '@/utils/kioskDemo'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const rvm    = useRvmStore()

const machineCode = computed(() => route.params.machineCode)
const theme       = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale, t } = useI18n()

// Screen timings (ms) of the simulated machine.
const BIN_CHECK_MS      = 2000  // K4
const COUNTDOWN_TICK_MS = 1000  // K6, each 3-2-1 step
const FLASH_MS          = 400   // K6, the "photo" flash
const CLASSIFY_MS       = 1500  // K7
const VALID_SCREEN_MS   = 1500  // K8a
const POINTS_SCREEN_MS  = 1500  // "Calculating Points..."

const displayPoints   = ref(0)
const itemWeight      = ref(0)
const itemPoints      = ref(0)
const itemCarbon      = ref(0)
const aiConfidence    = ref(0)
const chosenId        = ref('')
const cameraCountdown = ref(0)
const cameraFlash     = ref(false)
const flowBusy        = ref(false)
let   unmounted       = false

const machineName = computed(() => rvm.machine?.name || rvm.session?.machine?.name || '')

function itemLabel(id) {
  return id ? t(`demoKiosk.items.${id}`) : ''
}

// Carbon saved by the valid items of this session (header badge).
const sessionCarbon = computed(() => rvm.localSummary.transactions
  .filter((txn) => txn.is_valid)
  .reduce((sum, txn) => sum + (txn.carbon_saved || 0), 0))

// Points credited by this session so far (survives a refresh, unlike the
// session object saved when the session started).
const sessionPointsSoFar = computed(() => rvm.localSummary.transactions
  .filter((txn) => txn.is_valid)
  .reduce((sum, txn) => sum + (txn.points_earned || 0), 0))

const progressWidth = computed(() => {
  const stepOrder = ['bin_check', 'choose', 'camera', 'classify', 'validate_ok', 'weigh', 'complete']
  const idx = stepOrder.indexOf(rvm.currentStep)
  return Math.max(5, idx < 0 ? 100 : ((idx + 1) / stepOrder.length) * 100)
})

const currentStepLabel = computed(() => {
  const map = {
    bin_check: t('session.stepBinCheck'), choose: t('session.stepSelection'),
    camera: t('session.stepCamera'), classify: t('session.stepClassify'),
    validate_ok: t('session.stepWeight'), weigh: t('session.stepWeight'),
    complete: t('session.stepComplete'), item_rejected: t('session.stepItemRejected'),
  }
  return map[rvm.currentStep] || rvm.currentStep
})

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

// ── K4: simulated bin check, always passes ──────────────────────────────────
const isAutoFlowRunning = ref(false)

async function autoStartFlow() {
  if (isAutoFlowRunning.value) return
  isAutoFlowRunning.value = true
  await delay(BIN_CHECK_MS)
  if (!unmounted && rvm.currentStep === 'bin_check') rvm.setStep('choose')
  isAutoFlowRunning.value = false
}

watch(() => rvm.currentStep, (step) => {
  if (step === 'bin_check') autoStartFlow()
})

// ── K5 → K6 → K7 → K8a/K8b ──────────────────────────────────────────────────
async function chooseItem(item) {
  if (flowBusy.value || rvm.currentStep !== 'choose') return
  flowBusy.value = true
  try {
    chosenId.value     = item.id
    itemWeight.value   = 0
    itemPoints.value   = 0
    itemCarbon.value   = 0
    aiConfidence.value = simulatedConfidence()
    rvm.setSelectedMaterial(item.id)

    // K6: simulated capture, 3-2-1 then a flash.
    rvm.setStep('camera')
    for (let i = 3; i >= 1; i--) {
      cameraCountdown.value = i
      await delay(COUNTDOWN_TICK_MS)
      if (unmounted) return
    }
    cameraCountdown.value = 0
    cameraFlash.value = true
    await delay(FLASH_MS)
    cameraFlash.value = false
    if (unmounted) return

    // K7: simulated AI.
    rvm.setStep('classify')
    await delay(CLASSIFY_MS)
    if (unmounted) return

    if (item.accepted) await awardPoints(item.id)
    else await rejectItem(item.id)
  } finally {
    flowBusy.value = false
  }
}

// K8a → weigh → K9: credits the item's points for real.
async function awardPoints(id) {
  rvm.setStep('validate_ok')
  await delay(VALID_SCREEN_MS)
  if (unmounted) return
  rvm.setStep('weigh')

  if (rvm.isGuest) await saveGuestItem(id)
  else await saveUserItem(id)
  if (unmounted) return

  rvm.recordLocalTransaction({ material: id, weight: itemWeight.value, points: itemPoints.value, isValid: true, carbon: itemCarbon.value })
  rvm.setStep('complete')
}

// Range must match BackEnd/app/Http/Controllers/TransactionController.php's
// POINTS_MIN/POINTS_MAX — only used if the save call fails.
function randomPointsFallback() {
  return Math.floor(Math.random() * (20 - 15 + 1)) + 15
}

// Logged in: weigh (server decides the points) then complete (saves them).
async function saveUserItem(id) {
  try {
    const weighRes = await rvm.processStep('weigh', { material_selected: id, ai_detected_type: id })
    itemWeight.value = weighRes.weight_grams || 0
    itemPoints.value = weighRes.points_earned || randomPointsFallback()
  } catch {
    itemPoints.value = randomPointsFallback()
  }
  await delay(POINTS_SCREEN_MS)
  if (unmounted) return

  try {
    // One key per item: a retried complete returns the first result (U04).
    const idempotencyKey = newIdempotencyKey()
    const completeRes = await retryOnce(() => rvm.processStep('complete', {
      material_selected: id,
      ai_detected_type: id,
      ai_confidence: aiConfidence.value,
      idempotency_key: idempotencyKey,
    }))
    itemPoints.value    = completeRes.points_earned || itemPoints.value
    itemCarbon.value    = completeRes.carbon_saved ?? 0
    displayPoints.value = completeRes.total_points || (displayPoints.value + itemPoints.value)
  } catch {
    displayPoints.value += itemPoints.value
  }
}

// Guest: one call saves the item (under the shared guest account) and returns
// its points and carbon. Without a real backend session it stays local only.
async function saveGuestItem(id) {
  let saved = false
  if (rvm.guestSessionIsReal && rvm.session?.session_code) {
    try {
      const res = await api.post('/hardware/session/complete', {
        session_code:     rvm.session.session_code,
        ai_detected_type: id,
        ai_confidence:    aiConfidence.value,
      })
      if (res.data?.success) {
        itemWeight.value = res.data.weight_grams ?? 0
        itemPoints.value = res.data.points_earned ?? 0
        itemCarbon.value = res.data.carbon_saved ?? 0
        saved = true
      }
    } catch { /* local fallback below */ }
  }
  if (!saved) {
    itemPoints.value = randomPointsFallback()
    itemCarbon.value = 0
  }
  await delay(POINTS_SCREEN_MS)
  displayPoints.value += itemPoints.value
}

// K8b: glass is recognised but not taken by this machine. 0 points.
async function rejectItem(id) {
  itemPoints.value = 0
  itemCarbon.value = 0
  if (rvm.isGuest) {
    if (rvm.guestSessionIsReal && rvm.session?.session_code) {
      api.post('/hardware/session/complete', {
        session_code:      rvm.session.session_code,
        ai_detected_type:  id,
        ai_confidence:     aiConfidence.value,
        material_selected: 'reject',
      }).catch(() => {})
    }
  } else {
    api.post('/transactions/reject', {
      session_code:      rvm.session?.session_code,
      material_selected: 'reject',
      ai_detected_type:  id,
      ai_confidence:     aiConfidence.value,
    }).catch(() => {})
  }
  rvm.recordLocalTransaction({ material: id, weight: 0, points: 0, isValid: false, deducted: 0, carbon: 0 })
  rvm.setStep('item_rejected')
}

function recycleAgain() {
  rvm.resetTransaction()
  chosenId.value     = ''
  itemPoints.value   = 0
  itemCarbon.value   = 0
  aiConfidence.value = 0
  rvm.setStep('choose')
}

async function confirmEndSession() {
  const code = rvm.guestMachineCode || machineCode.value
  if (rvm.isGuest) {
    await rvm.endSession()
    router.push(`${kioskBase(route)}/${code}/summary`)
    return
  }
  try {
    const res = await rvm.endSession()
    const finalPoints = res?.session?.current_points ?? res?.session?.end_points ?? displayPoints.value
    if (auth.user) auth.updatePoints(finalPoints)
  } catch {
    if (auth.user) auth.updatePoints(displayPoints.value)
  }
  router.push(`${kioskBase(route)}/${code}/summary`)
}

onBeforeUnmount(() => {
  unmounted = true
})

onMounted(() => {
  if (!rvm.session) rvm.restoreKioskSession(machineCode.value)

  if (!rvm.session) {
    router.replace({ name: 'demo-kiosk-qr', params: { machineCode: machineCode.value } })
    return
  }

  if (rvm.currentStep === 'summary') {
    router.replace({ name: 'demo-kiosk-summary', params: { machineCode: machineCode.value } })
    return
  }

  const startPoints = rvm.session?.start_points ?? rvm.session?.current_points ?? 0
  if (!rvm.localSummary.start_points) rvm.localSummary.start_points = startPoints
  displayPoints.value = startPoints + sessionPointsSoFar.value

  // Restore the last item's result for the K9 / K8b screens after a refresh.
  const last = rvm.localSummary.transactions.at(-1)
  if (last) {
    chosenId.value   = last.material || rvm.selectedMaterial || ''
    itemPoints.value = last.points_earned ?? 0
    itemCarbon.value = last.carbon_saved ?? 0
  }

  // A refresh stops the simulated chain: back to the item choice. K8a
  // (validate_ok) has not saved anything yet either, so it goes back too.
  const step = resumeStep(rvm.currentStep)
  rvm.setStep(step === 'validate_ok' ? 'choose' : step)

  if (rvm.currentStep === 'bin_check') autoStartFlow()
})
</script>

<style scoped>
.session-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg-primary);
}

.rvm-header {
  background: var(--grad-header);
  padding: 20px 20px 16px;
  position: relative;
}
.header-controls {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  gap: 6px;
}
.ctrl-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 4px 10px;
  border-radius: 16px;
  cursor: pointer;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
}
.ctrl-icon { width: 14px; height: 14px; display: block; }
.rvm-title {
  color: white;
  font-size: 22px;
  font-weight: 800;
  text-align: center;
  margin-bottom: 4px;
}
.welcome-text {
  color: rgba(255,255,255,0.85);
  text-align: center;
  font-size: 13px;
  margin-bottom: 12px;
}
.guest-label { color: #4ade80; }
.header-badges {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}
.badge {
  background: rgba(255,255,255,0.22);
  border-radius: 10px;
  padding: 8px 14px;
  flex: 1;
}
.badge-label { display: block; color: rgba(255,255,255,0.8); font-size: 11px; margin-bottom: 3px; }
.badge-value { color: white; font-size: 20px; font-weight: 700; }

.progress-bar {
  height: 4px;
  background: var(--bg-card);
}
.progress-fill {
  height: 100%;
  width: 100%;
  transform-origin: left;
  background: var(--grad-header);
  transition: transform 0.6s ease;
}

.rvm-body {
  flex: 1;
  background: var(--bg-secondary);
  padding: 24px 20px;
  overflow-y: auto;
}

.step-content { min-height: 320px; }
.step-content.centered {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

/* SIMULATED labels (demo only) */
.sim-badge {
  display: inline-block;
  margin-top: 6px;
  padding: 3px 10px;
  border-radius: 6px;
  background: #fef3c7;
  border: 1px solid #f59e0b;
  color: #92400e;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.3px;
}
.sim-badge.inline { margin: 0 0 0 6px; padding: 1px 7px; font-size: 11px; vertical-align: 1px; }

/* K5 item choice (demo only) */
.demo-tiles {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  width: 100%;
  max-width: 720px;
  margin-top: 16px;
}
.demo-tile {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 18px 8px;
  border-radius: var(--radius);
  border: 2px solid rgba(78, 158, 245, 0.4);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s, transform 0.15s;
}
.demo-tile:hover:not(:disabled) { border-color: #4e9ef5; transform: translateY(-2px); }
.demo-tile:disabled { opacity: 0.6; cursor: not-allowed; }
.demo-tile-reject { border-color: rgba(239, 68, 68, 0.45); }
.demo-tile-reject:hover:not(:disabled) { border-color: var(--accent-red); }
.demo-tile-icon { width: 44px; height: 44px; }
.demo-tile-name { font-size: 14px; }
@media (max-width: 520px) {
  .demo-tiles { grid-template-columns: 1fr 1fr; }
}

/* Camera */
.camera-container {
  position: relative;
  width: 100%;
  max-width: 340px;
  border-radius: 12px;
  overflow: hidden;
  background: #000;
  margin-bottom: 16px;
  aspect-ratio: 4/3;
}
.camera-sim {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0b1020;
}
.camera-sim-icon { width: 38%; height: 38%; color: rgba(255,255,255,0.85); }
/* the item icon fills the centre, so the 3-2-1 sits in the corner (mockup K6) */
.camera-countdown.camera-countdown-corner { top: auto; left: auto; right: 22px; bottom: 14px; transform: none; font-size: 44px; }
.camera-flash {
  position: absolute;
  inset: 0;
  background: white;
  animation: camera-flash 0.4s ease-out forwards;
  pointer-events: none;
}
@keyframes camera-flash {
  0%   { opacity: 0; }
  20%  { opacity: 1; }
  100% { opacity: 0; }
}
.scan-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
}
.scan-corners {
  position: absolute;
  inset: 12px;
  border: 2px solid rgba(78, 158, 245, 0.7);
  border-radius: 8px;
}
.scan-corners::before,
.scan-corners::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  border-color: #4e9ef5;
  border-style: solid;
}
.scan-corners::before { top: -2px; left: -2px; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
.scan-corners::after  { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
.scan-line {
  position: absolute;
  inset: 12px;
  background-image: linear-gradient(90deg, transparent, #4e9ef5, transparent);
  background-repeat: no-repeat;
  background-size: 100% 2px;
  animation: scan-move 2s ease-in-out infinite;
}
@keyframes scan-move {
  0%   { background-position: 0 0%; }
  50%  { background-position: 0 100%; }
  100% { background-position: 0 0%; }
}
.camera-countdown {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 52px;
  font-weight: 800;
  color: white;
  text-shadow: 0 2px 12px rgba(0,0,0,0.7);
  pointer-events: none;
}

/* Spinner */
.spinner-lg {
  width: 60px; height: 60px;
  border: 4px solid var(--border);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.7; transform:scale(0.95); } }

/* AI Spinner */
.ai-ring {
  width: 70px; height: 70px;
  border: 3px solid transparent;
  border-top-color: #a855f7;
  border-right-color: #4e6ef2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

.ai-dots { display: flex; gap: 8px; margin-top: 12px; }
.ai-dots span {
  width: 8px; height: 8px;
  background: var(--accent-purple);
  border-radius: 50%;
  animation: bounce 1s infinite;
}
.ai-dots span:nth-child(2) { animation-delay: 0.2s; }
.ai-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }

/* Result icons */
.result-icon {
  width: 80px; height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 16px;
}
.result-icon svg { width: 36px; height: 36px; }
.result-icon.valid   { background: #22c55e; color: white; }

/* Item being returned to the user (rejected item) */
.return-anim {
  position: relative;
  width: 100px;
  height: 90px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  margin-bottom: 16px;
}
.return-slot {
  width: 70px;
  height: 8px;
  background: var(--border);
  border-radius: 4px;
}
.return-item {
  position: absolute;
  bottom: 8px;
  width: 40px;
  height: 40px;
  color: var(--accent-red);
  animation: returnItemDrop 1.8s ease-in-out infinite;
}
@keyframes returnItemDrop {
  0%   { transform: translateY(-30px); opacity: 0; }
  25%  { transform: translateY(0);     opacity: 1; }
  70%  { transform: translateY(0);     opacity: 1; }
  100% { transform: translateY(24px);  opacity: 0; }
}

.success-pulse { position: relative; }
.success-pulse::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 2px solid rgba(34,197,94,0.6);
  animation: success-pulse 2s ease-out 3;
}
@keyframes success-pulse {
  0%   { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}

.step-status { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
.step-status.green { color: var(--accent-green); }
.step-status.red   { color: var(--accent-red); }
.step-sub { color: var(--text-secondary); font-size: 14px; margin-bottom: 8px; }

.result-box {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 24px;
  margin: 12px 0;
  text-align: center;
  min-width: 240px;
}
.result-box p { color: var(--text-secondary); font-size: 14px; margin: 4px 0; }
.earned-text    { color: var(--accent-green) !important; font-weight: 700; font-size: 16px !important; }
.carbon-text    { color: var(--accent-blue) !important; font-weight: 700; font-size: 14px !important; }
.carbon-icon    { vertical-align: -2px; margin-right: 2px; }

.points-icon { width: 60px; height: 60px; color: var(--accent-green); margin-bottom: 16px; animation: pulse 1.2s ease-in-out infinite; }

.points-display { margin-top: 12px; text-align: center; }
.points-value { display: block; font-size: 40px; font-weight: 800; color: var(--accent-green); }
.points-preview { color: var(--text-secondary); font-size: 16px; font-weight: 600; }

.mat-inline-icon { width: 16px; height: 16px; vertical-align: -3px; margin: 0 2px; }

.action-buttons { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; justify-content: center; }
.action-buttons button:disabled { opacity: 0.6; cursor: not-allowed; }

.end-btn {
  padding: 13px 24px;
  background: var(--accent-red);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 140px;
}
.recycle-btn {
  padding: 13px 24px;
  background: var(--accent-green);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 180px;
}
.btn-icon {
  width: 15px;
  height: 15px;
  vertical-align: -2px;
  margin-right: 4px;
}
.rvm-footer {
  background: var(--bg-card);
  border-top: 1px solid var(--border);
  padding: 12px 20px;
  text-align: center;
  font-size: 13px;
  color: var(--text-muted);
}
.step-label { color: var(--accent-blue); }

/* Step transitions */
.step-transition-enter-active { transition: all 0.35s ease; }
.step-transition-leave-active { transition: all 0.2s ease; }
.step-transition-enter-from   { opacity: 0; transform: translateX(20px); }
.step-transition-leave-to     { opacity: 0; transform: translateX(-20px); }

@keyframes spin { to { transform: rotate(360deg); } }

/* Kiosk-sized screens in either orientation: center each step top-to-bottom
   (same as RvmSessionView.vue). Phones (<700px wide) keep the top-aligned layout. */
@media (min-width: 700px) {
  .rvm-body {
    display: flex;
    flex-direction: column;
    justify-content: safe center;
  }
  .step-content { min-height: 0; flex-shrink: 0; }
}

/* Compact layout for small kiosk touchscreens (e.g. 1024x600) */
@media (max-height: 650px) {
  .rvm-header { padding: 12px 16px 10px; }
  .rvm-title { font-size: 18px; margin-bottom: 2px; }
  .welcome-text { font-size: 12px; margin-bottom: 8px; }
  .badge { padding: 6px 12px; }
  .badge-value { font-size: 17px; }

  .rvm-body { padding: 14px 20px; }
  .step-content { min-height: 220px; }

  .spinner-lg { width: 44px; height: 44px; margin-bottom: 12px; }
  .ai-ring { width: 52px; height: 52px; margin-bottom: 12px; }
  .result-icon { width: 60px; height: 60px; margin-bottom: 10px; }
  .result-icon svg { width: 28px; height: 28px; }
  .points-icon { width: 44px; height: 44px; margin-bottom: 10px; }
  .points-value { font-size: 30px; }
  .camera-container { max-width: 260px; }
  .demo-tile { padding: 12px 6px; }
  .demo-tile-icon { width: 34px; height: 34px; }

  .step-status { font-size: 17px; }
  .result-box { padding: 10px 18px; margin: 8px 0; }

  .rvm-footer { padding: 8px 16px; font-size: 12px; }
}

/* Portrait kiosk panel (1080x1920), same sizes as RvmSessionView.vue. */
@media (orientation: portrait) and (min-width: 700px) {
  .rvm-header { padding: 32px 32px 24px; }
  .rvm-title { font-size: 30px; margin-bottom: 8px; }
  .welcome-text { font-size: 17px; margin-bottom: 18px; }
  .badge { padding: 14px 20px; }
  .badge-label { font-size: 14px; }
  .badge-value { font-size: 26px; }

  .rvm-body { padding: 32px 40px; }

  .step-status { font-size: 28px; }
  .step-sub { font-size: 18px; }
  .sim-badge { font-size: 15px; }

  .demo-tiles { grid-template-columns: 1fr 1fr; gap: 18px; }
  .demo-tile { padding: 28px 12px; font-size: 18px; }
  .demo-tile-icon { width: 64px; height: 64px; }
  .demo-tile-name { font-size: 18px; }

  .camera-container { max-width: 560px; }
  .camera-countdown { font-size: 76px; }
  .camera-countdown.camera-countdown-corner { font-size: 64px; right: 30px; bottom: 20px; }
  .spinner-lg { width: 84px; height: 84px; }
  .ai-ring { width: 96px; height: 96px; }
  .result-icon { width: 110px; height: 110px; }
  .result-icon svg { width: 50px; height: 50px; }
  .points-icon { width: 84px; height: 84px; }
  .points-value { font-size: 56px; }
  .points-preview { font-size: 20px; }
  .return-anim { width: 140px; height: 126px; }
  .return-item { width: 56px; height: 56px; }
  .return-slot { width: 98px; height: 11px; }

  .result-box { padding: 22px 32px; min-width: 340px; }
  .result-box p { font-size: 18px; }
  .earned-text { font-size: 20px !important; }
  .carbon-text { font-size: 18px !important; }

  .end-btn, .recycle-btn { padding: 18px 36px; font-size: 18px; }
  .action-buttons { gap: 18px; margin-top: 24px; }

  .rvm-footer { padding: 18px 32px; font-size: 16px; }
}
</style>
