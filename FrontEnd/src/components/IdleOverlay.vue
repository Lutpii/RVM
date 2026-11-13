<script setup>
import { computed } from 'vue'

const props = defineProps({
  msLeft: { type: Number, required: true },
  totalMs: { type: Number, required: true },
})
defineEmits(['still-here'])

const progress = computed(() => Math.min(1, Math.max(0, props.msLeft / props.totalMs)))
const seconds = computed(() => Math.ceil(Math.max(0, props.msLeft) / 1000))
</script>

<template>
  <div class="idle-overlay" role="alertdialog" aria-modal="true" aria-labelledby="idle-title" aria-describedby="idle-time">
    <div class="idle-banner">
      <div class="idle-content">
        <div class="idle-message">
          <h2 id="idle-title">{{ $t('kiosk.idleTitle') }}</h2>
          <p id="idle-time">{{ $t('kiosk.idleText', { seconds }) }}</p>
        </div>
        <button type="button" class="still" @click="$emit('still-here')">{{ $t('kiosk.stillHere') }}</button>
      </div>
      <div class="idle-track" aria-hidden="true">
        <div class="idle-progress" :class="{ urgent: seconds <= 3 }" :style="{ transform: `scaleX(${progress})` }"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.idle-overlay {
  position: fixed; inset: 0; z-index: 10000;
  display: flex; align-items: flex-end; justify-content: center;
  padding: 16px 16px calc(16px + env(safe-area-inset-bottom));
  background: rgba(15, 23, 42, 0.36);
}
.idle-banner {
  width: 100%; max-width: 720px; overflow: hidden;
  border-radius: var(--radius); background: var(--bg-card);
  color: var(--text-primary); box-shadow: var(--shadow);
}
.idle-content {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 12px 24px; padding: 16px 20px;
}
.idle-message { flex: 1 1 220px; }
.idle-message h2 { margin: 0 0 4px; font-size: 1.5rem; }
.idle-message p { margin: 0; font-size: 1rem; font-variant-numeric: tabular-nums; }
.idle-track { height: 6px; background: var(--border); }
.idle-progress {
  height: 100%; background: #16a34a; transform-origin: left center;
  transition: transform 100ms linear, background-color 180ms ease;
}
.idle-progress.urgent { background: var(--accent-yellow); }
.still {
  min-height: 52px; padding: 8px 20px; border: 0; border-radius: var(--radius);
  background: #15803d; color: #fff; font-size: 1.0625rem; font-weight: 700; cursor: pointer;
}
.still:active { filter: brightness(0.9); }
@media (max-width: 480px) {
  .still { width: 100%; }
}
.idle-banner-enter-active, .idle-banner-leave-active { transition: opacity 220ms ease; }
.idle-banner-enter-active .idle-banner, .idle-banner-leave-active .idle-banner {
  transition: transform 220ms ease;
}
.idle-banner-enter-from, .idle-banner-leave-to { opacity: 0; }
.idle-banner-enter-from .idle-banner, .idle-banner-leave-to .idle-banner {
  transform: translateY(16px);
}
.idle-banner-leave-active { pointer-events: none; }
@media (prefers-reduced-motion: reduce) {
  .idle-banner-enter-active, .idle-banner-leave-active,
  .idle-banner-enter-active .idle-banner, .idle-banner-leave-active .idle-banner {
    transition: none;
  }
  .idle-progress { transition: none; }
}
</style>
