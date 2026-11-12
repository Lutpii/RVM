<!-- FrontEnd/src/components/ActiveSessionCard.vue -->
<!-- P2: "Session in progress" card on the phone Home screen. Renders nothing without a session. -->
<template>
  <section v-if="session" class="session-card" aria-live="polite">
    <div class="card-top">
      <span class="live-dot" aria-hidden="true"></span>
      <strong class="card-title">{{ $t('phoneSession.inProgress') }}</strong>
      <span v-if="summary.isDemo" class="demo-chip">
        <PhFlask weight="bold" aria-hidden="true" />
        {{ $t('phoneSession.demoMachine') }}
      </span>
    </div>
    <p class="card-machine">
      {{ summary.machineName }}<template v-if="showLocation"> · {{ summary.location }}</template>
    </p>
    <p class="card-summary">
      {{ $t('phoneSession.summary', { items: summary.items, points: summary.points, carbon: summary.carbon.toFixed(3) }) }}
    </p>
    <button type="button" class="view-btn" @click="sheetOpen = true">
      {{ $t('phoneSession.view') }}
    </button>
    <ActiveSessionSheet v-if="sheetOpen" :session="session" @close="sheetOpen = false" />
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { PhFlask } from '@phosphor-icons/vue'
import { useActiveSession } from '@/composables/useActiveSession'
import { summarizeSession } from '@/utils/activeSession'
import ActiveSessionSheet from '@/components/ActiveSessionSheet.vue'

const { session } = useActiveSession()
const summary = computed(() => summarizeSession(session.value))
const showLocation = computed(() => summary.value.location && !summary.value.machineName.includes(summary.value.location))
const sheetOpen = ref(false)
</script>

<style scoped>
.session-card {
  margin: 12px 16px 0; padding: 14px;
  background: var(--bg-card); border: 1px solid var(--accent-green); border-radius: var(--radius);
  display: flex; flex-direction: column; gap: 6px;
}
.card-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.live-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--accent-green); animation: pulse 1.4s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.card-title { font-size: 15px; color: var(--text-primary); flex: 1; }
.card-machine { margin: 0; font-size: 13px; color: var(--text-secondary); }
.card-summary { margin: 0; font-size: 14px; font-weight: 700; color: var(--text-primary); }
.demo-chip {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 999px;
  background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;
  font-size: 11px; font-weight: 700; line-height: 1.4; white-space: nowrap;
}
.demo-chip svg { width: 12px; height: 12px; flex-shrink: 0; }
.view-btn {
  margin-top: 4px; min-height: 44px; border: none; border-radius: var(--radius);
  background: var(--accent-blue); color: white; font-size: 14px; font-weight: 700; cursor: pointer;
}
.view-btn:focus-visible { outline: 3px solid var(--accent-blue); outline-offset: 2px; }
@media (max-width: 480px) { .session-card { margin: 10px 12px 0; } }
</style>
