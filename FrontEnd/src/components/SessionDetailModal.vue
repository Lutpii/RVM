<!-- FrontEnd/src/components/SessionDetailModal.vue -->
<!-- Receipt for one past recycling session, opened from the Activity list (U05). -->
<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="detail-modal" role="dialog" aria-modal="true" :aria-label="$t('activity.detail.title')">
      <h3 class="detail-title">{{ $t('activity.detail.title') }}</h3>
      <p class="detail-code">{{ sessionCode }}</p>

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
              {{ materialLabel(t.material) }}
              <span v-if="!t.is_valid" class="item-rejected">{{ $t('activity.detail.rejected') }}</span>
            </span>
            <span v-if="t.is_valid" class="pts-green">+{{ t.points_earned }}</span>
            <span v-else class="pts-red">-{{ t.points_deducted }}</span>
          </li>
        </ul>
      </template>

      <button class="close-btn" @click="$emit('close')">{{ $t('activity.detail.close') }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const props = defineProps({
  sessionCode: { type: String, required: true },
})
defineEmits(['close'])

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
  display: flex; align-items: center; justify-content: center; z-index: 210; padding: 16px;
}
.detail-modal {
  background: var(--bg-card); border-radius: var(--radius); max-width: 400px; width: 100%;
  max-height: calc(100dvh - 32px); overflow-y: auto; padding: 20px;
  display: flex; flex-direction: column; gap: 8px;
}
.detail-title { font-size: 17px; color: var(--text-primary); margin: 0; }
.detail-code { font-family: ui-monospace, Menlo, monospace; font-size: 12px; color: var(--text-muted); margin: 0; word-break: break-all; }
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
.item-rejected { margin-left: 6px; font-size: 11px; color: var(--accent-red); }
.pts-green { color: var(--accent-green); font-weight: 700; }
.pts-red { color: var(--accent-red); font-weight: 700; }
.close-btn {
  margin-top: 8px; padding: 10px; border-radius: var(--radius); border: 1px solid var(--border);
  background: transparent; color: var(--text-primary); font-size: 14px; cursor: pointer;
}
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
