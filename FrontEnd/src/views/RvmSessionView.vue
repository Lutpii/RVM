<template>
  <div class="session-page">
    <!-- RVM Header -->
    <div class="rvm-header">
      <div class="header-controls">
        <button class="ctrl-btn" @click="toggleTheme()">
          <svg v-if="theme === 'dark'" class="ctrl-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
          <svg v-else class="ctrl-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
        </button>
        <button class="ctrl-btn" @click="toggleLang">{{ locale === 'en' ? 'MY' : 'EN' }}</button>
      </div>
      <h1 class="rvm-title">{{ $t('app.name') }}</h1>
      <p class="welcome-text" v-if="auth.user">Welcome, {{ auth.user.name }}</p>
      <p class="welcome-text" v-else-if="rvm.session?.user_name && !rvm.isGuest">Welcome, {{ rvm.session.user_name }}</p>
      <p class="welcome-text guest-label" v-else-if="rvm.isGuest">Guest — Points will be donated</p>
      <div class="header-badges">
        <div class="badge points-badge">
          <span class="badge-label">{{ rvm.isGuest ? 'Donating' : $t('session.totalPoints') }}</span>
          <span class="badge-value points-animate">{{ displayPoints }}</span>
        </div>
        <div class="badge status-badge">
          <span class="badge-label">{{ $t('session.status') }}</span>
          <span :class="['badge-value', 'status-' + statusClass]">{{ statusText }}</span>
        </div>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: progressWidth + '%' }"></div>
    </div>

    <!-- Main Step Content -->
    <div class="rvm-body">
      <Transition name="step-transition" mode="out-in">

        <!-- BIN CHECK step -->
        <div v-if="rvm.currentStep === 'bin_check'" key="bin_check" class="step-content centered">
          <div class="spinner-lg"></div>
          <h2 class="step-status">{{ $t('session.checkingBin') }}</h2>
        </div>

        <!-- LID OPENING step -->
        <div v-else-if="rvm.currentStep === 'lid'" key="lid" class="step-content centered">
          <div class="lid-animation">
            <div class="lid-box">
              <div :class="['lid-door', { open: lidOpen }]"></div>
            </div>
          </div>
          <h2 class="step-status">{{ $t('session.lidOpening') }}</h2>
          <p class="step-sub">{{ $t('session.pleaseWait') }}</p>
        </div>

        <!-- INSERT step -->
        <div v-else-if="rvm.currentStep === 'insert'" key="insert" class="step-content centered">
          <div class="insert-icon">
            <div class="box-3d">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 8.5 12 4l9 4.5-9 4.5-9-4.5Z"/>
                <path d="M3 8.5v7L12 20l9-4.5v-7"/>
                <path d="M12 13v7"/>
              </svg>
            </div>
          </div>
          <h2 class="step-status">{{ $t('session.readyAccept') }}</h2>
          <p class="step-sub">{{ $t('session.insertItem') }}</p>
          <button class="simulate-btn" @click="simulateInsert">
            {{ $t('session.simulateBtn') }}
          </button>
        </div>

        <!-- CONVEYOR step -->
        <div v-else-if="rvm.currentStep === 'conveyor'" key="conveyor" class="step-content centered">
          <div class="conveyor-wrap">
            <div class="conveyor-track">
              <div class="conveyor-item" :style="{ left: conveyorPos + '%' }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="conveyor-item-icon">
                  <path d="M3 8.5 12 4l9 4.5-9 4.5-9-4.5Z"/>
                  <path d="M3 8.5v7L12 20l9-4.5v-7"/>
                  <path d="M12 13v7"/>
                </svg>
              </div>
              <div class="conveyor-belt"></div>
            </div>
          </div>
          <h2 class="step-status">{{ $t('session.conveyorRunning') }}</h2>
          <p class="step-sub">{{ $t('session.movingItem') }}</p>
        </div>

        <!-- CAMERA step -->
        <div v-else-if="rvm.currentStep === 'camera'" key="camera" class="step-content centered">
          <!-- Choose mode — disabled: the kiosk only has one capture option (the
               real hardware camera), so we skip straight to it after the conveyor
               step instead of making the user tap through a menu of one.
          <template v-if="cameraMode === 'choose'">
            <h2 class="step-status">{{ $t('session.scanOrUpload') }}</h2>
            <p class="step-sub">{{ $t('session.chooseCapture') }}</p>
            <div class="capture-options">
              <button class="capture-btn" @click="startCameraMode">
                <span class="capture-icon">📷</span>
                <span>{{ $t('session.useCamera') }}</span>
              </button>
              <label class="capture-btn">
                <span class="capture-icon">📁</span>
                <span>{{ $t('session.uploadImage') }}</span>
                <input ref="fileInputRef" type="file" accept="image/jpeg,image/png,image/*" @change="handleFileUpload" style="display:none" />
              </label>
            </div>
          </template>
          -->

          <!-- Live camera + countdown -->
          <template v-if="cameraMode === 'camera'">
            <div class="camera-container">
              <!-- The Pi's CSI camera can't reach the browser via getUserMedia, so the
                   "live" preview here is an MJPEG stream from the AI service instead
                   (see /ai-stream in nginx-rvm.conf -> app.py's /stream). Stopped once
                   the countdown hits 0 so it isn't fighting /capture for the camera —
                   no placeholder/icon shown after that, just the plain background. -->
              <img
                v-if="isKioskRoute && cameraCountdown > 0"
                :src="cameraStreamUrl"
                class="camera-video"
                alt=""
              />
              <video v-else-if="!isKioskRoute" ref="videoRef" autoplay playsinline muted class="camera-video"></video>
              <canvas ref="canvasRef" style="display:none"></canvas>
              <div class="scan-overlay">
                <div class="scan-corners"></div>
                <div class="scan-line"></div>
              </div>
              <div class="camera-countdown" v-if="cameraCountdown > 0">{{ cameraCountdown }}</div>
            </div>
            <h2 class="step-status">{{ $t('session.capturingImage') }}</h2>
            <p class="step-sub">{{ cameraCountdown > 0 ? `Auto-capture in ${cameraCountdown}s` : 'Uploading...' }}</p>
          </template>

          <!-- File upload preview -->
          <template v-else-if="cameraMode === 'upload'">
            <canvas ref="canvasRef" style="display:none"></canvas>
            <div class="camera-container">
              <img v-if="capturedImageDataUrl" :src="capturedImageDataUrl" class="camera-video" alt="preview" style="object-fit:contain;background:#111" />
            </div>
            <h2 class="step-status">Uploading Image...</h2>
            <p class="step-sub">Processing your file</p>
          </template>
        </div>

        <!-- AI CLASSIFY step -->
        <div v-else-if="rvm.currentStep === 'classify'" key="classify" class="step-content centered">
          <div class="ai-spinner">
            <div class="ai-ring"></div>
          </div>
          <h2 class="step-status">{{ $t('session.aiProcessing') }}</h2>
          <p class="step-sub">{{ $t('session.classifying') }}</p>
          <div class="ai-dots">
            <span></span><span></span><span></span>
          </div>
        </div>

        <!-- VALIDATE step - Valid -->
        <div v-else-if="rvm.currentStep === 'validate_ok'" key="validate_ok" class="step-content centered">
          <div class="result-icon valid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 12 10 18 20 6"/></svg>
          </div>
          <h2 class="step-status green">{{ $t('session.itemValid') }}</h2>
          <div v-if="annotatedImageDataUrl" class="bbox-preview">
            <img :src="annotatedImageDataUrl" class="bbox-img" alt="AI detection" />
          </div>
          <p class="step-sub">{{ $t('session.selected') }}: {{ rvm.selectedMaterial }}</p>
          <p class="step-sub">
            {{ $t('session.classified') }}:
            <svg v-if="materialIconSvg(aiDetected)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(aiDetected)"></svg>
            {{ aiDetected }}
          </p>
          <p class="step-sub" v-if="aiConfidence > 0">Confidence: {{ (aiConfidence * 100).toFixed(1) }}%</p>
        </div>

        <!-- VALIDATE step - Invalid -->
        <div v-else-if="rvm.currentStep === 'validate_fail'" key="validate_fail" class="step-content centered">
          <div class="result-icon invalid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
          </div>
          <h2 class="step-status red">{{ $t('session.itemInvalid') }}</h2>
          <div v-if="annotatedImageDataUrl" class="bbox-preview">
            <img :src="annotatedImageDataUrl" class="bbox-img" alt="AI detection" />
          </div>
          <div class="result-box">
            <p>{{ $t('session.selected') }}: <strong>{{ rvm.selectedMaterial }}</strong></p>
            <p>
              AI Detected:
              <svg v-if="materialIconSvg(aiDetected)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(aiDetected)"></svg>
              <strong>{{ aiDetected }}</strong>
            </p>
            <p v-if="aiConfidence > 0">Confidence: {{ (aiConfidence * 100).toFixed(1) }}%</p>
            <p class="deduction-text">-10 points will be deducted</p>
          </div>
        </div>

        <!-- WEIGH step (points calculation — no physical scale on this hardware) -->
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

        <!-- COMPLETE step -->
        <div v-else-if="rvm.currentStep === 'complete'" key="complete" class="step-content centered">
          <div class="result-icon valid success-pulse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 12 10 18 20 6"/></svg>
          </div>
          <h2 class="step-status green">{{ $t('session.success') }}</h2>
          <div class="result-box">
            <p>
              {{ $t('session.material') }}:
              <svg v-if="materialIconSvg(rvm.selectedMaterial)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(rvm.selectedMaterial)"></svg>
              {{ rvm.selectedMaterial }}
            </p>
            <p class="earned-text">{{ rvm.isGuest ? 'Points Donated' : $t('session.pointsEarned') }}: +{{ itemPoints }}</p>
          </div>
          <div class="action-buttons">
            <button class="end-btn" @click="confirmEndSession">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="1.5"/></svg>
              {{ $t('session.endSession') }}
            </button>
            <button class="recycle-btn" @click="rvm.resetTransaction()">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
              {{ $t('session.recycleAnother') }}
            </button>
          </div>
        </div>

        <!-- UNKNOWN step (AI couldn't recognize the material) -->
        <div v-else-if="rvm.currentStep === 'item_unknown'" key="item_unknown" class="step-content centered">
          <div class="return-anim">
            <svg class="return-item" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 4"/>
              <line x1="12" y1="17" x2="12" y2="17.01"/>
            </svg>
            <div class="return-slot"></div>
          </div>
          <h2 class="step-status red">Item Not Recognized</h2>
          <div v-if="annotatedImageDataUrl" class="bbox-preview">
            <img :src="annotatedImageDataUrl" class="bbox-img" alt="AI detection" />
          </div>
          <div class="result-box">
            <p>{{ $t('session.pointsEarned') }}: +0</p>
          </div>
          <div class="action-buttons">
            <button class="end-btn" @click="confirmEndSession">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="1.5"/></svg>
              {{ $t('session.endSession') }}
            </button>
            <button class="recycle-btn" @click="rvm.resetTransaction()">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
              Retry Another Item
            </button>
          </div>
        </div>

        <!-- REJECTED step -->
        <div v-else-if="rvm.currentStep === 'rejected'" key="rejected" class="step-content centered">
          <div class="result-icon invalid pulse-red">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
          </div>
          <h2 class="step-status red">{{ $t('session.itemRejected') }}</h2>
          <div class="result-box">
            <p>{{ $t('session.selected') }}: {{ rvm.selectedMaterial }}</p>
            <p>
              {{ $t('session.aiDetected') }}:
              <svg v-if="materialIconSvg(aiDetected)" class="mat-inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(aiDetected)"></svg>
              {{ aiDetected }}
            </p>
            <p class="deduction-text">{{ $t('session.pointsDeducted') }}: -{{ deductedPoints }}</p>
          </div>
          <p class="reject-hint">Please select the correct material type</p>
          <button class="recycle-btn" @click="rvm.resetTransaction()">{{ $t('session.tryAgain') }}</button>
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
import api, { setKioskToken } from '@/services/api'

const router   = useRouter()
const route    = useRoute()
const auth     = useAuthStore()
const rvm      = useRvmStore()

const isKioskRoute    = computed(() => route.path.startsWith('/kiosk/'))
const kioskMachineCode = computed(() => route.params.machineCode)
const theme    = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale } = useI18n()

const displayPoints  = ref(auth.user?.total_points ?? rvm.session?.current_points ?? 0)
// Seed start_points for local summary tracking
rvm.localSummary.start_points = auth.user?.total_points ?? rvm.session?.start_points ?? 0
const lidOpen        = ref(false)
const conveyorPos    = ref(0)
const itemWeight     = ref(0)
const itemPoints     = ref(0)
const aiDetected     = ref('')
const aiConfidence   = ref(0)
const deductedPoints = ref(10)

// Small per-class icons shown next to a material name. Fixed lookup only —
// never interpolates the detected string into markup — so it's safe to render via v-html.
const MATERIAL_ICON_PATHS = {
  aluminum: '<path d="M7 4h10l-1 2.5v11A1.5 1.5 0 0 1 14.5 19h-5A1.5 1.5 0 0 1 8 17.5v-11L7 4Z"/><path d="M8 4V3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1"/><path d="M8 9h8"/>',
  plastic:  '<path d="M10 2h4v3l1.5 2v13.5A1.5 1.5 0 0 1 14 22h-4a1.5 1.5 0 0 1-1.5-1.5V7L10 5V2Z"/><path d="M8.5 11h7"/>',
  glass:    '<path d="M10.5 2h3v5l2 3v10a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 8.5 20V10l2-3V2Z" fill="currentColor" fill-opacity="0.15"/><path d="M9 15h6"/>',
  paper:    '<path d="M6 3h9l3 3v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M15 3v3h3"/><path d="M8 12h8M8 16h8M8 8h4"/>',
}
function materialIconSvg(material) {
  return MATERIAL_ICON_PATHS[(material || '').toLowerCase()] || ''
}

// Camera
const videoRef              = ref(null)
const canvasRef             = ref(null)
const fileInputRef          = ref(null)
const cameraCountdown       = ref(3)
const cameraStreamUrl       = ref('')
const cameraMode            = ref('choose') // 'choose' | 'camera' | 'upload'
const capturedImageDataUrl  = ref(null)
const annotatedImageDataUrl = ref(null)
let   cameraStream          = null
let   cameraResolve         = null // resolves the promise waiting for user capture

async function openCamera() {
  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } }
    })
    if (videoRef.value) videoRef.value.srcObject = cameraStream
  } catch {
    cameraStream = null
  }
}

function stopCamera() {
  if (cameraStream) {
    cameraStream.getTracks().forEach(t => t.stop())
    cameraStream = null
  }
  if (videoRef.value) videoRef.value.srcObject = null
}

async function startCameraMode() {
  cameraMode.value = 'camera'
  cameraCountdown.value = 3

  // The Pi's own CSI camera (Picamera2) isn't reachable via the browser's
  // getUserMedia() — it's driven by the AI service's /capture endpoint instead
  // (see BackEnd/ai_service/app.py). On a kiosk route, skip the browser camera
  // entirely and let the backend grab the real hardware frame.
  if (isKioskRoute.value) {
    cameraStreamUrl.value = '/ai-stream?t=' + Date.now()
    await delay(1040)
    for (let i = 3; i >= 1; i--) {
      cameraCountdown.value = i
      await delay(1300)
    }
    // Explicitly blank the <img> src WHILE it's still mounted, to force the
    // browser to actually abort the MJPEG connection — swapping it out via
    // v-if alone isn't reliable, and a lingering stream fights /capture for
    // the camera lock.
    cameraStreamUrl.value = ''
    await delay(300)
    cameraCountdown.value = 0
    const path = await captureFromHardware()
    if (cameraResolve) { cameraResolve(path); cameraResolve = null }
    return
  }

  await openCamera()
  await delay(1040)
  for (let i = 3; i >= 1; i--) {
    cameraCountdown.value = i
    await delay(1300)
  }
  cameraCountdown.value = 0
  const path = await captureFromCamera()
  stopCamera()
  if (cameraResolve) { cameraResolve(path); cameraResolve = null }
}

async function captureFromHardware() {
  try {
    const endpoint = rvm.isGuest ? '/hardware/capture' : '/transactions/capture-image'
    const form = new FormData()
    if (!rvm.isGuest) form.append('session_code', rvm.session?.session_code || '')
    const res = await api.post(endpoint, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return res.data.image_path || null
  } catch {
    return null
  }
}

async function captureFromCamera() {
  const video  = videoRef.value
  const canvas = canvasRef.value
  if (video && canvas && video.readyState >= 2) {
    canvas.width  = video.videoWidth  || 640
    canvas.height = video.videoHeight || 480
    canvas.getContext('2d').drawImage(video, 0, 0)
    capturedImageDataUrl.value = canvas.toDataURL('image/jpeg', 0.88)
  }
  if (rvm.isGuest) return null
  return new Promise((resolve) => {
    if (!canvas || !canvas.toBlob) { resolve(null); return }
    canvas.toBlob(async (blob) => {
      if (!blob) { resolve(null); return }
      try {
        const form = new FormData()
        form.append('session_code', rvm.session?.session_code || '')
        form.append('image', blob, 'capture.jpg')
        const res = await api.post('/transactions/capture-image', form, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        resolve(res.data.image_path || null)
      } catch {
        resolve(null)
      }
    }, 'image/jpeg', 0.88)
  })
}

async function handleFileUpload(event) {
  const file = event.target.files?.[0]
  if (!file) return
  cameraMode.value = 'upload'
  // Show preview
  const reader = new FileReader()
  reader.onload = (e) => { capturedImageDataUrl.value = e.target.result }
  reader.readAsDataURL(file)
  // Upload to server
  let path = null
  if (!rvm.isGuest) {
    try {
      const form = new FormData()
      form.append('session_code', rvm.session?.session_code || '')
      form.append('image', file, file.name)
      const res = await api.post('/transactions/capture-image', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      path = res.data.image_path || null
    } catch { path = null }
  }
  if (cameraResolve) { cameraResolve(path); cameraResolve = null }
}

onBeforeUnmount(() => stopCamera())

const statusText = computed(() => {
  if (['insert'].includes(rvm.currentStep)) return 'Ready'
  return 'Processing'
})

const statusClass = computed(() => {
  return ['complete', 'validate_ok'].includes(rvm.currentStep) ? 'green' : 'blue'
})

const progressWidth = computed(() => {
  const stepOrder = ['bin_check','lid','insert','conveyor','camera','classify','validate_ok','weigh','complete']
  const idx = stepOrder.indexOf(rvm.currentStep)
  return Math.max(5, idx < 0 ? 100 : ((idx + 1) / stepOrder.length) * 100)
})

const currentStepLabel = computed(() => {
  const map = {
    selection: 'Selection', bin_check: 'Bin Check', lid: 'Lid Open',
    insert: 'Insert', conveyor: 'Conveyor', camera: 'Camera',
    classify: 'Classify', validate_ok: 'Weight', validate_fail: 'Validate',
    weigh: 'Weight', complete: 'Complete', rejected: 'Rejected', item_unknown: 'Not Recognized',
  }
  return map[rvm.currentStep] || rvm.currentStep
})

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

const isAutoFlowRunning = ref(false)

async function autoStartFlow() {
  if (isAutoFlowRunning.value) return
  isAutoFlowRunning.value = true

  await delay(1950)

  rvm.setStep('lid')
  lidOpen.value = false
  await delay(650)
  lidOpen.value = true
  await delay(2600)
  rvm.setStep('insert')

  isAutoFlowRunning.value = false
}

watch(() => rvm.currentStep, (step) => {
  if (step === 'bin_check') autoStartFlow()
})

function drawBoundingBoxes(imageDataUrl, predictions) {
  return new Promise((resolve) => {
    const offscreen = document.createElement('canvas')
    const img = new Image()
    img.onload = () => {
      offscreen.width  = img.width
      offscreen.height = img.height
      const ctx = offscreen.getContext('2d')
      ctx.drawImage(img, 0, 0)

      const COLORS = { aluminum: '#94a3b8', plastic: '#3b82f6', glass: '#22c55e', paper: '#eab308' }
      const fontSize = Math.max(13, Math.round(img.width / 35))

      for (const box of predictions) {
        if (!box.bbox) continue
        const { x1, y1, x2, y2 } = box.bbox
        const px = x1 * img.width,  py = y1 * img.height
        const pw = (x2 - x1) * img.width, ph = (y2 - y1) * img.height
        const color = COLORS[box.material] || '#60a5fa'
        const label = `${box.material} ${(box.confidence * 100).toFixed(0)}%`

        ctx.lineWidth   = Math.max(2, img.width / 200)
        ctx.strokeStyle = color
        ctx.strokeRect(px, py, pw, ph)

        ctx.font = `bold ${fontSize}px sans-serif`
        const tw = ctx.measureText(label).width
        ctx.fillStyle = color
        ctx.fillRect(px, py - fontSize - 6, tw + 10, fontSize + 6)
        ctx.fillStyle = '#000'
        ctx.fillText(label, px + 5, py - 4)
      }
      resolve(offscreen.toDataURL('image/jpeg', 0.92))
    }
    img.src = imageDataUrl
  })
}

async function simulateInsert() {
  capturedImageDataUrl.value  = null
  annotatedImageDataUrl.value = null
  rvm.setStep('conveyor')
  // Animate conveyor
  conveyorPos.value = 0
  const interval = setInterval(() => {
    conveyorPos.value += 2
    if (conveyorPos.value >= 90) clearInterval(interval)
  }, 50)

  await delay(2200)

  // ── CAMERA: only one capture option (real hardware camera), so go straight
  //    into it instead of waiting for the user to pick from a menu of one. ──
  rvm.setStep('camera')
  capturedImageDataUrl.value = null
  const capturedImagePath = await new Promise(resolve => {
    cameraResolve = resolve
    startCameraMode()
  })
  // ─────────────────────────────────────────────────────────────────────────

  rvm.setStep('classify')
  await delay(1950)

  // Step 1: Classify — AI detects the material type (no pre-selection)
  let isValid = true
  try {
    const res = await rvm.processStep('classify', {
      material_selected: rvm.selectedMaterial,
      image_path: capturedImagePath,
    })
    aiDetected.value   = res.ai_detected || rvm.selectedMaterial || 'plastic'
    aiConfidence.value = res.confidence || 0
    // Valid unless the AI couldn't recognize the material at all
    isValid = aiDetected.value !== 'unknown'
    rvm.setSelectedMaterial(aiDetected.value)
    if (capturedImageDataUrl.value && res.all_predictions?.length) {
      annotatedImageDataUrl.value = await drawBoundingBoxes(capturedImageDataUrl.value, res.all_predictions)
    }
  } catch {
    aiDetected.value = rvm.selectedMaterial || 'plastic'
    rvm.setSelectedMaterial(aiDetected.value)
    isValid = true
  }

  if (aiDetected.value === 'unknown') {
    // Not the AI's fault vs. the user's — no weight, no points, no deduction.
    // Still has to physically eject the item though, same as test_yolo.py's
    // reject drop — /hardware/sort is public (works for guest and logged-in
    // alike) and falls back to the reject slot for any unmapped material.
    api.post('/hardware/sort', { material: 'reject' }).catch(() => {})
    itemWeight.value = 0
    itemPoints.value = 0
    rvm.setStep('item_unknown')
    rvm.recordLocalTransaction({ material: 'unknown', weight: 0, points: 0, isValid: false, deducted: 0 })
    return
  }

  if (isValid) {
    rvm.setStep('validate_ok')
    await delay(1950)
    rvm.setStep('weigh')

    // Step 2: Weigh — fallback to random weight if API fails
    try {
      const weighRes = await rvm.processStep('weigh', {
        material_selected: rvm.selectedMaterial,
        ai_detected_type: aiDetected.value,
      })
      itemWeight.value = weighRes.weight_grams || (Math.floor(Math.random() * 400) + 50)
      itemPoints.value = weighRes.points_earned || Math.floor(itemWeight.value / 100) * 10
    } catch {
      itemWeight.value = Math.floor(Math.random() * 400) + 50
      itemPoints.value = Math.floor(itemWeight.value / 100) * 10
    }
    await delay(2600)

    // Step 3: Complete — always call API to save to DB
    try {
      const completeRes = await rvm.processStep('complete', {
        material_selected: rvm.selectedMaterial,
        ai_detected_type: aiDetected.value,
        ai_confidence: aiConfidence.value,
        weight_grams: itemWeight.value,
        points_earned: itemPoints.value,
      })
      displayPoints.value = completeRes.total_points || (displayPoints.value + itemPoints.value)
    } catch {
      displayPoints.value += itemPoints.value
    }
    rvm.recordLocalTransaction({ material: rvm.selectedMaterial, weight: itemWeight.value, points: itemPoints.value, isValid: true })
    rvm.setStep('complete')
  } else {
    // Show mismatch screen first so user sees what AI detected
    rvm.setStep('validate_fail')
    await delay(2600)

    // Step 3b: Reject — always call API to save deduction
    try {
      const rejectRes = await rvm.processStep('reject', {
        material_selected: rvm.selectedMaterial,
        ai_detected_type: aiDetected.value,
        ai_confidence: aiConfidence.value,
      })
      deductedPoints.value = rejectRes.points_deducted || 10
      displayPoints.value  = rejectRes.total_points || (displayPoints.value - deductedPoints.value)
    } catch {
      deductedPoints.value = 10
      displayPoints.value  = Math.max(0, displayPoints.value - 10)
    }
    rvm.recordLocalTransaction({ material: rvm.selectedMaterial, weight: 0, points: 0, isValid: false, deducted: deductedPoints.value })
    rvm.setStep('rejected')
  }
}

async function confirmEndSession() {
  if (rvm.isGuest) {
    await rvm.endSession()
    router.push(`/kiosk/${rvm.guestMachineCode}/summary`)
    return
  }
  try {
    const res = await rvm.endSession()
    const finalPoints = res?.session?.current_points ?? res?.session?.end_points ?? displayPoints.value
    if (auth.user) auth.updatePoints(finalPoints)
  } catch {
    if (auth.user) auth.updatePoints(displayPoints.value)
  }
  if (isKioskRoute.value) {
    router.push(`/kiosk/${kioskMachineCode.value}/summary`)
  } else {
    router.push('/session/summary')
  }
}

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

onMounted(() => {
  if (!rvm.session && !rvm.isGuest) { router.push('/scan'); return }
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
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(8px);
  border-radius: 10px;
  padding: 8px 14px;
  flex: 1;
}
.badge-label { display: block; color: rgba(255,255,255,0.8); font-size: 11px; margin-bottom: 3px; }
.badge-value { color: white; font-size: 20px; font-weight: 700; }
.status-green { color: #4ade80 !important; }
.status-blue  { color: #93c5fd !important; }

.progress-bar {
  height: 4px;
  background: var(--bg-card);
}
.progress-fill {
  height: 100%;
  background: var(--grad-header);
  transition: width 0.6s ease;
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
.camera-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
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
  left: 12px;
  right: 12px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #4e9ef5, transparent);
  animation: scan-move 2s ease-in-out infinite;
}
@keyframes scan-move {
  0%   { top: 12px; }
  50%  { top: calc(100% - 14px); }
  100% { top: 12px; }
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

/* Capture mode selection */
.capture-options {
  display: flex;
  gap: 16px;
  margin-top: 24px;
  justify-content: center;
  flex-wrap: wrap;
}
.capture-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 24px 32px;
  border-radius: 16px;
  border: 2px solid rgba(78, 158, 245, 0.4);
  background: rgba(78, 158, 245, 0.08);
  color: var(--text-primary);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s, transform 0.15s;
  min-width: 130px;
}
.capture-btn:hover {
  background: rgba(78, 158, 245, 0.2);
  border-color: #4e9ef5;
  transform: translateY(-2px);
}
.capture-icon {
  font-size: 36px;
  line-height: 1;
}

/* Selection */
.step-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
  text-align: center;
  margin-bottom: 20px;
}

.material-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.material-btn {
  padding: 20px 12px;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  color: white;
  transition: all 0.2s;
  position: relative;
}
.material-btn:hover:not(:disabled) { transform: translateY(-2px); filter: brightness(1.1); }
.material-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.mat-aluminum { background: #64748b; }
.mat-plastic  { background: #3b82f6; }
.mat-glass    { background: #22c55e; }
.mat-paper    { background: #6b7280; }

.mat-icon { font-size: 28px; }
.mat-name { font-size: 14px; }

.bin-full-badge {
  position: absolute;
  bottom: 6px;
  right: 6px;
  background: #ef4444;
  color: white;
  font-size: 9px;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 700;
}

.end-session-btn {
  width: 100%;
  padding: 13px;
  background: transparent;
  border: 2px solid var(--accent-red);
  color: var(--accent-red);
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.end-session-btn:hover { background: var(--accent-red); color: white; }

/* Spinner */
.spinner-lg {
  width: 60px; height: 60px;
  border: 4px solid var(--border);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

/* Lid animation */
.lid-animation { margin-bottom: 20px; }
.lid-box {
  width: 100px; height: 100px;
  border: 3px solid var(--border);
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  background: var(--bg-card);
}
.lid-door {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 50%;
  background: var(--bg-hover);
  border-bottom: 2px solid var(--border);
  transition: transform 3s ease;
  transform-origin: top center;
}
.lid-door.open { transform: rotateX(-90deg); }

/* Conveyor */
.conveyor-wrap { width: 100%; max-width: 320px; margin-bottom: 20px; }
.conveyor-track {
  height: 70px;
  background: var(--bg-card);
  border: 2px solid var(--border);
  border-radius: 8px;
  position: relative;
  overflow: hidden;
}
.conveyor-item {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  transition: left 0.1s linear;
  line-height: 1;
}
.conveyor-item-icon {
  width: 28px;
  height: 28px;
  color: var(--text-primary);
}
.conveyor-belt {
  position: absolute;
  bottom: 8px;
  left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, transparent 50%, var(--border) 50%);
  background-size: 16px 4px;
  animation: belt-move 0.5s linear infinite;
}
@keyframes belt-move { from { background-position: 0; } to { background-position: 16px; } }

/* Camera */
.camera-svg { width: 80px; height: 80px; animation: pulse 1.5s ease-in-out infinite; margin-bottom: 20px; }
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
.result-icon.invalid { background: #ef4444; color: white; }

/* Item being returned to the user (unrecognized item) */
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

.success-pulse { animation: success-pulse 2s ease-in-out 3; }
@keyframes success-pulse { 0%,100% { box-shadow:0 0 0 0 rgba(34,197,94,0.4); } 50% { box-shadow:0 0 0 20px transparent; } }

.pulse-red { animation: pulse-red 1.5s ease-in-out 2; }
@keyframes pulse-red { 0%,100% { box-shadow:0 0 0 0 rgba(239,68,68,0.4); } 50% { box-shadow:0 0 0 20px transparent; } }

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
.deduction-text { color: var(--accent-red) !important; font-weight: 700; font-size: 16px !important; }

.points-icon { width: 60px; height: 60px; color: var(--accent-green); margin-bottom: 16px; animation: pulse 1.2s ease-in-out infinite; }

.points-display { margin-top: 12px; text-align: center; }
.points-value { display: block; font-size: 40px; font-weight: 800; color: var(--accent-green); }
.points-preview { color: var(--text-secondary); font-size: 16px; font-weight: 600; }

.mat-inline-icon { width: 16px; height: 16px; vertical-align: -3px; margin: 0 2px; }

.simulate-btn {
  margin-top: 16px;
  padding: 14px 32px;
  background: var(--accent-green);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 16px rgba(34,197,94,0.3);
}
.simulate-btn:hover { transform: translateY(-2px); }

.insert-icon { margin-bottom: 16px; }
.box-3d {
  width: 64px;
  height: 64px;
  color: var(--text-primary);
  animation: float 2s ease-in-out infinite;
}
@keyframes float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-10px); } }

.action-buttons { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; justify-content: center; }

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
  background: var(--accent-blue);
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
.reject-hint { color: var(--text-muted); font-size: 13px; margin: 8px 0; }

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

.bbox-preview {
  width: 100%;
  max-width: 320px;
  border-radius: 10px;
  overflow: hidden;
  margin: 12px 0;
  border: 2px solid var(--border);
}
.bbox-img {
  width: 100%;
  display: block;
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
  .lid-box { width: 76px; height: 76px; }
  .box-3d { width: 48px; height: 48px; }
  .conveyor-track { height: 54px; }
  .ai-ring { width: 52px; height: 52px; margin-bottom: 12px; }
  .result-icon { width: 60px; height: 60px; margin-bottom: 10px; }
  .result-icon svg { width: 28px; height: 28px; }
  .points-icon { width: 44px; height: 44px; margin-bottom: 10px; }
  .points-value { font-size: 30px; }
  .camera-container { max-width: 260px; }

  .step-status { font-size: 17px; }
  .result-box { padding: 10px 18px; margin: 8px 0; }

  .rvm-footer { padding: 8px 16px; font-size: 12px; }
}
</style>
