<template>
  <div class="platform-admin platform-monitor">
    <section class="platform-admin-card">
      <h3>{{ t('platform.machine.' + type) }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></h3>
      <p v-if="listError" role="alert">{{ t('adminUi.platform.failed') }}</p>
      <p v-else-if="machines === null" role="status">{{ t('adminUi.platform.load') }}</p>
      <p v-else-if="!machines.length">{{ t('adminUi.noMachines') }}</p>
      <div v-else class="platform-monitor-list">
        <button v-for="machine in machines" :key="machine.id" type="button" class="platform-monitor-link"
          :aria-pressed="selected === machine.id" @click="selected = machine.id">
          <strong>{{ machine.name }}</strong>
          <small>{{ machine.location || t('adminUi.noLocation') }}</small>
          {{ t('platform.status.' + machine.status) }}
        </button>
      </div>
    </section>

    <p v-if="detailError" role="alert">{{ t('adminUi.platform.failed') }}</p>
    <p v-else-if="selected && !detail" role="status">{{ t('adminUi.platform.load') }}</p>
    <template v-else-if="detail">
      <section class="platform-admin-card platform-monitor-detail">
        <h3>{{ detail.machine.name }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></h3>
        <dl class="platform-reading-grid">
          <div v-for="field in fields" :key="field.key" class="platform-reading">
            <dt>{{ t('adminUi.platform.fields.' + field.key) }}</dt>
            <dd>{{ shownValue(field, detail.telemetry[field.key]) }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></dd>
          </div>
        </dl>
        <p v-if="type === 'composter'" class="platform-admin-muted">{{ t('adminUi.platform.ideal') }}</p>
      </section>
      <section class="platform-admin-card">
        <h3>{{ t('adminUi.platform.alerts') }}</h3>
        <p v-if="!detail.alerts.length">{{ t('adminUi.platform.noAlerts') }}</p>
        <ul v-else class="platform-alerts">
          <li v-for="alert in detail.alerts" :key="alert.code">{{ t('adminUi.platform.alert.' + alert.code) }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></li>
        </ul>
      </section>
    </template>
    <slot />
    <section v-if="detail && !detailError" class="platform-admin-card">
      <h3>{{ t('adminUi.platform.recentActivity') }}</h3>
      <PlatformRows :type="type" :rows="detail.recent" />
    </section>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { intlLocale } from '@/composables/useLanguage'
import api from '@/services/api'
import PlatformRows from './PlatformRows.vue'

const props = defineProps({ type: { type: String, required: true }, fields: { type: Array, required: true }, scope: String, refresh: Number })
const { t, locale } = useI18n()
const machines = ref(null)
const selected = ref(null)
const detail = ref(null)
const listError = ref(false)
const detailError = ref(false)

function shownValue(field, value) {
  if (value == null) return '—'
  if (field.kind === 'date') return new Date(value).toLocaleString(intlLocale(locale.value), { dateStyle: 'short', timeStyle: 'short' })
  if (field.kind === 'online') return t(value ? 'adminUi.onlineWord' : 'adminUi.offline')
  if (field.kind === 'sensor') return t('adminUi.platform.values.' + (value ? 'normal' : 'warning'))
  if (field.kind === 'range') return t('adminUi.platform.values.' + (value ? 'inside' : 'outside'))
  if (typeof value === 'string') return t('adminUi.platform.values.' + value)

  return new Intl.NumberFormat(intlLocale(locale.value), { maximumFractionDigits: 2 }).format(value) + (field.unit || '')
}

watch(() => [props.scope, props.refresh], async ([scope], previous, onCleanup) => {
  const controller = new AbortController()
  onCleanup(() => controller.abort())
  listError.value = false
  if (scope === 'real') {
    selected.value = null
    machines.value = []
    return
  }

  try {
    const { data } = await api.get('/admin/platform/machines', { params: { type: props.type }, signal: controller.signal })
    if (!controller.signal.aborted) {
      machines.value = data.machines
      if (!data.machines.some(machine => machine.id === selected.value)) selected.value = null
    }
  } catch {
    if (!controller.signal.aborted) listError.value = true
  }
}, { immediate: true })

watch(() => [selected.value, props.refresh], async ([id], previous, onCleanup) => {
  const controller = new AbortController()
  onCleanup(() => controller.abort())
  detailError.value = false
  if (previous?.[0] !== id) detail.value = null
  if (!id) return

  try {
    const { data } = await api.get('/admin/platform/machines/' + id, { signal: controller.signal })
    if (!controller.signal.aborted) detail.value = data
  } catch {
    if (!controller.signal.aborted) detailError.value = true
  }
})
</script>
