<script setup>
import { computed } from 'vue'

const props = defineProps({
  msLeft: { type: Number, required: true },
  totalMs: { type: Number, required: true },
})
defineEmits(['still-here'])

const R = 54
const C = 2 * Math.PI * R
const offset = computed(() => C * (1 - Math.min(1, Math.max(0, props.msLeft / props.totalMs))))
const seconds = computed(() => Math.ceil(Math.max(0, props.msLeft) / 1000))
</script>

<template>
  <div class="idle-overlay" role="alertdialog" aria-modal="true" aria-labelledby="idle-title">
    <div class="idle-card">
      <h2 id="idle-title">{{ $t('kiosk.idleTitle') }}</h2>
      <p>{{ $t('kiosk.idleText') }}</p>
      <div class="ring" aria-hidden="true">
        <svg viewBox="0 0 120 120" width="160" height="160">
          <circle cx="60" cy="60" :r="R" class="track" />
          <circle cx="60" cy="60" :r="R" class="bar" :stroke-dasharray="C" :stroke-dashoffset="offset" transform="rotate(-90 60 60)" />
        </svg>
        <span class="secs">{{ seconds }}</span>
      </div>
      <button type="button" class="still" @click="$emit('still-here')">{{ $t('kiosk.stillHere') }}</button>
    </div>
  </div>
</template>

<style scoped>
.idle-overlay { position: fixed; inset: 0; z-index: 10000; display: flex; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.85); }
.idle-card { background: var(--bg-card); color: var(--text-primary); border-radius: 12px; padding: 2.5rem 3rem; text-align: center; max-width: 90vw; }
.idle-card h2 { font-size: 2rem; margin: 0 0 0.5rem; }
.idle-card p { font-size: 1.25rem; margin: 0 0 1.5rem; }
.ring { position: relative; width: 160px; height: 160px; margin: 0 auto 1.75rem; }
.track { fill: none; stroke: rgba(100, 116, 139, 0.3); stroke-width: 10; }
.bar { fill: none; stroke: #16a34a; stroke-width: 10; stroke-linecap: round; }
.secs { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; }
.still { min-height: 72px; min-width: 280px; padding: 0 2rem; font-size: 1.5rem; font-weight: 600; border: 0; border-radius: 12px; background: #16a34a; color: #fff; cursor: pointer; }
.still:active { filter: brightness(0.9); }
</style>
