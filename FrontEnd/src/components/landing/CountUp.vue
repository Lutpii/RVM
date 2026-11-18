<template>
  <span ref="el">{{ text }}</span>
</template>

<script setup>
// Counts a number up from 0 the first time it scrolls into view.
// null shows "—"; reduced-motion users (and browsers without
// IntersectionObserver) get the final value straight away.
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  value: { type: Number, default: null },
  format: { type: Function, default: (n) => String(Math.round(n)) },
  duration: { type: Number, default: 1200 },
})

const el = ref(null)
const shown = ref(null)
const seen = ref(false)
let frame = 0
let observer = null

const text = computed(() => (props.value == null ? '—' : props.format(shown.value ?? 0)))

function reducedMotion() {
  return typeof window === 'undefined' || !window.matchMedia
    || window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function run() {
  cancelAnimationFrame(frame)
  if (props.value == null) return
  const target = props.value
  if (reducedMotion()) { shown.value = target; return }
  const start = performance.now()
  const step = (now) => {
    const p = Math.min(1, (now - start) / props.duration)
    shown.value = target * (1 - Math.pow(1 - p, 3)) // ease-out
    if (p < 1) frame = requestAnimationFrame(step)
  }
  frame = requestAnimationFrame(step)
}

onMounted(() => {
  if (typeof IntersectionObserver === 'undefined' || reducedMotion()) {
    seen.value = true
    shown.value = props.value
    return
  }
  observer = new IntersectionObserver((entries) => {
    if (entries.some((e) => e.isIntersecting)) {
      seen.value = true
      observer.disconnect()
      run()
    }
  }, { threshold: 0.4 })
  observer.observe(el.value)
})

// Numbers arrive from the API after mount: count up once they are in.
watch(() => props.value, () => { if (seen.value) run() })

onBeforeUnmount(() => {
  cancelAnimationFrame(frame)
  observer?.disconnect()
})
</script>
