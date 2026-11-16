<template>
  <!-- One styled dropdown for the whole app: the same menu look as the landing
       language switch (card list, check mark, dark mode via theme variables). -->
  <div ref="root" :class="['app-select', `app-select-${size}`, { open, disabled }]">
    <button :id="id" ref="button" type="button" class="app-select-btn" :disabled="disabled"
      aria-haspopup="listbox" :aria-expanded="open" :aria-label="ariaLabel || undefined"
      @click="toggle" @keydown="onButtonKey">
      <span :class="['app-select-value', { placeholder: !hasValue }]">{{ label }}</span>
      <PhCaretDown class="app-select-caret" :size="14" weight="bold" aria-hidden="true" />
    </button>
    <ul v-if="open" ref="list" class="app-select-list" role="listbox" :aria-label="ariaLabel || undefined">
      <li v-for="(option, i) in options" :key="String(option.value)" role="none">
        <button type="button" role="option" class="app-select-item" :disabled="option.disabled"
          :aria-selected="option.value === modelValue" :data-index="i"
          @click="choose(option)" @keydown="onItemKey($event, i)">
          <span>{{ option.label }}</span>
          <PhCheck v-if="option.value === modelValue" :size="16" weight="bold" aria-hidden="true" />
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { PhCaretDown, PhCheck } from '@phosphor-icons/vue'
import { nextOptionIndex, optionLabel } from '@/utils/selectNav'

const props = defineProps({
  modelValue: { type: [String, Number, Boolean, null], default: null },
  // [{ value, label, disabled? }]
  options: { type: Array, required: true },
  placeholder: { type: String, default: '' },
  id: { type: String, default: undefined },
  ariaLabel: { type: String, default: '' },
  disabled: Boolean,
  // 'form' = modal/form field (44px), 'compact' = toolbar filter (38px)
  size: { type: String, default: 'form' },
})
const emit = defineEmits(['update:modelValue', 'change'])

const open = ref(false)
const root = ref(null)
const button = ref(null)
const list = ref(null)

const hasValue = computed(() => props.options.some((o) => o.value === props.modelValue))
const label = computed(() => optionLabel(props.options, props.modelValue, props.placeholder))
const enabled = computed(() => props.options.map((o) => !o.disabled))

function focusItem(i) {
  list.value?.querySelector(`[data-index="${i}"]`)?.focus()
}

async function openList() {
  open.value = true
  await nextTick()
  const selected = props.options.findIndex((o) => o.value === props.modelValue)
  focusItem(selected >= 0 ? selected : nextOptionIndex(-1, 'ArrowDown', enabled.value))
}

function close(refocus = false) {
  open.value = false
  if (refocus) nextTick(() => button.value?.focus())
}

function toggle() {
  if (props.disabled) return
  open.value ? close() : openList()
}

function choose(option) {
  if (option.disabled) return
  if (option.value !== props.modelValue) {
    emit('update:modelValue', option.value)
    emit('change', option.value)
  }
  close(true)
}

function onButtonKey(e) {
  if (e.key === 'Escape' && open.value) {
    e.preventDefault()
    e.stopPropagation()
    close()
  } else if (['ArrowDown', 'ArrowUp'].includes(e.key) && !open.value) {
    e.preventDefault()
    openList()
  }
}

function onItemKey(e, i) {
  if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(e.key)) {
    e.preventDefault()
    focusItem(nextOptionIndex(i, e.key, enabled.value))
  } else if (e.key === 'Escape') {
    e.preventDefault()
    e.stopPropagation() // do not also close the modal this select sits in
    close(true)
  } else if (e.key === 'Tab') {
    close()
  }
}

const onPointer = (e) => { if (open.value && root.value && !root.value.contains(e.target)) close() }
onMounted(() => document.addEventListener('pointerdown', onPointer))
onUnmounted(() => document.removeEventListener('pointerdown', onPointer))
</script>

<style scoped>
.app-select { position: relative; min-width: 0; }
.app-select-btn {
  width: 100%; box-sizing: border-box; display: flex; align-items: center; justify-content: space-between; gap: 8px;
  border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-primary);
  font: inherit; text-align: left; cursor: pointer;
}
.app-select-btn:hover:not(:disabled) { border-color: var(--accent-blue); }
.app-select-btn:focus-visible, .app-select.open .app-select-btn { outline: none; border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(78, 110, 242, 0.15); }
.app-select-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.app-select-value { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.app-select-value.placeholder { color: var(--text-muted); }
.app-select-caret { flex-shrink: 0; color: var(--text-muted); transition: transform 0.15s; }
.app-select.open .app-select-caret { transform: rotate(180deg); }

.app-select-form .app-select-btn { min-height: 44px; padding: 10px 13px; border-radius: 8px; font-size: 14px; }
.app-select-compact .app-select-btn { min-height: 38px; padding: 7px 10px; border-radius: 6px; font-size: 13px; }

.app-select-list {
  position: absolute; top: calc(100% + 6px); left: 0; z-index: 60; min-width: 100%; max-height: 280px; overflow-y: auto;
  margin: 0; padding: 4px; list-style: none; box-sizing: border-box;
  background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border); border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
}
.app-select-item {
  width: 100%; min-height: 40px; padding: 0 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px;
  border: 0; border-radius: 8px; background: transparent; color: inherit; font: inherit; font-size: 14px; text-align: left; cursor: pointer;
  white-space: nowrap;
}
.app-select-item:hover:not(:disabled), .app-select-item:focus-visible { background: var(--bg-secondary); outline: none; }
.app-select-item[aria-selected="true"] { font-weight: 700; color: var(--accent-blue); }
.app-select-item:disabled { opacity: 0.45; cursor: not-allowed; }
</style>
