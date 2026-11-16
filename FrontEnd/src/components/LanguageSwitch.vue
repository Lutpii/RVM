<template>
  <!-- Kiosks: all three languages visible, one tap to switch. -->
  <div v-if="variant === 'segmented'" class="lang-seg" role="radiogroup" :aria-label="$t('settings.language')">
    <button v-for="l in languages" :key="l.code" type="button" role="radio"
      :class="['lang-seg-btn', { active: locale === l.code }]"
      :aria-checked="locale === l.code" :aria-label="l.label" @click="setLanguage(l.code)">
      {{ l.short }}
    </button>
  </div>

  <!-- Landing page: compact button that opens a small menu. variant="row": the same
       menu, but the trigger is a full-width row whose content the parent supplies
       through the default slot (Account page). -->
  <div v-else ref="root" :class="['lang-menu', { 'lang-menu-row': variant === 'row' }]">
    <button ref="button" type="button" class="lang-menu-btn" aria-haspopup="menu" :aria-expanded="open"
      :aria-label="`${$t('settings.language')}: ${currentLanguage.label}`" @click="act('toggle')">
      <slot :current="currentLanguage">
        {{ currentLanguage.short }}
        <PhCaretDown :size="14" weight="bold" aria-hidden="true" />
      </slot>
    </button>
    <div v-if="open" class="lang-menu-list" role="menu" :aria-label="$t('settings.language')">
      <button v-for="l in languages" :key="l.code" type="button" role="menuitemradio" class="lang-menu-item"
        :aria-checked="locale === l.code" @click="choose(l.code)">
        <span>{{ l.label }}</span>
        <PhCheck v-if="locale === l.code" :size="16" weight="bold" aria-hidden="true" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { PhCaretDown, PhCheck } from '@phosphor-icons/vue'
import { useLanguage } from '@/composables/useLanguage'
import { languageMenuNext } from '@/utils/languageMenu'
import { escapeListener } from '@/composables/useEscapeToClose'

defineProps({ variant: { type: String, default: 'segmented' } })

const { locale, languages, currentLanguage, setLanguage } = useLanguage()
const open = ref(false)
const root = ref(null)
const button = ref(null)

async function act(action) {
  const next = languageMenuNext(open.value, action)
  open.value = next.open
  if (next.refocus) { await nextTick(); button.value?.focus() }
}

function choose(code) {
  setLanguage(code)
  act('choose')
}

const onKey = escapeListener(() => act('escape'))
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
/* Sits in the kiosk header's translucent white-on-gradient look. */
.lang-seg { display: inline-flex; padding: 2px; gap: 2px; border-radius: 16px; background: rgba(255,255,255,0.2); }
.lang-seg-btn {
  min-width: 40px; min-height: 36px; padding: 0 10px; border: 0; border-radius: 14px;
  background: transparent; color: #fff; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer;
}
.lang-seg-btn.active { background: #fff; color: #1e3a8a; }
.lang-seg-btn:focus-visible { outline: 2px solid #fff; outline-offset: 1px; }

/* Same size and look as the landing header's round buttons. */
.lang-menu { position: relative; }
.lang-menu-btn {
  height: 44px; padding: 0 12px; display: inline-flex; align-items: center; gap: 4px; border: 0; border-radius: 999px;
  background: rgba(255,255,255,0.18); color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer;
}
.lang-menu-btn:hover { background: rgba(255,255,255,0.28); }
.lang-menu-list {
  position: absolute; top: calc(100% + 6px); right: 0; z-index: 20; min-width: 190px; padding: 4px;
  background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border); border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.18);
}
.lang-menu-item {
  width: 100%; min-height: 44px; padding: 0 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px;
  border: 0; border-radius: 8px; background: transparent; color: inherit; font: inherit; font-size: 14px; text-align: left; cursor: pointer;
}
.lang-menu-item:hover, .lang-menu-item:focus-visible { background: var(--bg-secondary); outline: none; }
.lang-menu-item[aria-checked="true"] { font-weight: 700; color: var(--accent-blue); }

/* Row trigger: plain, full width; the parent styles what is inside it. */
.lang-menu-row .lang-menu-btn {
  width: 100%; height: auto; padding: 0; gap: 0; border-radius: 0;
  background: transparent; color: inherit; font-weight: inherit; text-align: left;
}
.lang-menu-row .lang-menu-btn:hover { background: transparent; }
.lang-menu-row .lang-menu-list { right: 12px; }
</style>
