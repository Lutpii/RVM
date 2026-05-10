<template>
  <div class="scan-page">
    <div class="scan-header">
      <RouterLink to="/dashboard" class="back-btn">← {{ $t('nav.dashboard') }}</RouterLink>
      <h2>Scan RVM QR Code</h2>
      <p>Point your camera at the QR code on the machine</p>
    </div>

    <div class="scan-body">
      <!-- Manual token entry -->
      <div class="manual-section">
        <div class="qr-visual">
          <div class="qr-frame">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
            <div class="scan-line"></div>
            <div class="qr-placeholder">
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
          </div>
        </div>

        <p class="scan-hint">After scanning on the machine, enter the session token below:</p>

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

        <div class="divider-text">— OR —</div>

        <!-- Machine list shortcut -->
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { useRvmStore }  from '@/store/rvm'
import api from '@/services/api'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const rvm    = useRvmStore()

const token          = ref('')
const loading        = ref(false)
const error          = ref('')
const success        = ref('')
const machines       = ref([])
const loadingMachines = ref(true)

const activeMachines = computed(() => machines.value.filter(m => m.status === 'active'))

async function handleScan() {
  if (!token.value.trim()) return
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
        // Resume existing active session — phone still goes to dashboard
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
    // Demo: start session directly without QR token
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
    // Resume existing active session if backend returned one
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
      } catch { /* fall through to offline demo mode */ }
    }
    // Offline demo fallback — no backend session, AI calls will be skipped
    rvm.setMachine(machine)
    rvm.setSession({
      session_code:  'DEMO-' + Math.random().toString(36).substr(2, 8).toUpperCase(),
      user_name:     auth.user?.name,
      current_points: auth.user?.total_points || 0,
      start_points:  auth.user?.total_points || 0,
      points_earned: 0,
      total_items:   0,
      machine:       machine,
    })
    rvm.setStep('bin_check')
    router.push('/session')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  // Auto-fill token from QR scan URL (e.g. /#/scan?token=xxx&machine=RVM-001)
  if (route.query.token) {
    token.value = route.query.token
    // Auto-connect immediately if token present in URL
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
</script>

<style scoped>
.scan-page { min-height: 100vh; background: var(--bg-primary); display: flex; flex-direction: column; }
.scan-header { background: var(--grad-header); padding: 20px 20px 24px; text-align: center; position: relative; }
.back-btn { position: absolute; left: 16px; top: 18px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; }
.scan-header h2 { color: white; font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.scan-header p  { color: rgba(255,255,255,0.8); font-size: 13px; }
.scan-body { flex: 1; padding: 24px 16px; }
.manual-section { max-width: 400px; margin: 0 auto; }

/* QR Visual */
.qr-visual { display: flex; justify-content: center; margin-bottom: 20px; }
.qr-frame {
  width: 160px; height: 160px;
  position: relative;
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-card);
  border-radius: 8px;
}
.corner {
  position: absolute;
  width: 20px; height: 20px;
  border-color: var(--accent-blue);
  border-style: solid;
  border-width: 0;
}
.corner.tl { top: 8px; left: 8px; border-top-width: 3px; border-left-width: 3px; }
.corner.tr { top: 8px; right: 8px; border-top-width: 3px; border-right-width: 3px; }
.corner.bl { bottom: 8px; left: 8px; border-bottom-width: 3px; border-left-width: 3px; }
.corner.br { bottom: 8px; right: 8px; border-bottom-width: 3px; border-right-width: 3px; }
.scan-line {
  position: absolute;
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
