<template>
  <div class="scan-page">
    <div class="scan-header">
      <RouterLink to="/dashboard" class="back-btn">← {{ $t('nav.dashboard') }}</RouterLink>
      <h2>Scan RVM QR Code</h2>
      <p>Point your camera at the QR code on the machine</p>
    </div>

    <div class="scan-body">
      <div class="manual-section">

        <!-- Camera scanner -->
        <div class="camera-section">
          <div class="qr-frame" :class="{ 'camera-active': cameraActive }">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>

            <!-- Live camera feed -->
            <video
              v-show="cameraActive"
              ref="videoRef"
              class="camera-feed"
              autoplay
              playsinline
              muted
            ></video>

            <!-- Hidden canvas for frame capture -->
            <canvas ref="canvasRef" class="hidden-canvas"></canvas>

            <!-- Scan line overlay (only when camera active) -->
            <div v-if="cameraActive" class="scan-line"></div>

            <!-- QR icon placeholder (when no camera) -->
            <div v-if="!cameraActive" class="qr-placeholder">
              <svg viewBox="0 0 80 80" width="60" height="60">
                <rect x="5" y="5" width="30" height="30" fill="none" stroke="var(--accent-blue)" stroke-width="3"/>
                <rect x="10" y="10" width="20" height="20" fill="var(--accent-blue)" opacity="0.3"/>
                <rect x="45" y="5" width="30" height="30" fill="none" stroke="var(--accent-blue)" stroke-width="3"/>
                <rect x="50" y="10" width="20" height="20" fill="var(--accent-blue)" opacity="0.3"/>
                <rect x="5" y="45" width="30" height="30" fill="none" stroke="var(--accent-blue)" stroke-width="3"/>
                <rect x="10" y="50" width="20" height="20" fill="var(--accent-blue)" opacity="0.3"/>
                <rect x="45" y="45" width="8" height="8" fill="var(--accent-blue)"/>
                <rect x="57" y="45" width="8" height="8" fill="var(--accent-blue)"/>
                <rect x="45" y="57" width="8" height="8" fill="var(--accent-blue)"/>
                <rect x="57" y="57" width="8" height="8" fill="var(--accent-blue)"/>
              </svg>
            </div>

            <!-- Detected flash overlay -->
            <div v-if="qrDetected" class="detected-flash"></div>
          </div>

          <div class="camera-status" v-if="cameraActive">
            <span class="pulse-dot"></span> Camera active — point at QR code
          </div>
          <div class="camera-status error" v-if="cameraError">
            ⚠ {{ cameraError }}
          </div>

          <button
            v-if="cameraSupported"
            class="camera-btn"
            @click="toggleCamera"
            :disabled="loading"
          >
            <span v-if="!cameraActive">📷 Open Camera</span>
            <span v-else>✕ Close Camera</span>
          </button>
          <p v-else class="insecure-note">📷 Camera unavailable — enter the token manually below</p>
        </div>

        <div class="divider-text">— OR ENTER MANUALLY —</div>

        <!-- <p class="scan-hint">Enter the session token shown on the machine screen:</p> -->

        <div class="token-input-wrap">
          <input
            v-model="token"
            type="text"
            placeholder="Enter QR token (from machine screen)"
            class="token-input"
            @keyup.enter="handleScan"
          />
        </div>

        <div v-if="error" class="error-msg">{{ error }}</div>
        <div v-if="success" class="success-msg">{{ success }}</div>

        <button class="scan-btn" @click="handleScan" :disabled="loading || !token">
          <span v-if="loading" class="spinner"></span>
          {{ loading ? 'Connecting...' : '🔗 Connect to Machine' }}
        </button>

        <!-- Machine list shortcut
        <div class="divider-text">— OR —</div>

        <p class="shortcut-label">Select a machine directly (demo mode):</p>
        <div v-if="loadingMachines" class="loading-row"><div class="spinner-sm"></div> Loading machines...</div>
        <div v-else class="machine-shortcuts">
          <button
            v-for="machine in activeMachines"
            :key="machine.id"
            class="machine-shortcut"
            @click="connectDemo(machine)"
            :disabled="loading"
          >
            <span class="shortcut-dot dot-green"></span>
            <div class="shortcut-info">
              <strong>{{ machine.name }}</strong>
              <span>{{ machine.location_name }}</span>
            </div>
            <span>→</span>
          </button>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { useRvmStore }  from '@/store/rvm'
import api from '@/services/api'
import jsQR from 'jsqr'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const rvm    = useRvmStore()

const token           = ref('')
const loading         = ref(false)
const error           = ref('')
const success         = ref('')
const machines        = ref([])
const loadingMachines = ref(true)

// Camera state
const videoRef     = ref(null)
const canvasRef    = ref(null)
const cameraActive = ref(false)
const cameraError  = ref('')
const qrDetected   = ref(false)
let stream         = null
let scanInterval   = null

// Camera is only available in secure contexts (HTTPS / localhost)
const cameraSupported = computed(() =>
  window.isSecureContext && !!navigator.mediaDevices?.getUserMedia
)

const activeMachines = computed(() => machines.value.filter(m => m.status === 'active'))

async function toggleCamera() {
  if (cameraActive.value) {
    stopCamera()
  } else {
    await startCamera()
  }
}

async function startCamera() {
  cameraError.value = ''

  // Camera API requires HTTPS or localhost (secure context)
  if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
    cameraError.value = 'Camera requires HTTPS. Open the app via https:// or enter the token manually below.'
    return
  }

  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 640 }, height: { ideal: 640 } },
    })
    videoRef.value.srcObject = stream
    cameraActive.value = true
    scanInterval = setInterval(scanFrame, 200)
  } catch (e) {
    if (e.name === 'NotAllowedError') {
      cameraError.value = 'Camera permission denied. Please allow camera access and try again.'
    } else if (e.name === 'NotFoundError') {
      cameraError.value = 'No camera found on this device.'
    } else if (e.name === 'NotReadableError' || e.message?.toLowerCase().includes('in use')) {
      cameraError.value = 'Camera is in use by another app. Close it and try again.'
    } else {
      cameraError.value = 'Could not access camera: ' + e.message
    }
  }
}

function stopCamera() {
  clearInterval(scanInterval)
  scanInterval = null
  if (stream) {
    stream.getTracks().forEach(t => t.stop())
    stream = null
  }
  if (videoRef.value) videoRef.value.srcObject = null
  cameraActive.value = false
}

function scanFrame() {
  const video = videoRef.value
  const canvas = canvasRef.value
  if (!video || !canvas || video.readyState < 2) return

  const size = Math.min(video.videoWidth, video.videoHeight)
  canvas.width  = size
  canvas.height = size

  const ctx = canvas.getContext('2d')
  const offsetX = (video.videoWidth  - size) / 2
  const offsetY = (video.videoHeight - size) / 2
  ctx.drawImage(video, offsetX, offsetY, size, size, 0, 0, size, size)

  const imageData = ctx.getImageData(0, 0, size, size)
  const code = jsQR(imageData.data, imageData.width, imageData.height, {
    inversionAttempts: 'dontInvert',
  })

  if (code && code.data) {
    onQrDetected(code.data)
  }
}

function onQrDetected(data) {
  stopCamera()
  qrDetected.value = true
  setTimeout(() => { qrDetected.value = false }, 1000)

  // If the QR contains a full URL with ?token= param, extract just the token
  try {
    const url = new URL(data)
    const t = url.searchParams.get('token')
    if (t) { token.value = t; handleScan(); return }
  } catch { /* not a URL */ }

  token.value = data
  handleScan()
}

function extractToken(raw) {
  const s = raw.trim()
  try {
    const t = new URL(s).searchParams.get('token')
    if (t) return t
  } catch { /* not a URL */ }
  // Handle hash-URL pasted without protocol: e.g. "10.x.x.x:5173/#/scan?token=ABC"
  const m = s.match(/[?&]token=([^&]+)/)
  if (m) return decodeURIComponent(m[1])
  return s
}

async function handleScan() {
  const raw = token.value.trim()
  if (!raw) return
  token.value = extractToken(raw)
  if (!token.value) return
  loading.value = true
  error.value   = ''
  success.value = ''
  try {
    const res = await api.post('/qr/scan', { token: token.value.trim() })
    if (res.data.success) {
      success.value = 'Connected! Returning to dashboard...'
      rvm.setMachine(res.data.machine)

      try {
        const sessionRes = await api.post('/sessions/start', {
          machine_id: res.data.machine_id,
          qr_token:   token.value.trim(),
        })
        if (sessionRes.data.success) {
          setTimeout(() => router.push('/dashboard'), 800)
        }
      } catch (sessionErr) {
        const errData = sessionErr.response?.data
        if (errData?.session_code) {
          setTimeout(() => router.push('/dashboard'), 800)
          return
        }
        success.value = ''
        error.value = errData?.message || 'Failed to start session.'
      }
    } else {
      error.value = res.data.message || 'Invalid QR code.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to connect to machine.'
  } finally {
    loading.value = false
  }
}

async function connectDemo(machine) {
  loading.value = true
  error.value   = ''
  try {
    const sessionRes = await api.post('/sessions/start', {
      machine_id: machine.id,
      qr_token:   'DEMO_' + Date.now(),
    })

    if (sessionRes.data.success) {
      rvm.setMachine(machine)
      rvm.setSession(sessionRes.data.session)
      rvm.setStep('bin_check')
      router.push('/session')
    } else {
      error.value = sessionRes.data.message
    }
  } catch (e) {
    const errData = e.response?.data
    if (errData?.session_code) {
      try {
        const showRes = await api.get(`/sessions/${errData.session_code}`)
        if (showRes.data.success) {
          rvm.setMachine(showRes.data.session.machine || machine)
          rvm.setSession(showRes.data.session)
          rvm.setStep('bin_check')
          router.push('/session')
          return
        }
      } catch { /* fall through */ }
    }
    rvm.setMachine(machine)
    rvm.setSession({
      session_code:   'DEMO-' + Math.random().toString(36).substr(2, 8).toUpperCase(),
      user_name:      auth.user?.name,
      current_points: auth.user?.total_points || 0,
      start_points:   auth.user?.total_points || 0,
      points_earned:  0,
      total_items:    0,
      machine:        machine,
    })
    rvm.setStep('bin_check')
    router.push('/session')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (route.query.token) {
    token.value = route.query.token
    await handleScan()
  }

  try {
    const res = await api.get('/machines')
    machines.value = res.data.machines || []
  } catch {
    machines.value = [
      { id: 1, machine_code: 'RVM-001', name: 'RVM Kuantan Mall', location_name: 'Kuantan Parade', status: 'active', aluminum_level: 45, plastic_level: 30, glass_level: 80, paper_level: 100 },
      { id: 2, machine_code: 'RVM-002', name: 'RVM UMP', location_name: 'Universiti Malaysia Pahang', status: 'active', aluminum_level: 20, plastic_level: 60, glass_level: 15, paper_level: 40 },
    ]
  } finally {
    loadingMachines.value = false
  }
})

onUnmounted(() => {
  stopCamera()
})
</script>

<style scoped>
.scan-page { min-height: 100vh; background: var(--bg-primary); display: flex; flex-direction: column; }
.scan-header { background: var(--grad-header); padding: 20px 20px 24px; text-align: center; position: relative; }
.back-btn { position: absolute; left: 16px; top: 18px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; }
.scan-header h2 { color: white; font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.scan-header p  { color: rgba(255,255,255,0.8); font-size: 13px; }
.scan-body { flex: 1; padding: 24px 16px; }
.manual-section { max-width: 400px; margin: 0 auto; }

/* Camera section */
.camera-section { display: flex; flex-direction: column; align-items: center; margin-bottom: 8px; }

.qr-frame {
  width: 240px; height: 240px;
  position: relative;
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-card);
  border-radius: 12px;
  overflow: hidden;
  transition: width 0.3s, height 0.3s;
}
.qr-frame.camera-active {
  width: 100%;
  max-width: 380px;
  height: 320px;
}

.corner {
  position: absolute; z-index: 10;
  width: 24px; height: 24px;
  border-color: var(--accent-blue);
  border-style: solid;
  border-width: 0;
  pointer-events: none;
}
.corner.tl { top: 8px; left: 8px; border-top-width: 3px; border-left-width: 3px; }
.corner.tr { top: 8px; right: 8px; border-top-width: 3px; border-right-width: 3px; }
.corner.bl { bottom: 8px; left: 8px; border-bottom-width: 3px; border-left-width: 3px; }
.corner.br { bottom: 8px; right: 8px; border-bottom-width: 3px; border-right-width: 3px; }

.scan-line {
  position: absolute; z-index: 10;
  left: 12px; right: 12px;
  height: 2px;
  background: var(--accent-blue);
  opacity: 0.8;
  animation: scan 2s ease-in-out infinite;
  box-shadow: 0 0 8px var(--accent-blue);
}
@keyframes scan {
  0%, 100% { top: 20px; }
  50%       { top: calc(100% - 20px); }
}

.camera-feed {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  object-fit: cover;
  border-radius: 12px;
}
.hidden-canvas { display: none; }

.detected-flash {
  position: absolute; inset: 0;
  background: rgba(34, 197, 94, 0.35);
  border-radius: 12px;
  animation: flash 0.6s ease-out forwards;
  z-index: 20;
}
@keyframes flash {
  0%   { opacity: 1; }
  100% { opacity: 0; }
}

.camera-status {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; color: var(--text-secondary);
  margin: 8px 0 4px;
}
.camera-status.error { color: var(--accent-red); }

.pulse-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--accent-green);
  animation: pulse 1.2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.5; transform: scale(0.7); }
}

.camera-btn {
  margin-top: 10px; padding: 10px 28px;
  background: transparent;
  border: 1.5px solid var(--accent-blue);
  border-radius: var(--radius);
  color: var(--accent-blue);
  font-size: 14px; font-weight: 600; cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.camera-btn:hover { background: var(--accent-blue); color: white; }
.camera-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.insecure-note { font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 10px; }

.scan-hint { text-align: center; color: var(--text-secondary); font-size: 13px; margin-bottom: 16px; }
.token-input-wrap { margin-bottom: 12px; }
.token-input {
  width: 100%; padding: 13px 16px;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); color: var(--text-primary);
  font-size: 14px; outline: none; font-family: monospace;
  transition: border-color 0.2s;
}
.token-input:focus { border-color: var(--accent-blue); }
.error-msg   { background: rgba(239,68,68,0.1); color: var(--accent-red); padding: 8px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 12px; }
.success-msg { background: rgba(34,197,94,0.1); color: var(--accent-green); padding: 8px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 12px; }
.scan-btn {
  width: 100%; padding: 14px;
  background: var(--accent-blue); color: white;
  border: none; border-radius: var(--radius);
  font-size: 15px; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: opacity 0.2s;
}
.scan-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.divider-text { text-align: center; color: var(--text-muted); font-size: 12px; margin: 20px 0 16px; }
.shortcut-label { font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; text-align: center; }
.loading-row { display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 13px; justify-content: center; padding: 12px; }
.machine-shortcuts { display: flex; flex-direction: column; gap: 10px; }
.machine-shortcut {
  display: flex; align-items: center; gap: 12px;
  padding: 14px; background: var(--bg-card);
  border: 1px solid var(--border); border-radius: var(--radius);
  cursor: pointer; transition: all 0.2s; text-align: left; width: 100%;
  color: var(--text-primary);
}
.machine-shortcut:hover { border-color: var(--accent-blue); background: var(--bg-hover); }
.machine-shortcut:disabled { opacity: 0.5; cursor: not-allowed; }
.shortcut-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.shortcut-info { flex: 1; display: flex; flex-direction: column; }
.shortcut-info strong { font-size: 14px; font-weight: 600; }
.shortcut-info span   { font-size: 12px; color: var(--text-muted); }
.spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
.spinner-sm { width: 16px; height: 16px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
