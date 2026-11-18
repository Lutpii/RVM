<template>
  <div class="pmd">
    <!-- Compactor and Composter data is simulated, so "Real only" has nothing to show. -->
    <section v-if="scope === 'real'" class="section-card pmd-empty">
      <p>{{ t('adminUi.platform.dash.realEmpty') }}</p>
    </section>
    <p v-else-if="error" class="section-card" role="alert">{{ t('adminUi.platform.failed') }}</p>
    <div v-else-if="machines === null" class="pmd-loading" role="status"><div class="spinner-lg"></div></div>
    <template v-else>
      <!-- Today, same layout as the Ultra RVM dashboard -->
      <div class="overview-cards">
        <div v-for="card in todayCards" :key="card.key" class="overview-card">
          <svg v-if="card.material" class="ov-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
            :data-material-icon="card.material" aria-hidden="true" v-html="materialIconSvg(card.material)"></svg>
          <component :is="card.icon" v-else class="ov-icon" :style="{ color: card.color }" weight="regular" aria-hidden="true" />
          <div class="ov-label">{{ card.label }}</div>
          <div class="ov-count">{{ card.value }}</div>
          <div class="ov-sub">{{ card.sub }}</div>
        </div>
      </div>

      <div class="stats-grid">
        <div v-for="stat in statCards" :key="stat.key" class="stat-card">
          <component :is="stat.icon" class="stat-icon" weight="regular" aria-hidden="true" />
          <div class="stat-info">
            <div class="stat-value" :style="stat.color ? { color: stat.color } : {}">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
        <div class="stat-card machine-status-card">
          <div>
            <PhRobot class="ms-icon" weight="regular" aria-hidden="true" />
            <div class="ms-label">{{ t('adminUi.machineStatus') }}</div>
            <div :class="['ms-status', online > 0 ? 'ms-online' : 'ms-offline']">
              {{ online > 0 ? t('adminUi.statusOnline') : t('adminUi.statusOffline') }}
            </div>
            <div class="ms-sub">{{ t('adminUi.machinesOnline', { online, total: machines.length }) }}</div>
          </div>
        </div>
      </div>

      <div class="charts-row">
        <div class="section-card chart-card-main">
          <h3 class="card-title-bar chart-title"><span class="title-sq" :style="{ background: color }"></span> {{ t('adminUi.platform.dash.trendTitle') }}</h3>
          <p class="chart-sub">{{ t('adminUi.platform.dash.trendSub') }}</p>
          <div class="chart-wrap">
            <Bar v-if="barData" :data="barData" :options="barOptions" />
            <div v-else class="chart-empty">{{ t('adminUi.noDataYet') }}</div>
          </div>
        </div>
        <div class="section-card chart-card-side">
          <h3 class="card-title-bar chart-title"><span class="title-sq title-sq-yellow"></span> {{ sideTitle }}</h3>
          <p class="chart-sub">{{ sideSub }}</p>
          <div class="chart-wrap donut-wrap">
            <Doughnut v-if="donutData" :data="donutData" :options="donutOptions" />
            <div v-else class="chart-empty">{{ t('adminUi.noDataYet') }}</div>
          </div>
        </div>
      </div>

      <!-- Every machine with its readings, no click needed -->
      <section class="section-card">
        <h3 class="card-title-bar"><span class="title-sq" :style="{ background: color }"></span> {{ t('adminUi.platform.dash.machineList') }}
          <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></h3>
        <p v-if="!machines.length" class="platform-admin-muted">{{ t('adminUi.noMachines') }}</p>
        <div v-else class="pmd-machines">
          <article v-for="machine in machines" :key="machine.id" class="pmd-machine">
            <header class="pmd-machine-head">
              <div>
                <strong>{{ machine.name }}</strong>
                <small>{{ machine.location || t('adminUi.noLocation') }}</small>
              </div>
              <span :class="['pmd-status', 'pmd-status-' + machine.status]">{{ t('platform.status.' + machine.status) }}</span>
            </header>
            <div class="pmd-fill" :aria-label="t('adminUi.platform.fields.fill_level') + ' ' + (machine.telemetry?.fill_level ?? 0) + '%'">
              <span :style="{ width: Math.min(100, machine.telemetry?.fill_level ?? 0) + '%', background: fillColor(machine.telemetry?.fill_level) }"></span>
            </div>
            <dl class="pmd-readings">
              <div v-for="field in fields" :key="field.key">
                <dt>{{ t('adminUi.platform.fields.' + field.key) }}</dt>
                <dd>{{ shownValue(field, machine.telemetry?.[field.key]) }}</dd>
              </div>
            </dl>
            <ul v-if="alertsFor(machine.id).length" class="pmd-alerts">
              <li v-for="alert in alertsFor(machine.id)" :key="alert.code"><PhWarningOctagon aria-hidden="true" /> {{ t('adminUi.platform.alert.' + alert.code) }}</li>
            </ul>
          </article>
        </div>
        <p v-if="type === 'composter'" class="platform-admin-muted pmd-ideal">{{ t('adminUi.platform.ideal') }}</p>
      </section>

      <div v-if="$slots.default" class="pmd-slot"><slot /></div>

      <section class="section-card">
        <h3 class="card-title-bar"><span class="title-sq"></span> {{ t('adminUi.platform.recentActivity') }}</h3>
        <PlatformRows :type="type" :rows="recent" />
      </section>
    </template>
  </div>
</template>

<script setup>
// Compactor / Composter admin dashboard. Uses the existing platform endpoints only:
// /admin/platform/machines (readings), /overview (7-day CO2 + alerts), /activity (latest rows).
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Bar, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend } from 'chart.js'
import {
  PhStack, PhBowlFood, PhTray, PhThermometer, PhRobot, PhWarningOctagon,
  PhFactory, PhLeaf, PhGauge, PhListNumbers, PhProhibit, PhArrowsClockwise, PhDrop, PhFlask,
} from '@phosphor-icons/vue'
import { intlLocale } from '@/composables/useLanguage'
import { materialIconSvg } from '@/utils/materialIcons'
import api from '@/services/api'
import PlatformRows from './PlatformRows.vue'

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend)

const props = defineProps({
  type: { type: String, required: true },
  fields: { type: Array, required: true },
  scope: String,
  refresh: Number,
})
const { t, locale } = useI18n()

const machines = ref(null)
const overview = ref(null)
const recent = ref([])
const error = ref(false)

const isCompactor = computed(() => props.type === 'compactor')
const color = computed(() => (isCompactor.value ? '#d97706' : '#0891b2'))

watch(() => [props.scope, props.refresh, props.type], async ([scope], previous, onCleanup) => {
  const controller = new AbortController()
  onCleanup(() => controller.abort())
  error.value = false
  if (scope === 'real') return
  try {
    const signal = controller.signal
    const [list, ov, act] = await Promise.all([
      api.get('/admin/platform/machines', { params: { type: props.type }, signal }),
      api.get('/admin/platform/overview', { params: { scope: scope || 'all' }, signal }),
      api.get('/admin/platform/activity', { params: { type: props.type }, signal }),
    ])
    if (signal.aborted) return
    machines.value = list.data.machines
    overview.value = ov.data
    recent.value = act.data.activity?.data || []
  } catch {
    if (!controller.signal.aborted) error.value = true
  }
}, { immediate: true })

const nf = (value, digits = 0) => new Intl.NumberFormat(intlLocale(locale.value), { maximumFractionDigits: digits }).format(value)
const sumOf = key => (machines.value || []).reduce((sum, m) => sum + (Number(m.telemetry?.[key]) || 0), 0)
const avgOf = (key) => {
  const values = (machines.value || []).map(m => m.telemetry?.[key]).filter(v => typeof v === 'number')
  return values.length ? values.reduce((a, b) => a + b, 0) / values.length : null
}
// The composter reading has no "online" field, so count every machine that is running.
const online = computed(() => (machines.value || []).filter(m => !['offline', 'maintenance'].includes(m.status)).length)

const isToday = (value) => {
  const d = new Date(value)
  const now = new Date()
  return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate()
}
// Today's rows come from the latest activity page (compactor ≤ 5 batches and composter ≤ 7
// deposits per machine per day, so today fits in it).
const todayRows = computed(() => recent.value.filter(row => isToday(row.created_at)))
const todaySum = key => todayRows.value.reduce((sum, row) => sum + (Number(row[key]) || 0), 0)
const across = computed(() => t('adminUi.platform.dash.acrossMachines', { n: (machines.value || []).length }, (machines.value || []).length))

const todayCards = computed(() => (isCompactor.value
  ? [
      { key: 'plastic', material: 'plastic', label: t('adminUi.platform.dash.plasticToday'), value: nf(todaySum('plastic_count')), sub: t('adminUi.platform.dash.compactedToday') },
      { key: 'aluminium', material: 'aluminum', label: t('adminUi.platform.dash.aluminiumToday'), value: nf(todaySum('aluminum_count')), sub: t('adminUi.platform.dash.compactedToday') },
      { key: 'batches', icon: PhStack, color: '#7c3aed', label: t('adminUi.platform.dash.batchesToday'), value: nf(sumOf('batches_today')), sub: across.value },
    ]
  : [
      { key: 'food', icon: PhBowlFood, color: '#0891b2', label: t('adminUi.platform.dash.foodToday'), value: nf(sumOf('kg_today'), 2) + ' kg', sub: t('adminUi.platform.dash.receivedToday') },
      { key: 'deposits', icon: PhTray, color: '#16a34a', label: t('adminUi.platform.dash.depositsToday'), value: nf(todayRows.value.length), sub: across.value },
      { key: 'temp', icon: PhThermometer, color: '#dc2626', label: t('adminUi.platform.dash.avgTemp'), value: avgOf('temperature_c') == null ? '—' : nf(avgOf('temperature_c'), 1) + ' °C', sub: t('adminUi.platform.dash.rightNow') },
    ]))

const co2Week = computed(() => (overview.value?.carbon_7d || []).reduce((sum, day) => sum + (Number(day[props.type]) || 0), 0))
const typeAlerts = computed(() => (overview.value?.alerts || []).filter(alert => alert.machine_type === props.type))
const alertsFor = id => typeAlerts.value.filter(alert => alert.machine_id === id)

const statCards = computed(() => {
  const common = [
    { key: 'machines', icon: PhFactory, label: t('adminUi.platform.dash.machines'), value: nf((machines.value || []).length) },
    { key: 'co2', icon: PhLeaf, label: t('adminUi.platform.dash.co2Week'), value: nf(co2Week.value, 2), color: 'var(--carbon-accent)' },
    { key: 'fill', icon: PhGauge, label: t('adminUi.platform.dash.avgFill'), value: avgOf('fill_level') == null ? '—' : nf(avgOf('fill_level')) + '%' },
  ]
  const specific = isCompactor.value
    ? [
        { key: 'items', icon: PhListNumbers, label: t('adminUi.platform.fields.items_today'), value: nf(sumOf('items_today')) },
        { key: 'rejected', icon: PhProhibit, label: t('adminUi.platform.fields.non_target_today'), value: nf(sumOf('non_target_today')) },
        { key: 'cycles', icon: PhArrowsClockwise, label: t('adminUi.platform.fields.cycles_today'), value: nf(sumOf('cycles_today')) },
      ]
    : [
        { key: 'moisture', icon: PhDrop, label: t('adminUi.platform.dash.avgMoisture'), value: avgOf('moisture_pct') == null ? '—' : nf(avgOf('moisture_pct')) + '%' },
        { key: 'ph', icon: PhFlask, label: t('adminUi.platform.dash.avgPh'), value: avgOf('ph') == null ? '—' : nf(avgOf('ph'), 1) },
        { key: 'drum', icon: PhArrowsClockwise, label: t('adminUi.platform.fields.drum_rotations_today'), value: nf(sumOf('drum_rotations_today')) },
      ]
  return [...common, ...specific]
})

// 7-day CO2 bar chart for this machine type
const barData = computed(() => {
  const days = overview.value?.carbon_7d || []
  if (!days.length) return null
  return {
    labels: days.map(day => new Date(day.date + 'T00:00:00').toLocaleDateString(intlLocale(locale.value), { weekday: 'short' })),
    datasets: [{ label: t('adminUi.platform.carbonSaved'), data: days.map(day => Number(day[props.type]) || 0), backgroundColor: color.value, borderRadius: 4, maxBarThickness: 46 }],
  }
})
const barOptions = computed(() => ({
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, title: { display: true, text: t('adminUi.platform.dash.kgAxis') } }, x: { grid: { display: false } } },
}))

// Side chart: compactor = item mix of the latest batches, composter = average fill level
const sideTitle = computed(() => t(isCompactor.value ? 'adminUi.platform.dash.mixTitle' : 'adminUi.platform.dash.fillTitle'))
const sideSub = computed(() => (isCompactor.value
  ? t('adminUi.platform.dash.mixSub', { n: recent.value.length })
  : t('adminUi.platform.dash.fillSub')))
const donutData = computed(() => {
  if (isCompactor.value) {
    const plastic = recent.value.reduce((s, r) => s + (r.plastic_count || 0), 0)
    const aluminium = recent.value.reduce((s, r) => s + (r.aluminum_count || 0), 0)
    const rejected = recent.value.reduce((s, r) => s + (r.non_target_count || 0), 0)
    if (!plastic && !aluminium && !rejected) return null
    return {
      labels: [t('adminUi.platform.dash.plastic'), t('adminUi.platform.dash.aluminium'), t('adminUi.platform.dash.rejected')],
      datasets: [{ data: [plastic, aluminium, rejected], backgroundColor: ['#4e6ef2', '#f59e0b', '#ef4444'], borderWidth: 0 }],
    }
  }
  const fill = avgOf('fill_level')
  if (fill == null) return null
  return {
    labels: [t('adminUi.platform.dash.filled'), t('adminUi.platform.dash.free')],
    datasets: [{ data: [Math.round(fill), 100 - Math.round(fill)], backgroundColor: ['#0891b2', '#e2e8f0'], borderWidth: 0 }],
  }
})
const donutOptions = { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }

const fillColor = level => (level >= 90 ? '#ef4444' : level >= 80 ? '#f59e0b' : '#22c55e')

function shownValue(field, value) {
  if (value == null) return '—'
  if (field.kind === 'date') return new Date(value).toLocaleString(intlLocale(locale.value), { dateStyle: 'short', timeStyle: 'short' })
  if (field.kind === 'online') return t(value ? 'adminUi.onlineWord' : 'adminUi.offline')
  if (field.kind === 'sensor') return t('adminUi.platform.values.' + (value ? 'normal' : 'warning'))
  if (field.kind === 'range') return t('adminUi.platform.values.' + (value ? 'inside' : 'outside'))
  if (typeof value === 'string') return t('adminUi.platform.values.' + value)
  return nf(value, 2) + (field.unit || '')
}
</script>

<style scoped>
/* Same look as the Ultra RVM dashboard in AdminView (those rules are scoped there). */
.pmd-loading { display: flex; justify-content: center; padding: 60px 0; }
.pmd-empty p { margin: 0; color: var(--text-muted); }
.overview-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 16px; }
.overview-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 20px 22px; }
.ov-icon { font-size: 36px; margin-bottom: 10px; display: block; }
.ov-svg { width: 36px; height: 36px; stroke-width: 2; margin-bottom: 10px; display: block; }
.ov-label { font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: .06em; margin-bottom: 8px; text-transform: uppercase; }
.ov-count { font-size: 36px; font-weight: 800; color: var(--text-primary); line-height: 1; margin-bottom: 4px; }
.ov-sub { font-size: 12px; color: var(--text-muted); }
.stats-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 12px; margin-bottom: 16px; }
.stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius, 10px); padding: 14px; display: flex; align-items: center; gap: 12px; min-width: 0; }
.stat-icon { font-size: 24px; color: var(--accent-blue); flex-shrink: 0; }
.stat-value { font-size: 22px; font-weight: 800; color: var(--text-primary); }
.stat-label { font-size: 12px; color: var(--text-muted); }
.machine-status-card { align-items: flex-start; }
.ms-icon { font-size: 26px; margin-bottom: 6px; color: var(--accent-blue); }
.ms-label { font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: .06em; margin-bottom: 6px; text-transform: uppercase; }
.ms-status { font-size: 20px; font-weight: 900; letter-spacing: .04em; margin-bottom: 4px; }
.ms-online { color: #00e5a0; }
.ms-offline { color: #ef4444; }
.ms-sub { font-size: 12px; color: var(--text-secondary); }
.section-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius, 10px); padding: 16px; margin-bottom: 16px; }
.card-title-bar { font-size: 12px; font-weight: 700; color: var(--text-muted); letter-spacing: .8px; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }
.title-sq { width: 10px; height: 10px; border-radius: 2px; background: var(--accent-blue); flex-shrink: 0; }
.title-sq-yellow { background: var(--accent-yellow); }
.charts-row { display: grid; grid-template-columns: 1fr 340px; gap: 16px; margin-bottom: 16px; }
.chart-card-main, .chart-card-side { margin-bottom: 0; min-width: 0; }
.card-title-bar.chart-title { margin-bottom: 4px; }
.chart-sub { margin: 0 0 12px; font-size: 12px; color: var(--text-muted); }
.chart-wrap { height: 260px; position: relative; overflow: hidden; min-width: 0; }
.donut-wrap { display: flex; align-items: center; justify-content: center; }
.chart-empty { color: var(--text-muted); font-size: 13px; text-align: center; padding-top: 80px; }
.platform-admin-sim { display: inline-block; padding: 2px 7px; border-radius: 99px; background: #fef3c7; color: #854d0e; font-size: .7rem; font-weight: 750; letter-spacing: 0; text-transform: none; }
.platform-admin-muted { color: var(--text-muted); font-size: .85rem; }

/* Machine cards */
.pmd-machines { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr)); gap: 12px; }
.pmd-machine { border: 1px solid var(--border); border-radius: 10px; padding: 14px; background: var(--bg-primary); display: flex; flex-direction: column; gap: 10px; min-width: 0; }
.pmd-machine-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
.pmd-machine-head strong { display: block; font-size: 15px; }
.pmd-machine-head small { color: var(--text-muted); font-size: 12px; }
.pmd-status { padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.pmd-status-available { background: rgba(34,197,94,.14); color: #15803d; }
.pmd-status-almost_full { background: rgba(245,158,11,.16); color: #b45309; }
.pmd-status-full { background: rgba(239,68,68,.14); color: #b91c1c; }
.pmd-status-offline, .pmd-status-maintenance { background: var(--bg-hover); color: var(--text-muted); }
.pmd-status-fault { background: rgba(239,68,68,.14); color: #b91c1c; }
.pmd-fill { height: 8px; border-radius: 99px; background: var(--border); overflow: hidden; }
.pmd-fill span { display: block; height: 100%; border-radius: inherit; }
.pmd-readings { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 12px; margin: 0; }
.pmd-readings dt { color: var(--text-muted); font-size: 11.5px; }
.pmd-readings dd { margin: 1px 0 0; font-weight: 700; font-size: 13.5px; }
.pmd-alerts { list-style: none; margin: 0; padding: 8px 10px; border-radius: 8px; background: rgba(239,68,68,.08); color: #b91c1c; font-size: 12.5px; display: flex; flex-direction: column; gap: 4px; }
.pmd-alerts li { display: flex; align-items: center; gap: 6px; }
.pmd-ideal { margin: 12px 0 0; }
.pmd-slot { margin-bottom: 16px; }
[data-theme="dark"] .pmd-status-available { color: #4ade80; }
[data-theme="dark"] .pmd-alerts { color: #fca5a5; }

@media (max-width: 1280px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 900px) {
  .charts-row { grid-template-columns: 1fr; }
  .overview-cards { grid-template-columns: 1fr; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
