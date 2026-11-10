<!-- FrontEnd/src/views/ActivityView.vue -->
<template>
  <div class="activity-page">
    <h1 class="page-title">{{ $t('activity.title') }}</h1>

    <p v-if="!loading && hasLoadError" class="load-error-notice">{{ $t('activity.loadError') }}</p>

    <div v-if="loading" class="loading-placeholder">
      <div class="spinner-sm"></div>
    </div>
    <div v-else-if="feed.length === 0" class="empty-state">
      <p>{{ $t('activity.empty') }}</p>
    </div>
    <template v-else>
    <div class="filter-bar" role="group" :aria-label="$t('activity.filter.label')">
      <div class="chip-row">
        <button v-for="k in KIND_OPTIONS" :key="k.value" type="button"
                :class="['chip', { active: filters.kind === k.value }]" :aria-pressed="filters.kind === k.value"
                @click="filters.kind = k.value">{{ $t(k.label) }}</button>
      </div>
      <div class="chip-row">
        <button v-for="r in RANGE_OPTIONS" :key="r.value" type="button"
                :class="['chip', { active: filters.range === r.value }]" :aria-pressed="filters.range === r.value"
                @click="filters.range = r.value">{{ $t(r.label) }}</button>
      </div>
      <div class="chip-row">
        <button v-for="m in MATERIAL_OPTIONS" :key="m.value" type="button"
                :class="['chip', { active: filters.material === m.value }]" :aria-pressed="filters.material === m.value"
                @click="filters.material = m.value">{{ $t(m.label) }}</button>
      </div>
      <div class="chip-row">
        <button v-for="st in STATUS_OPTIONS" :key="st.value" type="button"
                :class="['chip', { active: filters.status === st.value }]" :aria-pressed="filters.status === st.value"
                @click="filters.status = st.value">{{ $t(st.label) }}</button>
      </div>
    </div>
    <div v-if="visibleFeed.length === 0" class="empty-state">
      <p>{{ $t('activity.noMatch') }}</p>
    </div>
    <div v-else class="activity-list">
      <component :is="entry.kind === 'session' ? 'button' : 'div'" v-for="(entry, index) in visibleFeed" :key="entry.id"
                 :type="entry.kind === 'session' ? 'button' : undefined"
                 :class="['activity-item', { clickable: entry.kind === 'session' }]"
                 :aria-label="entry.kind === 'session' ? $t('activity.detail.viewDetails') + ': ' + entry.description : undefined"
                 @click="entry.kind === 'session' && openSession(entry.description)" v-reveal="index">
        <div class="activity-icon" aria-hidden="true">
          <PhRecycle v-if="entry.kind === 'session'" class="icon-session" weight="regular" />
          <PhGift v-else-if="entry.kind === 'redemption'" class="icon-redemption" weight="regular" />
          <PhPlus v-else-if="entry.pointsChange > 0" class="icon-positive" weight="regular" />
          <PhMinus v-else class="icon-negative" weight="regular" />
        </div>
        <div class="activity-info">
          <span class="activity-desc">{{ entry.kind === 'session' ? $t('activity.session', { code: entry.description }) : (entry.kind === 'redemption' ? $t('activity.redemption', { name: entry.description }) : entry.description) }}</span>
          <span class="activity-time">{{ formatTime(entry.timestamp) }}</span>
        </div>
        <span v-if="entry.pointsChange != null" :class="['activity-pts', entry.pointsChange > 0 ? 'pts-green' : 'pts-red']">
          {{ entry.pointsChange > 0 ? '+' : '' }}{{ entry.pointsChange }}
        </span>
        <PhCaretRight v-if="entry.kind === 'session'" class="row-caret" weight="regular" aria-hidden="true" />
      </component>
    </div>
    </template>

    <SessionDetailModal v-if="openSessionCode" :session-code="openSessionCode" @close="openSessionCode = null" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { PhCaretRight, PhGift, PhMinus, PhPlus, PhRecycle } from '@phosphor-icons/vue'
import api from '@/services/api'
import { mergeActivityFeed, filterActivityFeed } from '@/utils/activityFeed'
import SessionDetailModal from '@/components/SessionDetailModal.vue'

const KIND_OPTIONS = [
  { value: 'all',       label: 'activity.filter.all' },
  { value: 'recycling', label: 'activity.filter.recycling' },
  { value: 'rewards',   label: 'activity.filter.rewards' },
  { value: 'points',    label: 'activity.filter.points' },
]
const RANGE_OPTIONS = [
  { value: 'all', label: 'activity.filter.allTime' },
  { value: '7d',  label: 'activity.filter.last7' },
  { value: '30d', label: 'activity.filter.last30' },
]

const MATERIAL_OPTIONS = [
  { value: 'all',      label: 'activity.filter.allMaterials' },
  { value: 'plastic',  label: 'session.plastic' },
  { value: 'aluminum', label: 'session.aluminum' },
  { value: 'glass',    label: 'session.glass' },
  { value: 'paper',    label: 'session.paper' },
]
const STATUS_OPTIONS = [
  { value: 'all',      label: 'activity.filter.allStatuses' },
  { value: 'credited', label: 'activity.filter.credited' },
  { value: 'deducted', label: 'activity.filter.deducted' },
]

const feed    = ref([])
const loading = ref(true)
const filters = reactive({ kind: 'all', range: 'all', material: 'all', status: 'all' })
const visibleFeed = computed(() => filterActivityFeed(feed.value, filters, new Date()))
const openSessionCode = ref(null)
function openSession(code) { if (code) openSessionCode.value = code }

const pointsHistoryFailed = ref(false)
const sessionsFailed      = ref(false)
const redemptionsFailed   = ref(false)
const hasLoadError = computed(() => pointsHistoryFailed.value || sessionsFailed.value || redemptionsFailed.value)

function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  let pointsHistory = []
  let sessions = []
  let redemptions = []

  try {
    const res = await api.get('/user/points-history')
    pointsHistory = res.data.history?.data || []
  } catch {
    pointsHistory = []
    pointsHistoryFailed.value = true
  }

  try {
    const res = await api.get('/user/sessions')
    sessions = res.data.sessions?.data || []
  } catch {
    sessions = []
    sessionsFailed.value = true
  }

  try {
    const res = await api.get('/user/reward-redemptions')
    redemptions = res.data.redemptions?.data || []
  } catch {
    redemptions = []
    redemptionsFailed.value = true
  }

  feed.value = mergeActivityFeed(pointsHistory, sessions, redemptions)
  loading.value = false
})
</script>

<style scoped>
.activity-page { padding: 20px 16px 32px; max-width: 640px; margin: 0 auto; }
.page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.load-error-notice {
  font-size: 12px; color: var(--accent-yellow);
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 8px 12px; margin-bottom: 12px;
}
.empty-state {
  text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;
  background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border);
}
.activity-list { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.filter-bar { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.chip-row { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; }
.chip-row::-webkit-scrollbar { display: none; }
.chip {
  flex-shrink: 0; padding: 6px 12px; border-radius: 999px; font-size: 12px; cursor: pointer;
  border: 1px solid var(--border); background: var(--bg-card); color: var(--text-secondary);
}
.chip.active { background: var(--accent-blue); border-color: var(--accent-blue); color: #fff; }
.activity-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--border); }
button.activity-item { width: 100%; border-left: none; border-right: none; border-top: none; background: transparent; text-align: left; font: inherit; color: inherit; }
.activity-item.clickable { cursor: pointer; }
.activity-item.clickable:hover { background: var(--bg-hover, rgba(0,0,0,0.03)); }
.row-caret { width: 14px; height: 14px; flex-shrink: 0; color: var(--text-muted); }
.activity-item:last-child { border-bottom: none; }
.activity-icon { width: 18px; height: 18px; flex-shrink: 0; color: var(--text-secondary); }
.activity-icon :deep(svg) { display: block; width: 100%; height: 100%; }
.icon-session, .icon-positive { color: var(--accent-green); }
.icon-redemption { color: var(--accent-blue); }
.icon-negative { color: var(--accent-red); }
.activity-info { flex: 1; display: flex; flex-direction: column; }
.activity-desc { font-size: 13px; color: var(--text-primary); }
.activity-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.activity-pts { font-size: 14px; font-weight: 700; }
.pts-green { color: var(--accent-green); }
.pts-red { color: var(--accent-red); }
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
