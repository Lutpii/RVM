<template>
  <div class="kiosk-landing">
    <div class="kiosk-bg"></div>

    <div class="kiosk-content">
      <div class="rvm-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em">
          <path d="M3 12a9 9 0 0 1 15-6.7L21 8"/>
          <path d="M21 3v5h-5"/>
          <path d="M21 12a9 9 0 0 1-15 6.7L3 16"/>
          <path d="M3 21v-5h5"/>
        </svg>
      </div>
      <h1 class="rvm-title">Reverse Vending Machine</h1>
      <p class="rvm-subtitle">Smart Recycling System</p>

      <div class="machine-badge">
        <span class="machine-dot"></span>
        <span>{{ machineName }} &nbsp;·&nbsp; {{ machineLocation }}</span>
      </div>

      <button class="start-btn" @click="goToQr">
        <span class="start-icon">▶</span>
        Start Recycling
      </button>

      <p class="hint-text">Scan the QR code with your phone to begin</p>
    </div>

    <div class="kiosk-footer">
      <img src="@/assets/dsme-logo.png" class="dsme-logo" alt="DSME Engineering" />
      <span>UMPSA &nbsp;·&nbsp; Eco Smart Campus</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const route  = useRoute()

const machineCode     = route.params.machineCode || 'RVM-001'
const machineName     = ref('RVM Machine')
const machineLocation = ref('Loading...')

onMounted(async () => {
  try {
    const res = await api.get('/machines')
    const machines = res.data.machines || []
    const machine = machines.find(m => m.machine_code === machineCode)
    if (machine) {
      machineName.value     = machine.name
      machineLocation.value = machine.location_name
    }
  } catch {
    machineLocation.value = ''
  }
})

function goToQr() {
  router.push(`/kiosk/${machineCode}/qr`)
}
</script>

<style scoped>
.kiosk-landing {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  position: relative;
  overflow: hidden;
}

.kiosk-bg {
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(78,110,242,0.25) 0%, transparent 65%),
              radial-gradient(ellipse at 80% 100%, rgba(34,197,94,0.15) 0%, transparent 50%);
}

.kiosk-content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 40px;
  text-align: center;
}

.rvm-logo {
  font-size: 96px;
  filter: drop-shadow(0 0 40px rgba(34,197,94,0.5));
  animation: float 4s ease-in-out infinite;
}
@keyframes float {
  0%,100% { transform: translateY(0); }
  50%      { transform: translateY(-14px); }
}

.rvm-title {
  font-size: 52px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -1px;
  margin: 0;
}

.rvm-subtitle {
  font-size: 22px;
  color: rgba(255,255,255,0.5);
  margin: 0;
}

.machine-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 999px;
  padding: 8px 20px;
  color: rgba(255,255,255,0.7);
  font-size: 16px;
}

.machine-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #22c55e;
  box-shadow: 0 0 8px #22c55e;
  animation: blink 1.5s ease-in-out infinite;
}
@keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.3; } }

.start-btn {
  margin-top: 20px;
  padding: 22px 64px;
  background: linear-gradient(135deg, #4e6ef2, #22c55e);
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 28px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 8px 40px rgba(78,110,242,0.4);
  transition: transform 0.2s, box-shadow 0.2s;
}
.start-btn:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 12px 50px rgba(78,110,242,0.55);
}

.start-icon {
  font-size: 24px;
}

.hint-text {
  color: rgba(255,255,255,0.35);
  font-size: 15px;
  margin: 0;
}

.kiosk-footer {
  position: absolute;
  bottom: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: rgba(255,255,255,0.2);
  font-size: 13px;
}

.dsme-logo {
  height: 32px;
  width: auto;
  opacity: 0.55;
}

/* Compact layout for small kiosk touchscreens (e.g. 1024x600) */
@media (max-height: 650px) {
  .kiosk-content { gap: 12px; padding: 20px; }
  .rvm-logo { font-size: 56px; }
  .rvm-title { font-size: 34px; }
  .rvm-subtitle { font-size: 16px; }
  .machine-badge { padding: 6px 16px; font-size: 13px; }
  .start-btn { margin-top: 8px; padding: 14px 40px; font-size: 20px; gap: 10px; }
  .start-icon { font-size: 18px; }
  .hint-text { font-size: 12px; }

  .kiosk-footer { bottom: 10px; gap: 4px; font-size: 11px; }
  .kiosk-footer .dsme-logo { height: 20px; }
}
</style>
