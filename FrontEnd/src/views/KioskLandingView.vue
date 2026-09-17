<template>
  <div class="kiosk-landing" data-theme="light">
    <div class="kiosk-bg"></div>

    <div class="kiosk-content">
      <div class="rvm-logo-float">
        <PhRecycle class="rvm-logo" weight="regular" />
      </div>
      <h1 class="rvm-title">{{ $t('app.name') }}</h1>
      <p class="rvm-subtitle">{{ $t('kioskLanding.subtitle') }}</p>

      <div class="machine-badge">
        <span class="machine-dot"></span>
        <span>{{ machineName }} &nbsp;·&nbsp; {{ machineLocation || $t('kioskLanding.loading') }}</span>
      </div>

      <button class="start-btn min-h-kiosk-touch" @click="goToQr">
        <PhPlay class="start-icon" weight="fill" />
        {{ $t('dashboard.startRecycling') }}
      </button>

      <!-- <p class="hint-text">{{ $t('kioskLanding.hint') }}</p> -->
    </div>

    <div class="kiosk-footer">
      <BrandFooter class="brand-logo" />
      <span>UMPSA &nbsp;·&nbsp; DSME Engineering</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { PhRecycle, PhPlay } from '@phosphor-icons/vue'
import BrandFooter from '@/components/BrandFooter.vue'

const router = useRouter()
const route  = useRoute()

const machineCode     = route.params.machineCode || 'RVM-001'
const machineName     = ref('RVM Machine')
const machineLocation = ref('')

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
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  color: var(--text-primary);
  position: relative;
  overflow: hidden;
}

.kiosk-bg {
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(78,110,242,0.14) 0%, transparent 65%),
              radial-gradient(ellipse at 80% 100%, rgba(34,197,94,0.12) 0%, transparent 50%);
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

.rvm-logo-float {
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 0;
  animation: float 4s cubic-bezier(0.45, 0, 0.55, 1) infinite;
}

.rvm-logo {
  font-size: 96px;
  color: var(--accent-green);
  filter: drop-shadow(0 0 40px rgba(34,197,94,0.5));
  animation: pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes pop {
  from { transform: scale(0.5); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.rvm-title {
  font-size: 52px;
  font-weight: 800;
  color: var(--text-primary);
  letter-spacing: -1px;
  margin: 0;
}

.rvm-subtitle {
  font-size: 22px;
  color: var(--text-secondary);
  margin: 0;
}

.machine-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  /* Light kiosk surface (data-theme="light" on the view root, above) —
     dark-alpha here matches the light background, not an oversight. */
  background: rgba(0,0,0,0.04);
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 999px;
  padding: 8px 20px;
  color: var(--text-secondary);
  font-size: 16px;
}

.machine-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--accent-green);
  box-shadow: 0 0 8px var(--accent-green);
  /* Static, not blinking — an infinitely-looping animation on a screen
     that can idle indefinitely violates the Pi motion budget (spec §6).
     The badge text next to it already conveys live status; the color
     alone still reads as "online". */
}

.start-btn {
  margin-top: 20px;
  padding: 22px 64px;
  background: linear-gradient(135deg, var(--accent-blue), var(--accent-green));
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
  transition: transform 0.2s;
}
.start-btn:hover {
  transform: translateY(-3px) scale(1.02);
}

.start-icon {
  font-size: 24px;
}

.hint-text {
  color: var(--text-muted);
  font-size: 15px;
  margin: 0;
}

.kiosk-footer {
  position: absolute;
  bottom: max(24px, env(safe-area-inset-bottom));
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  color: var(--text-muted);
  font-size: 13px;
}

.brand-logo {
  /* See LandingView.vue's landing-brand-logo comment — trimmed logo, so this
     is much smaller than the old 96px to keep the same actual visual size. */
  --brand-logo-height: 54px;
}

/* Compact layout for small kiosk touchscreens (e.g. 1024x600) */
@media (max-height: 650px) {
  .kiosk-content { gap: 12px; padding: 20px; }
  .rvm-logo { font-size: 76px; }
  .rvm-title { font-size: 34px; }
  .rvm-subtitle { font-size: 16px; }
  .machine-badge { padding: 6px 16px; font-size: 13px; }
  .start-btn { margin-top: 8px; padding: 14px 40px; font-size: 20px; gap: 10px; }
  .start-icon { font-size: 18px; }
  .hint-text { font-size: 12px; }

  /* Logo height deliberately NOT overridden here — this breakpoint always
     matches the real kiosk's fixed 1024x600 screen (600 < 650), so shrinking
     it here would mean the physical kiosk never shows the same 54px as
     everywhere else (LandingView etc.), only ever this smaller one. */
  .kiosk-footer { bottom: max(10px, env(safe-area-inset-bottom)); gap: 3px; font-size: 11px; }
}

@media (prefers-reduced-motion: reduce) {
  .rvm-logo-float,
  .rvm-logo { animation: none; }
}
</style>
