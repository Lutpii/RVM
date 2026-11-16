<template>
  <div class="platform-admin platform-overview">
    <p v-if="error" role="alert">{{ t('adminUi.platform.failed') }}</p>
    <p v-else-if="!overview" role="status">{{ t('adminUi.platform.load') }}</p>
    <template v-else>
      <div class="platform-admin-grid">
        <div v-for="row in kpis" :key="row.key" class="platform-admin-card platform-admin-kpi">
          <span>{{ t('adminUi.platform.' + row.key) }}</span>
          <strong>{{ row.value }}</strong>
        </div>
      </div>
      <div class="platform-admin-grid">
        <div v-for="type in types" :key="type" class="platform-admin-card platform-admin-kpi">
          <span>{{ t('platform.machine.' + type) }}</span>
          <strong>{{ overview.kpis.machines_active[type] }} / {{ overview.kpis.machines_total[type] }}</strong>
          <span v-if="type !== 'rvm'" class="platform-admin-sim">{{ t('platform.simulated') }}</span>
        </div>
      </div>
      <section class="platform-admin-card">
        <h3>{{ t('adminUi.platform.carbon7d') }}</h3>
        <div class="platform-admin-chart"><Bar :data="chart" :options="options" :aria-label="t('adminUi.platform.carbon7d')" /></div>
      </section>
      <section class="platform-admin-card">
        <h3>{{ t('adminUi.platform.alerts') }}</h3>
        <p v-if="!overview.alerts.length">{{ t('adminUi.platform.noAlerts') }}</p>
        <ul v-else class="platform-alerts">
          <li v-for="alert in overview.alerts" :key="alert.machine_id + '-' + alert.code">
            <strong>{{ alert.machine_name }}</strong> {{ t('adminUi.platform.alert.' + alert.code) }}
            <span v-if="alert.machine_type !== 'rvm'" class="platform-admin-sim">{{ t('platform.simulated') }}</span>
          </li>
        </ul>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Bar } from 'vue-chartjs'
import { intlLocale } from '@/composables/useLanguage'
import api from '@/services/api'

const props = defineProps({ scope: String, refresh: Number })
const { t, locale } = useI18n()
const overview = ref(null)
const error = ref(false)
const types = ['rvm', 'compactor', 'composter']
const colors = ['#16824f', '#d97706', '#0891b2']
const options = { animation: false, responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } }

const format = value => new Intl.NumberFormat(intlLocale(locale.value), { maximumFractionDigits: 2 }).format(value)
const kpis = computed(() => {
  const summary = overview.value.kpis
  return [
    { key: 'pointsIssued', value: format(summary.points_issued) },
    { key: 'carbonSaved', value: format(summary.carbon_saved_kg) + ' kg' },
    { key: 'activeMachines', value: format(Object.values(summary.machines_active).reduce((total, count) => total + count, 0)) },
    { key: 'alerts', value: format(summary.alerts) },
  ]
})
const chart = computed(() => ({
  labels: overview.value.carbon_7d.map(row => new Date(row.date + 'T00:00:00').toLocaleDateString(intlLocale(locale.value), { day: 'numeric', month: 'short' })),
  datasets: types.map((type, index) => ({
    label: t('platform.machine.' + type) + (type === 'rvm' ? '' : ' · ' + t('platform.simulated')),
    data: overview.value.carbon_7d.map(row => row[type]),
    backgroundColor: colors[index], borderRadius: 4,
  })),
}))

watch(() => [props.scope, props.refresh], async ([scope], previous, onCleanup) => {
  const controller = new AbortController()
  onCleanup(() => controller.abort())
  if (previous && scope !== previous[0]) overview.value = null
  error.value = false
  try {
    const { data } = await api.get('/admin/platform/overview', { params: { scope }, signal: controller.signal })
    if (!controller.signal.aborted) overview.value = data
  } catch {
    if (!controller.signal.aborted) error.value = true
  }
}, { immediate: true })
</script>
