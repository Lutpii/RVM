<!-- FrontEnd/src/components/SessionDetailModal.vue -->
<!-- Receipt for one past recycling session, opened from the Activity list (U05). -->
<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="detail-modal" role="dialog" aria-modal="true" :aria-label="$t('activity.detail.title')">
      <h3 class="detail-title">{{ $t('activity.detail.title') }}</h3>
      <p class="detail-code">{{ sessionCode }}<span v-if="isDemo" class="demo-chip">{{ $t('activity.demoChip') }}</span></p>
      <p v-if="isDemo" class="demo-note">{{ $t('activity.demoNote') }}</p>

      <div v-if="loading" class="detail-loading"><div class="spinner-sm"></div></div>
      <p v-else-if="loadError" class="detail-error">{{ $t('activity.detail.loadError') }}</p>

      <template v-else-if="summary">
        <dl class="detail-meta">
          <div><dt>{{ $t('activity.detail.machine') }}</dt><dd>{{ summary.machine_name || '-' }}</dd></div>
          <div><dt>{{ $t('activity.detail.started') }}</dt><dd>{{ formatTime(summary.started_at) }}</dd></div>
          <div><dt>{{ $t('activity.detail.ended') }}</dt><dd>{{ formatTime(summary.ended_at) }}</dd></div>
          <div><dt>{{ $t('activity.detail.totalPoints') }}</dt><dd class="pts-green">+{{ summary.points_earned || 0 }}</dd></div>
        </dl>

        <h4 class="detail-sub">{{ $t('activity.detail.items') }} ({{ items.length }})</h4>
        <p v-if="items.length === 0" class="detail-empty">{{ $t('activity.detail.noItems') }}</p>
        <ul v-else class="detail-items">
          <li v-for="(t, i) in items" :key="i" class="detail-item">
            <span class="item-material">
              <svg v-if="materialIconSvg(t.material)" class="receipt-material-icon" aria-hidden="true"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                   stroke-linecap="round" stroke-linejoin="round" :data-material-icon="t.material?.toLowerCase()"
                   v-html="materialIconSvg(t.material)"></svg>
              {{ materialLabel(t.material) }}
              <span v-if="!t.is_valid" class="item-rejected">{{ $t('activity.detail.rejected') }}</span>
              <span v-if="t.id" class="item-ref">#{{ t.id }}<template v-if="t.created_at"> · {{ formatClock(t.created_at) }}</template></span>
              <small v-if="t.carbon_saved != null" class="item-carbon">{{ $t('phoneSession.co2Saved', { kg: Number(t.carbon_saved).toFixed(3) }) }}</small>
            </span>
            <span v-if="t.is_valid" class="pts-green">+{{ t.points_earned }}</span>
            <span v-else-if="t.points_deducted > 0" class="pts-red">-{{ t.points_deducted }}</span>
            <span v-else class="pts-zero">0</span>
          </li>
        </ul>
      </template>

      <button ref="closeBtn" class="close-btn" @click="$emit('close')">{{ $t('activity.detail.close') }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { materialIconSvg } from '@/utils/materialIcons'
import { useEscapeToClose } from '@/composables/useEscapeToClose'

const props = defineProps({
  sessionCode: { type: String, required: true },
  isDemo: { type: Boolean, default: false },
})
const emit = defineEmits(['close'])
const closeBtn = ref(null)
useEscapeToClose(() => emit('close'), closeBtn)

const { t, te } = useI18n()
const loading   = ref(true)
const loadError = ref(false)
const summary   = ref(null)
const items = computed(() => summary.value?.transactions || [])

function materialLabel(material) {
  const key = `session.${material}`
  return material && te(key) ? t(key) : (material || '-')
}

function formatTime(ts) {
  if (!ts) return '-'
  const d = new Date(ts)
  if (Number.isNaN(d.getTime())) return '-'
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function formatClock(ts) {
  const d = new Date(ts)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  try {
    const res = await api.get(`/sessions/${encodeURIComponent(props.sessionCode)}/summary`)
    summary.value = res.data.summary || null
    if (!summary.value) loadError.value = true
  } catch {
    loadError.value = true
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 1050; padding: 16px;
}
.detail-modal {
  background: var(--bg-card); border-radius: var(--radius); max-width: 400px; width: 100%;
  max-height: calc(100dvh - 32px); overflow-y: auto; padding: 20px;
  display: flex; flex-direction: column; gap: 8px;
}
.detail-title { font-size: 17px; color: var(--text-primary); margin: 0; }
.detail-code { font-family: ui-monospace, Menlo, monospace; font-size: 12px; color: var(--text-muted); margin: 0; word-break: break-all; }
.demo-chip { display: inline-block; margin-left: 8px; padding: 1px 8px; border-radius: 999px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; font-family: inherit; font-size: 10px; font-weight: 700; }
.demo-note { margin: 0; padding: 6px 10px; border-radius: 8px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; font-size: 12px; }
.detail-loading { display: flex; justify-content: center; padding: 16px; }
.detail-error { color: var(--accent-red); font-size: 13px; font-weight: 600; margin: 4px 0; }
.detail-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 12px; margin: 8px 0 0; }
.detail-meta dt { font-size: 11px; color: var(--text-muted); }
.detail-meta dd { margin: 2px 0 0; font-size: 13px; color: var(--text-primary); }
.detail-sub { font-size: 13px; color: var(--text-secondary); margin: 10px 0 0; }
.detail-empty { font-size: 13px; color: var(--text-muted); margin: 0; }
.detail-items { list-style: none; margin: 0; padding: 0; border: 1px solid var(--border); border-radius: var(--radius); }
.detail-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-bottom: 1px solid var(--border); font-size: 13px; color: var(--text-primary); }
.detail-item:last-child { border-bottom: none; }
.receipt-material-icon { width: 1.1em; height: 1.1em; vertical-align: -0.2em; margin-inline-end: 4px; }
.item-rejected { margin-left: 6px; font-size: 11px; color: var(--accent-red); }
.item-carbon { display: block; font-size: 11px; color: var(--carbon-accent); }
.item-ref { display: block; font-size: 11px; color: var(--text-muted); font-family: ui-monospace, Menlo, monospace; }
.pts-green, .detail-meta dd.pts-green { color: var(--accent-green); font-weight: 700; }
.pts-red { color: var(--accent-red); font-weight: 700; }
.pts-zero { color: var(--text-muted); font-weight: 700; }
.close-btn {
  margin-top: 8px; padding: 10px; border-radius: var(--radius); border: 1px solid var(--border);
  background: transparent; color: var(--text-primary); font-size: 14px; cursor: pointer;
}
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
