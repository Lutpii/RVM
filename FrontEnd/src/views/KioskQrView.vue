<template>
  <div class="kiosk-qr">
    <div class="kiosk-bg"></div>

    <!-- Header -->
    <div class="qr-header">
      <button class="back-btn" @click="$router.push(`/kiosk/${machineCode}`)">← Back</button>
      <span class="machine-label">{{ machineCode }}</span>
    </div>

    <!-- WAITING — show QR -->
    <div v-if="state === 'waiting'" class="qr-content">
      <h2 class="qr-title">Scan to Start Recycling</h2>
      <p class="qr-sub">Open your phone camera and scan the QR code below</p>

      <div class="qr-box">
        <div v-if="loadingQr" class="qr-loading">
          <div class="spinner"></div>
          <span>Generating QR...</span>
        </div>
        <img
          v-else-if="qrSvgSrc"
          :src="qrSvgSrc"
          class="qr-image"
          alt="QR Code"
        />
        <div v-else class="qr-error">Failed to load QR</div>

        <!-- Animated scan corners -->
        <div class="corner tl"></div>
        <div class="corner tr"></div>
        <div class="corner bl"></div>
        <div class="corner br"></div>
      </div>

      <div class="qr-url" v-if="currentToken">Token: {{ currentToken }}</div>

      <div class="qr-steps">
        <div class="step"><span class="step-num">1</span> Open this URL on your phone</div>
        <div class="step"><span class="step-num">2</span> Login to your RVM account</div>
        <div class="step"><span class="step-num">3</span> Session will start automatically</div>
      </div>

      <div class="timer-bar">
        <div class="timer-fill" :style="{ width: timerPct + '%' }"></div>
      </div>
      <p class="timer-text">QR expires in {{ expiresInSec }}s — will auto-refresh</p>

      <div class="guest-divider">
        <span class="guest-divider-line"></span>
        <span class="guest-divider-text">or</span>
        <span class="guest-divider-line"></span>
      </div>

      <button class="guest-btn" @click="startAsGuest">
        <span class="guest-btn-icon">👤</span>
        Continue as Guest
        <span class="guest-btn-note">Points will be donated</span>
      </button>
    </div>

    <!-- SCANNED — session active -->
    <div v-else-if="state === 'scanned'" class="qr-content scanned-content">
      <div class="success-ring">
        <div class="success-icon">✓</div>
      </div>
      <h2 class="qr-title green">Session Started!</h2>
      <p class="qr-sub" v-if="scannedUser">
        Welcome, <strong>{{ scannedUser }}</strong>
      </p>
      <p class="qr-sub">Redirecting to selection...</p>
    </div>

    <!-- EXPIRED -->
    <div v-else-if="state === 'expired'" class="qr-content">
      <div class="expired-icon">⏱</div>
      <h2 class="qr-title">QR Code Expired</h2>
      <p class="qr-sub">Generating a new one...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api, { setKioskToken } from '@/services/api'
import { useRvmStore } from '@/store/rvm'
import { useAuthStore } from '@/store/auth'

const router      = useRouter()
const route       = useRoute()
const rvm         = useRvmStore()
const auth        = useAuthStore()
const machineCode = route.params.machineCode || 'RVM-001'

const state          = ref('waiting') // waiting | scanned | expired
const loadingQr      = ref(true)
const qrSvgSrc       = ref('')
const scanUrl        = ref('')
const currentToken   = ref('')
const scannedUser    = ref('')
const expiresInSec   = ref(300)
const timerPct       = ref(100)

let pollInterval  = null
let timerInterval = null
let isActive      = false
let machineData   = null  // cached from generate response

async function generateQr() {
  loadingQr.value  = true
  qrSvgSrc.value   = ''
  state.value      = 'waiting'
  expiresInSec.value = 300
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
        scannedUser.value = res.data.user_name || 'User'
        state.value = 'scanned'
        clearIntervals()
        isActive = false

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
        } else {
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
          if (rvm.session) rvm.session.user_name = scannedUser.value
        }
        router.push(`/kiosk/${machineCode}/session`)
      } else if (res.data.status === 'expired') {
        handleExpiry()
      }
    } catch { /* ignore */ }
  }, 2000)
}

function startTimer() {
  clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    expiresInSec.value -= 1
    timerPct.value = (expiresInSec.value / 300) * 100
    if (expiresInSec.value <= 0) handleExpiry()
  }, 1000)
}

function handleExpiry() {
  clearIntervals()
  state.value = 'expired'
  setTimeout(() => generateQr(), 2000)
}

function clearIntervals() {
  clearInterval(pollInterval)
  clearInterval(timerInterval)
}

function startAsGuest() {
  clearIntervals()
  rvm.startGuestSession(machineCode)
  router.push(`/kiosk/${machineCode}/session`)
}

async function startAuthenticatedSession() {
  rvm.isGuest = false
  try {
    const res = await api.get('/machines')
    const machine = (res.data.machines || []).find(m => m.machine_code === machineCode)
    if (machine) {
      try {
        const sessionRes = await api.post('/sessions/start', {
          machine_id: machine.id,
          qr_token: 'KIOSK_' + Date.now(),
        })
        if (sessionRes.data.success) {
          rvm.setMachine(machine)
          rvm.setSession(sessionRes.data.session)
          rvm.setStep('bin_check')
          router.push(`/kiosk/${machineCode}/session`)
          return
        }
      } catch (e) {
        const errData = e.response?.data
        // Resume existing active session instead of falling to QR mode
        if (errData?.session_code) {
          try {
            const showRes = await api.get(`/sessions/${errData.session_code}`)
            if (showRes.data.success) {
              rvm.setMachine(showRes.data.session.machine || machine)
              rvm.setSession(showRes.data.session)
              rvm.setStep('bin_check')
              router.push(`/kiosk/${machineCode}/session`)
              return
            }
          } catch { /* fall through to QR */ }
        }
      }
    }
  } catch { /* fall through to QR */ }
  generateQr()
}

onMounted(() => {
  isActive = true
  if (auth.isLoggedIn) {
    startAuthenticatedSession()
  } else {
    generateQr()
  }
})
onBeforeUnmount(() => {
  isActive = false
  clearIntervals()
})
</script>

<style scoped>
.kiosk-qr {
  min-height: 100vh;
  background: #0f172a;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.kiosk-bg {
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% -10%, rgba(78,110,242,0.2) 0%, transparent 60%);
}

.qr-header {
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 32px;
}
.back-btn {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.12);
  color: rgba(255,255,255,0.6);
  padding: 8px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
}
.back-btn:hover { background: rgba(255,255,255,0.14); }
.machine-label {
  color: rgba(255,255,255,0.4);
  font-size: 13px;
  font-family: monospace;
}

.qr-content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
  padding: 20px 40px 40px;
  text-align: center;
  max-width: 520px;
  width: 100%;
}

.qr-title {
  font-size: 36px;
  font-weight: 800;
  color: #fff;
  margin: 0;
}
.qr-title.green { color: #22c55e; }
.qr-sub {
  font-size: 16px;
  color: rgba(255,255,255,0.55);
  margin: 0;
}

/* QR Box */
.qr-box {
  position: relative;
  width: 260px;
  height: 260px;
  background: white;
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
  border: 3px solid #e2e8f0;
  border-top-color: #4e6ef2;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.corner {
  position: absolute;
  width: 24px; height: 24px;
  border-color: #4e6ef2;
  border-style: solid;
  border-width: 0;
}
.corner.tl { top: -2px; left: -2px; border-top-width: 3px; border-left-width: 3px; border-radius: 4px 0 0 0; }
.corner.tr { top: -2px; right: -2px; border-top-width: 3px; border-right-width: 3px; border-radius: 0 4px 0 0; }
.corner.bl { bottom: -2px; left: -2px; border-bottom-width: 3px; border-left-width: 3px; border-radius: 0 0 0 4px; }
.corner.br { bottom: -2px; right: -2px; border-bottom-width: 3px; border-right-width: 3px; border-radius: 0 0 4px 0; }

.qr-url {
  font-family: monospace;
  font-size: 11px;
  color: rgba(255,255,255,0.25);
  word-break: break-all;
  max-width: 320px;
}

/* Steps */
.qr-steps {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  max-width: 320px;
}
.step {
  display: flex;
  align-items: center;
  gap: 12px;
  color: rgba(255,255,255,0.5);
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
  background: rgba(255,255,255,0.08);
  border-radius: 2px;
  overflow: hidden;
}
.timer-fill {
  height: 100%;
  background: linear-gradient(90deg, #4e6ef2, #22c55e);
  transition: width 1s linear;
  border-radius: 2px;
}
.timer-text {
  color: rgba(255,255,255,0.25);
  font-size: 12px;
  margin: 0;
}

/* Scanned state */
.scanned-content { padding-top: 60px; }
.success-ring {
  width: 120px; height: 120px;
  border-radius: 50%;
  background: rgba(34,197,94,0.15);
  border: 3px solid #22c55e;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 40px rgba(34,197,94,0.3);
  animation: pop 0.4s ease;
}
@keyframes pop { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.success-icon { font-size: 52px; color: #22c55e; font-weight: 700; }
.redirect-hint { color: rgba(255,255,255,0.3); font-size: 14px; margin: 0; }

/* Expired */
.expired-icon { font-size: 64px; }

/* Guest section */
.guest-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 260px;
  margin: 4px 0 0;
}
.guest-divider-line {
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.12);
}
.guest-divider-text {
  color: rgba(255,255,255,0.3);
  font-size: 12px;
}

.guest-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  width: 260px;
  padding: 14px 20px;
  background: rgba(255,255,255,0.05);
  border: 1px dashed rgba(255,255,255,0.2);
  border-radius: 12px;
  color: rgba(255,255,255,0.65);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.guest-btn:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.35);
  color: rgba(255,255,255,0.9);
}
.guest-btn-icon { font-size: 22px; margin-bottom: 2px; }
.guest-btn-note {
  font-size: 11px;
  font-weight: 400;
  color: rgba(34,197,94,0.75);
  letter-spacing: 0.02em;
}
</style>
