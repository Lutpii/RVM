<template>
  <!-- One round button showing the current language. Tap it and the pill slides
       open to the left with all languages; tap one to switch, or tap outside /
       press Escape to close. The pill floats over its neighbours, so opening it
       never moves anything else in the header. -->
  <div ref="root" :class="['lang-pill', `lang-pill-${tone}`, { open }]">
    <div class="lang-pill-box" role="group" :aria-label="groupLabel">
      <div class="lang-pill-track" :style="{ transform: `translateX(calc(var(--_s) * ${open ? 0 : shift}))` }">
        <button
          v-for="(l, i) in languages"
          :key="l.code"
          :ref="el => { if (l.code === current) currentBtn = el }"
          type="button"
          :class="['lang-pill-btn', { active: l.code === current }]"
          :tabindex="open || l.code === current ? 0 : -1"
          :aria-hidden="!open && l.code !== current ? 'true' : undefined"
          :aria-expanded="l.code === current ? open : undefined"
          :aria-pressed="open ? l.code === current : undefined"
          :aria-label="open ? l.label : `${groupLabel}: ${l.label}`"
          :title="l.label"
          @click="onClick(l.code, i)"
        >{{ l.short }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { languageMenuNext } from '@/utils/languageMenu'
import { pillShift } from '@/utils/languagePill'

const props = defineProps({
  languages: { type: Array, required: true }, // [{ code, short, label }]
  current: { type: String, required: true },
  groupLabel: { type: String, default: 'Language' },
  // 'gradient': white on a gradient header. 'light': on a light page.
  tone: { type: String, default: 'gradient' },
})
const emit = defineEmits(['select'])

const open = ref(false)
const root = ref(null)
let currentBtn = null

// Collapsed, the track is slid right so only the current language shows.
const shift = computed(() => pillShift(props.languages.map(l => l.code), props.current))

async function act(action) {
  const next = languageMenuNext(open.value, action)
  open.value = next.open
  if (next.refocus) { await nextTick(); currentBtn?.focus() }
}

function onClick(code) {
  if (!open.value) { act('toggle'); return }
  if (code !== props.current) emit('select', code)
  act('choose')
}

const onKey = (e) => { if (e.key === 'Escape' && open.value) act('escape') }
const onPointer = (e) => { if (open.value && root.value && !root.value.contains(e.target)) act('outside') }

onMounted(() => {
  window.addEventListener('keydown', onKey)
  document.addEventListener('pointerdown', onPointer)
})
onUnmounted(() => {
  window.removeEventListener('keydown', onKey)
  document.removeEventListener('pointerdown', onPointer)
})
</script>

<style scoped>
.lang-pill {
  /* one button; a parent can set --lang-size (e.g. 36px in a tight header) */
  --_s: var(--lang-size, 44px);
  --lang-pad: 3px;
  --lang-count: 3;
  position: relative;
  width: calc(var(--_s) + 2 * var(--lang-pad));
  height: calc(var(--_s) + 2 * var(--lang-pad));
  flex-shrink: 0;
}

.lang-pill-box {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 5;
  height: 100%;
  width: 100%;
  padding: var(--lang-pad);
  border-radius: 999px;
  overflow: hidden;
  transition: width 0.32s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.25s ease, box-shadow 0.25s ease;
}
.lang-pill.open .lang-pill-box {
  width: calc(var(--lang-count) * var(--_s) + 2 * var(--lang-pad));
}

/* Track is anchored to the right edge, so the box grows to the left. */
.lang-pill-track {
  position: absolute;
  top: var(--lang-pad);
  right: var(--lang-pad);
  display: flex;
  transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
}

.lang-pill-btn {
  width: var(--_s);
  height: var(--_s);
  border: 0;
  border-radius: 999px;
  background: transparent;
  font: inherit;
  font-size: clamp(12px, calc(var(--_s) * 0.32), 15px);
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, opacity 0.2s ease;
}
.lang-pill:not(.open) .lang-pill-btn:not(.active) { opacity: 0; pointer-events: none; }

/* gradient tone: translucent circle with white text; opens to a white pill */
.lang-pill-gradient .lang-pill-box { background: rgba(255, 255, 255, 0.18); }
.lang-pill-gradient .lang-pill-btn { color: #fff; }
.lang-pill-gradient:not(.open) .lang-pill-box:hover { background: rgba(255, 255, 255, 0.28); }
.lang-pill-gradient.open .lang-pill-box { background: #fff; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18); }
.lang-pill-gradient.open .lang-pill-btn { color: #4a5568; }
.lang-pill-gradient.open .lang-pill-btn.active { color: #fff; }

/* light tone: white pill with a soft border on light pages */
.lang-pill-light .lang-pill-box { background: #fff; box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08), 0 2px 10px rgba(0, 0, 0, 0.06); }
.lang-pill-light.open .lang-pill-box { box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08), 0 6px 20px rgba(0, 0, 0, 0.12); }
.lang-pill-light .lang-pill-btn { color: #4a5568; }
.lang-pill-light .lang-pill-btn.active { color: #fff; }

/* Current language: gradient fill (collapsed on light pages, and when open) */
.lang-pill-light .lang-pill-btn.active,
.lang-pill.open .lang-pill-btn.active {
  background: linear-gradient(135deg, #4e6ef2 0%, #a855f7 100%);
}
.lang-pill.open .lang-pill-btn:not(.active):hover { background: rgba(78, 110, 242, 0.1); }

.lang-pill-btn:focus-visible { outline: 2px solid #4e6ef2; outline-offset: 1px; }
.lang-pill-gradient:not(.open) .lang-pill-btn:focus-visible { outline-color: #fff; }

@media (prefers-reduced-motion: reduce) {
  .lang-pill-box, .lang-pill-track, .lang-pill-btn { transition: none; }
}
</style>
