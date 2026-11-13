<!-- FrontEnd/src/components/SessionFloat.vue -->
<!-- Floating "session in progress" indicator: a pill on Home, a small badge on the
     other bottom-nav tabs. One element, so the pill morphs into the badge. -->
<template>
  <template v-if="store.session">
    <button
      v-show="mode !== 'hidden'"
      type="button"
      :class="['session-float', shape === 'badge' ? 'float-badge' : 'float-pill']"
      :aria-label="$t('phoneSession.openDetails', { machine: summary.machineName })"
      @click="store.sheetOpen = true"
    >
      <span class="pill-content">
        <span class="live-dot" aria-hidden="true"></span>
        <span class="pill-text">
          <span class="pill-title">
            {{ $t('phoneSession.inProgress') }}
            <span v-if="summary.isDemo" class="demo-chip">{{ $t('phoneSession.demoMachine') }}</span>
          </span>
          <small>{{ $t('phoneSession.pillLine', { machine: summary.machineName, items: summary.items, points: summary.points }) }}</small>
        </span>
        <span class="pill-chev" aria-hidden="true">›</span>
      </span>
      <span class="badge-content" aria-hidden="true">
        <PhRecycle weight="bold" class="badge-icon" />
        <span class="badge-points">+{{ summary.points }}</span>
      </span>
    </button>
  </template>
  <Transition name="session-sheet">
    <ActiveSessionSheet v-if="store.session && store.sheetOpen" :session="store.session" @close="store.sheetOpen = false" />
  </Transition>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { PhRecycle } from '@phosphor-icons/vue'
import { useAuthStore } from '@/store/auth'
import { useActiveSessionStore } from '@/store/activeSession'
import { summarizeSession } from '@/utils/activeSession'
import { floatMode } from '@/utils/sessionFloat'
import ActiveSessionSheet from '@/components/ActiveSessionSheet.vue'

const route = useRoute()
const auth = useAuthStore()
const store = useActiveSessionStore()

const summary = computed(() => summarizeSession(store.session))
const mode = computed(() => floatMode({
  routeName: route.name,
  showAppNav: route.meta.showAppNav === true,
  hasSession: !!store.session,
  sheetOpen: store.sheetOpen,
}))

// Keep the last visible shape while hidden so the element does not morph as it appears or goes away.
const shape = ref(mode.value === 'badge' ? 'badge' : 'pill')
watch(mode, (m) => { if (m !== 'hidden') shape.value = m })

// Poll only for a logged-in user on a phone (user) route, never on kiosk or admin screens.
const isUserRoute = computed(() => !/^\/(kiosk|demo-kiosk|admin)(\/|$)/.test(route.path))
watch(
  () => auth.isLoggedIn && isUserRoute.value,
  (on) => (on ? store.start() : store.stop()),
  { immediate: true },
)
</script>

<style scoped>
.session-float {
  position: fixed; z-index: 1040; /* above the page and nav, below the 1050 sheet */
  bottom: calc(84px + env(safe-area-inset-bottom)); /* clear of the bottom nav and its lifted scan button */
  left: 12px; right: 12px; height: 56px;
  padding: 0; overflow: hidden; cursor: pointer; text-align: left;
  background: #ffffff; color: #111827;
  border: none; border-left: 4px solid var(--accent-green);
  border-radius: 12px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
  transition: left 0.3s ease, right 0.3s ease, height 0.3s ease, border-radius 0.3s ease, opacity 0.2s ease;
}
.float-badge { left: calc(100% - 62px); height: 50px; }
[data-theme="dark"] .session-float { background: #334155; color: #f1f5f9; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.5); }
.session-float:focus-visible { outline: 3px solid var(--accent-blue); outline-offset: 2px; }

.pill-content, .badge-content {
  position: absolute; inset: 0; display: flex; align-items: center;
  transition: opacity 0.2s ease;
}
.pill-content { gap: 10px; padding: 0 12px; font-size: 14px; }
.float-pill .badge-content, .float-badge .pill-content { opacity: 0; pointer-events: none; }
.pill-content { min-width: 0; }
.pill-text { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.3; }
.pill-title { display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 14px; white-space: nowrap; }
.pill-text small { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
[data-theme="dark"] .pill-text small { color: #cbd5e1; }
.pill-chev { font-size: 20px; opacity: 0.7; }
.live-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; background: var(--accent-green); animation: pulse 1.4s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.demo-chip {
  padding: 1px 8px; border-radius: 999px; background: #FEF3C7; color: #92400E;
  border: 1px solid #FCD34D; font-size: 10px; font-weight: 700; line-height: 1.5;
}

.badge-content { justify-content: center; }
.badge-icon { width: 24px; height: 24px; }
.badge-points {
  position: absolute; top: 3px; right: 3px; padding: 1px 5px; border-radius: 8px;
  background: var(--accent-green); color: #fff; font-size: 10px; font-weight: 700; line-height: 1.4;
}

@media (min-width: 769px) {
  .session-float { bottom: 20px; }
  .float-pill { left: max(12px, calc(50% - 210px)); right: max(12px, calc(50% - 210px)); }
}
@media (prefers-reduced-motion: reduce) {
  .session-float, .pill-content, .badge-content { transition: none; }
  .live-dot { animation: none; }
}
</style>
