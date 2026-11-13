<!-- FrontEnd/src/views/SessionConnectedView.vue -->
<!-- P1: short "Happy recycling" splash after the phone connects to a machine. -->
<template>
  <div class="splash">
    <div v-if="loading" class="splash-body" role="status" aria-live="polite">
      <span class="spinner" aria-hidden="true"></span>
      <p class="connected-to">{{ $t('phoneSession.connecting') }}</p>
    </div>
    <div v-if="session" class="splash-body">
      <PhRecycle class="splash-icon" weight="bold" aria-hidden="true" />
      <h1>{{ $t('phoneSession.happy', { name: auth.user?.name || '' }) }}</h1>
      <p class="connected-to">{{ $t('phoneSession.connectedTo') }}</p>
      <strong class="machine">{{ summary.machineName }}</strong>
      <span v-if="summary.location && !summary.machineName.includes(summary.location)" class="location">{{ summary.location }}</span>
      <span v-if="summary.isDemo" class="demo-chip">
        <PhFlask weight="bold" aria-hidden="true" />
        {{ $t('phoneSession.demoMachine') }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { PhRecycle, PhFlask } from '@phosphor-icons/vue'
import api from '@/services/api'
import { useAuthStore } from '@/store/auth'
import { summarizeSession } from '@/utils/activeSession'

const SPLASH_MS = 3000
const router = useRouter()
const auth = useAuthStore()
const session = ref(null)
const loading = ref(true)
const summary = computed(() => summarizeSession(session.value))
let timer = null

onMounted(async () => {
  try {
    const res = await api.get('/user/active-session')
    session.value = res.data?.session || null
  } catch {
    session.value = null
  }
  loading.value = false
  if (!session.value) {
    router.replace('/dashboard')
    return
  }
  timer = setTimeout(() => router.replace('/dashboard'), SPLASH_MS)
})
onUnmounted(() => clearTimeout(timer))
</script>

<style scoped>
.splash {
  min-height: 100dvh; background: var(--grad-header); color: white;
  display: flex; align-items: center; justify-content: center; padding: 24px; text-align: center;
}
.splash-body { display: flex; flex-direction: column; align-items: center; gap: 8px; animation: rise 0.4s ease-out; }
@keyframes rise { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
.splash-icon { width: 64px; height: 64px; }
h1 { margin: 8px 0 4px; font-size: 26px; font-weight: 800; line-height: 1.2; }
.connected-to { margin: 0; opacity: 0.85; font-size: 14px; }
.machine { font-size: 20px; }
.location { opacity: 0.85; font-size: 14px; }
.demo-chip {
  margin-top: 10px; display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 12px; border-radius: 999px;
  background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;
  font-size: 12px; font-weight: 700;
}
.demo-chip svg { width: 13px; height: 13px; }
.spinner {
  width: 40px; height: 40px; border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.35); border-top-color: white;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
