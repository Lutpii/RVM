<!-- FrontEnd/src/components/ActiveSessionSheet.vue -->
<!-- P3: live list of the open session's items. Gets the session from the card's polling. -->
<template>
  <div class="sheet-overlay" @click.self="$emit('close')">
    <div class="sheet" role="dialog" aria-modal="true" :aria-label="$t('phoneSession.inProgress')">
      <h3 class="sheet-title">{{ $t('phoneSession.inProgress') }}</h3>
      <p class="sheet-sub">
        {{ summary.machineName }}<template v-if="showLocation"> · {{ summary.location }}</template>
      </p>
      <p class="sheet-total">
        {{ $t('phoneSession.summary', { items: summary.items, points: summary.points, carbon: summary.carbon.toFixed(3) }) }}
      </p>

      <ul class="item-list">
        <li v-for="item in items" :key="item.id" class="item-row">
          <svg
            v-if="materialIconSvg(itemType(item))" class="item-icon" aria-hidden="true"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(itemType(item))"
          ></svg>
          <span v-else class="item-icon"></span>
          <span class="item-main">
            <strong>{{ label(itemType(item)) }}</strong>
            <small v-if="item.ai_confidence != null">{{ confidence(item.ai_confidence) }}</small>
          </span>
          <span class="item-nums">
            <b :class="item.is_valid ? 'pts-green' : 'pts-zero'">{{ item.is_valid ? '+' : '' }}{{ item.points_earned || 0 }}</b>
            <small>{{ Number(item.carbon_saved || 0).toFixed(3) }} kg</small>
          </span>
        </li>
      </ul>
      <p v-if="!items.length" class="empty">-</p>

      <p v-if="summary.isDemo" class="sim-note">{{ $t('phoneSession.simNote') }}</p>
      <button ref="closeBtn" type="button" class="close-btn" @click="$emit('close')">{{ $t('activity.detail.close') }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { materialIconSvg } from '@/utils/materialIcons'
import { summarizeSession } from '@/utils/activeSession'
import { useEscapeToClose } from '@/composables/useEscapeToClose'

const props = defineProps({ session: { type: Object, default: null } })
const emit = defineEmits(['close'])
const closeBtn = ref(null)
useEscapeToClose(() => emit('close'), closeBtn)

const { t, te } = useI18n()
const summary = computed(() => summarizeSession(props.session))
const showLocation = computed(() => summary.value.location && !summary.value.machineName.includes(summary.value.location))
const items = computed(() => props.session?.items || [])

// A named reject keeps material 'reject'; it is labelled by what the AI saw.
function itemType(item) {
  return item.material_selected === 'reject' ? item.ai_detected_type : item.material_selected
}
function label(type) {
  const key = `session.${type}`
  return type && te(key) ? t(key) : (type || '-')
}
function confidence(c) {
  const n = Number(c)
  return `${(n <= 1 ? n * 100 : n).toFixed(1)}%`
}
</script>

<style scoped>
.sheet-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 210;
  display: flex; align-items: flex-end; justify-content: center;
}
.sheet {
  background: var(--bg-card); width: 100%; max-width: 480px; max-height: 85dvh; overflow-y: auto;
  border-radius: 16px 16px 0 0; padding: 20px 16px 16px;
  display: flex; flex-direction: column; gap: 8px;
}
.sheet-title { margin: 0; font-size: 17px; color: var(--text-primary); }
.sheet-sub { margin: 0; font-size: 13px; color: var(--text-secondary); }
.sheet-total { margin: 0; font-size: 14px; font-weight: 700; color: var(--text-primary); }
.item-list { list-style: none; margin: 4px 0 0; padding: 0; border: 1px solid var(--border); border-radius: var(--radius); }
.item-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-bottom: 1px solid var(--border); }
.item-row:last-child { border-bottom: none; }
.item-icon { width: 24px; height: 24px; flex-shrink: 0; color: var(--text-secondary); }
.item-main, .item-nums { display: flex; flex-direction: column; min-width: 0; }
.item-main { flex: 1; }
.item-nums { align-items: flex-end; }
.item-main strong { font-size: 14px; color: var(--text-primary); }
small { font-size: 11px; color: var(--text-muted); }
.pts-green { color: var(--accent-green); }
.pts-zero { color: var(--text-muted); }
.empty { margin: 0; text-align: center; color: var(--text-muted); }
.sim-note { margin: 0; font-size: 12px; color: #92400E; }
.close-btn {
  margin-top: 4px; min-height: 44px; border-radius: var(--radius); border: 1px solid var(--border);
  background: transparent; color: var(--text-primary); font-size: 14px; cursor: pointer;
}
</style>
