<!-- FrontEnd/src/views/ActivityView.vue -->
<!-- Recycler activity (U05): tabs, one Filter button for date + material,
     entries grouped by day, a session's items folded into the session row. -->
<template>
  <div class="activity-page">
    <h1 class="page-title">{{ $t('activity.title') }}</h1>

    <p v-if="!loading && hasLoadError" class="load-error-notice">{{ $t('activity.loadError') }}</p>

    <div v-if="loading" class="loading-placeholder">
      <div class="spinner-sm"></div>
    </div>
    <div v-else-if="timeline.length === 0" class="empty-state">
      <p>{{ $t('activity.empty') }}</p>
    </div>
    <template v-else>
      <!-- Tabs + Filter button -->
      <div class="toolbar">
        <div class="tabs" role="tablist" :aria-label="$t('activity.filter.label')">
          <button v-for="tab in TABS" :key="tab" type="button" role="tab"
                  :class="['tab', { active: filters.tab === tab }]" :aria-selected="filters.tab === tab"
                  @click="filters.tab = tab">{{ $t('activity.tabs.' + tab) }}</button>
        </div>
        <button type="button" :class="['filter-btn', { active: filterCount > 0 || panelOpen }]"
                :aria-expanded="panelOpen" aria-controls="activity-filter-panel" @click="panelOpen = !panelOpen">
          <PhFunnelSimple weight="regular" aria-hidden="true" />
          <span>{{ $t('activity.filter.button') }}</span>
          <span v-if="filterCount" class="filter-count">{{ filterCount }}</span>
        </button>
      </div>

      <!-- Filter panel -->
      <div v-if="panelOpen" id="activity-filter-panel" class="filter-panel" @keydown.esc="panelOpen = false">
        <fieldset class="filter-group">
          <legend>{{ $t('activity.filter.date') }}</legend>
          <div class="option-row">
            <button v-for="r in RANGES" :key="r.value" type="button" :class="['option', { active: filters.range === r.value }]"
                    :aria-pressed="filters.range === r.value" @click="filters.range = r.value">{{ $t(r.label) }}</button>
          </div>
        </fieldset>
        <fieldset class="filter-group">
          <legend>{{ $t('activity.filter.material') }}</legend>
          <div class="option-row">
            <button v-for="m in MATERIALS" :key="m" type="button" :class="['option', { active: filters.material === m }]"
                    :aria-pressed="filters.material === m" @click="filters.material = m">
              {{ m === 'all' ? $t('activity.filter.allMaterials') : $t('session.' + m) }}
            </button>
          </div>
        </fieldset>
        <div class="panel-actions">
          <button type="button" class="link-btn" :disabled="!filterCount" @click="clearFilters">{{ $t('activity.filter.clear') }}</button>
          <button type="button" class="done-btn" @click="panelOpen = false">{{ $t('activity.filter.done') }}</button>
        </div>
      </div>

      <!-- Active filters, each removable -->
      <div v-if="filterCount && !panelOpen" class="active-filters">
        <button v-if="filters.range !== 'all'" type="button" class="active-chip" @click="filters.range = 'all'"
                :aria-label="$t('activity.filter.remove', { name: rangeLabel })">
          {{ rangeLabel }} <PhX weight="bold" aria-hidden="true" />
        </button>
        <button v-if="filters.material !== 'all'" type="button" class="active-chip" @click="filters.material = 'all'"
                :aria-label="$t('activity.filter.remove', { name: $t('session.' + filters.material) })">
          {{ $t('session.' + filters.material) }} <PhX weight="bold" aria-hidden="true" />
        </button>
        <button type="button" class="link-btn" @click="clearFilters">{{ $t('activity.filter.clear') }}</button>
      </div>

      <div v-if="groups.length === 0" class="empty-state">
        <p>{{ $t('activity.noMatch') }}</p>
        <button v-if="filterCount" type="button" class="done-btn" @click="clearFilters">{{ $t('activity.filter.clear') }}</button>
      </div>

      <section v-for="group in groups" :key="group.key" class="day-group">
        <h2 class="day-label">{{ dayLabel(group) }}</h2>
        <div class="activity-list">
          <component :is="entry.kind === 'session' ? 'button' : 'div'" v-for="entry in group.entries" :key="entry.id"
                     :type="entry.kind === 'session' ? 'button' : undefined"
                     :class="['activity-item', { clickable: entry.kind === 'session' }]"
                     @click="entry.kind === 'session' && openSession(entry.sessionCode)">
            <div :class="['activity-icon', 'icon-' + entry.kind]" aria-hidden="true">
              <PhRecycle v-if="entry.kind === 'session' || entry.kind === 'item'" weight="regular" />
              <PhGift v-else-if="entry.kind === 'redemption'" weight="regular" />
              <PhPlusMinus v-else weight="regular" />
            </div>
            <div class="activity-info">
              <span class="activity-title">{{ titleOf(entry) }}</span>
              <span class="activity-sub">{{ subtitleOf(entry) }}</span>
            </div>
            <span :class="['activity-pts', pointsClass(entry.pointsChange)]">{{ formatPoints(entry.pointsChange) }}</span>
            <PhCaretRight v-if="entry.kind === 'session'" class="row-caret" weight="regular" aria-hidden="true" />
            <span v-if="entry.kind === 'session'" class="sr-only">{{ $t('activity.detail.viewDetails') }}</span>
          </component>
        </div>
      </section>
    </template>

    <SessionDetailModal v-if="openSessionCode" :session-code="openSessionCode" @close="openSessionCode = null" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { PhCaretRight, PhFunnelSimple, PhGift, PhPlusMinus, PhRecycle, PhX } from '@phosphor-icons/vue'
import api from '@/services/api'
import { buildActivityTimeline, filterTimeline, groupByDay, activeFilterCount } from '@/utils/activityTimeline'
import SessionDetailModal from '@/components/SessionDetailModal.vue'

const { t, locale } = useI18n()

const TABS = ['all', 'recycling', 'rewards']
const RANGES = [
  { value: 'all', label: 'activity.filter.allTime' },
  { value: '7d',  label: 'activity.filter.last7' },
  { value: '30d', label: 'activity.filter.last30' },
]
const MATERIALS = ['all', 'plastic', 'aluminum', 'glass', 'paper']

const timeline  = ref([])
const loading   = ref(true)
const panelOpen = ref(false)
const filters   = reactive({ tab: 'all', range: 'all', material: 'all' })
const openSessionCode = ref(null)

const pointsHistoryFailed = ref(false)
const sessionsFailed      = ref(false)
const redemptionsFailed   = ref(false)
const hasLoadError = computed(() => pointsHistoryFailed.value || sessionsFailed.value || redemptionsFailed.value)

const filterCount = computed(() => activeFilterCount(filters))
const groups = computed(() => groupByDay(filterTimeline(timeline.value, filters, new Date()), new Date()))
const rangeLabel = computed(() => t(RANGES.find((r) => r.value === filters.range)?.label || 'activity.filter.allTime'))

function clearFilters() { filters.range = 'all'; filters.material = 'all' }
function openSession(code) { if (code) openSessionCode.value = code }

const dateLocale = computed(() => (locale.value === 'my' ? 'ms-MY' : 'en-GB'))
function dayLabel(group) {
  if (group.kind === 'today') return t('activity.today')
  if (group.kind === 'yesterday') return t('activity.yesterday')
  if (group.kind === 'unknown') return t('activity.unknownDate')
  return group.date.toLocaleDateString(dateLocale.value, { day: 'numeric', month: 'short', year: 'numeric' })
}
function clock(ts) {
  const d = new Date(ts)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleTimeString(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
}
function materialList(materials) {
  return materials.map((m) => t('session.' + m).toLowerCase()).join(', ')
}
function titleOf(e) {
  if (e.kind === 'session') return e.machineName ? t('activity.recycledAt', { machine: e.machineName }) : t('activity.recycledSession')
  if (e.kind === 'redemption') return t('activity.redemption', { name: e.title })
  return e.title
}
function subtitleOf(e) {
  const parts = []
  if (e.kind === 'session') {
    parts.push(t('activity.itemsCount', { n: e.itemCount }, e.itemCount))
    if (e.materials.length) parts.push(materialList(e.materials))
  }
  parts.push(clock(e.timestamp))
  return parts.filter(Boolean).join(' · ')
}
function formatPoints(n) {
  if (n == null) return ''
  return n > 0 ? `+${n}` : String(n)
}
function pointsClass(n) {
  if (n > 0) return 'pts-green'
  if (n < 0) return 'pts-red'
  return 'pts-zero'
}

onMounted(async () => {
  let pointsHistory = []
  let sessions = []
  let redemptions = []

  try {
    const res = await api.get('/user/points-history')
    pointsHistory = res.data.history?.data || []
  } catch {
    pointsHistoryFailed.value = true
  }
  try {
    const res = await api.get('/user/sessions')
    sessions = res.data.sessions?.data || []
  } catch {
    sessionsFailed.value = true
  }
  try {
    const res = await api.get('/user/reward-redemptions')
    redemptions = res.data.redemptions?.data || []
  } catch {
    redemptionsFailed.value = true
  }

  timeline.value = buildActivityTimeline(pointsHistory, sessions, redemptions)
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
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;
  background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border);
}
.empty-state p { margin: 0; }

/* toolbar */
.toolbar { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.tabs { flex: 1; display: flex; gap: 4px; padding: 4px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 999px; min-width: 0; }
.tab {
  flex: 1; min-height: 36px; padding: 0 10px; border: none; border-radius: 999px; background: transparent;
  color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap;
}
.tab.active { background: var(--accent-blue); color: #fff; }
.filter-btn {
  display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 14px; flex-shrink: 0;
  border: 1px solid var(--border); border-radius: 999px; background: var(--bg-card); color: var(--text-primary);
  font-size: 13px; font-weight: 600; cursor: pointer;
}
.filter-btn :deep(svg) { width: 16px; height: 16px; }
.filter-btn.active { border-color: var(--accent-blue); color: var(--accent-blue); }
.filter-count {
  min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: var(--accent-blue); color: #fff;
  font-size: 11px; line-height: 18px; text-align: center;
}

/* filter panel */
.filter-panel {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 14px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 14px;
}
.filter-group { border: none; margin: 0; padding: 0; }
.filter-group legend { font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; padding: 0; }
.option-row { display: flex; flex-wrap: wrap; gap: 8px; }
.option {
  min-height: 36px; padding: 0 14px; border-radius: 999px; border: 1px solid var(--border);
  background: transparent; color: var(--text-secondary); font-size: 13px; cursor: pointer;
}
.option.active { background: var(--accent-blue); border-color: var(--accent-blue); color: #fff; }
.panel-actions { display: flex; justify-content: space-between; align-items: center; }
.done-btn {
  min-height: 40px; padding: 0 18px; border-radius: 999px; border: none;
  background: var(--accent-blue); color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
}
.link-btn { background: none; border: none; padding: 8px 4px; color: var(--accent-blue); font-size: 13px; font-weight: 600; cursor: pointer; }
.link-btn:disabled { color: var(--text-muted); cursor: default; }

/* active filters */
.active-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 12px; }
.active-chip {
  display: inline-flex; align-items: center; gap: 6px; min-height: 32px; padding: 0 10px 0 12px;
  border-radius: 999px; border: 1px solid var(--accent-blue); background: transparent; color: var(--accent-blue);
  font-size: 12px; font-weight: 600; cursor: pointer;
}
.active-chip :deep(svg) { width: 12px; height: 12px; }

/* list */
.day-group { margin-bottom: 16px; }
.day-label { font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--text-muted); margin: 0 0 8px 4px; }
.activity-list { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.activity-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--border); min-height: 56px; }
.activity-item:last-child { border-bottom: none; }
button.activity-item { width: 100%; border-left: none; border-right: none; border-top: none; background: transparent; text-align: left; font: inherit; color: inherit; }
button.activity-item:last-child { border-bottom: none; }
.activity-item.clickable { cursor: pointer; }
.activity-item.clickable:hover { background: var(--bg-hover, rgba(0,0,0,0.03)); }
.activity-icon { width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--bg-secondary, rgba(0,0,0,0.04)); }
.activity-icon :deep(svg) { width: 18px; height: 18px; }
.icon-session, .icon-item { color: var(--accent-green); }
.icon-redemption { color: var(--accent-blue); }
.icon-adjustment { color: var(--text-secondary); }
.activity-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.activity-title { font-size: 14px; font-weight: 600; color: var(--text-primary); overflow-wrap: anywhere; }
.activity-sub { font-size: 12px; color: var(--text-muted); }
.activity-pts { font-size: 15px; font-weight: 700; flex-shrink: 0; }
.pts-green { color: var(--accent-green); }
.pts-red { color: var(--accent-red); }
.pts-zero { color: var(--text-muted); }
.row-caret { width: 16px; height: 16px; flex-shrink: 0; color: var(--text-muted); }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) { .spinner-sm { animation-duration: 2s; } }
</style>
