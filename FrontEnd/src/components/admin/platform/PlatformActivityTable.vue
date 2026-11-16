<template>
  <section class="platform-admin-card platform-transaction-table">
    <h3>{{ t('platform.machine.' + type) }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></h3>
    <p v-if="error" role="alert">{{ t('adminUi.platform.failed') }}</p>
    <p v-else-if="pending" role="status">{{ t('adminUi.platform.load') }}</p>
    <template v-else>
      <PlatformRows :type="type" :rows="activity?.data || []" />
      <AdminPagination v-if="activity?.last_page > 1" :current-page="page" :last-page="activity.last_page"
        :total="activity.total" :per-page="20" :per-page-options="[20]" @change-page="page = $event" />
    </template>
  </section>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import PlatformRows from './PlatformRows.vue'
import api from '@/services/api'

const props = defineProps({ type: { type: String, required: true }, scope: String, refresh: Number })
const { t } = useI18n()
const page = ref(1)
const activity = ref(null)
const pending = ref(false)
const error = ref(false)

watch(() => [props.type, props.scope], () => { page.value = 1 })
watch(() => [props.type, props.scope, props.refresh, page.value], async ([type, scope, , current], previous, onCleanup) => {
  const controller = new AbortController()
  onCleanup(() => controller.abort())
  error.value = false
  pending.value = false
  if (scope === 'real') {
    activity.value = null
    return
  }
  pending.value = true
  try {
    const { data } = await api.get('/admin/platform/activity', { params: { type, page: current }, signal: controller.signal })
    if (!controller.signal.aborted) activity.value = data.activity
  } catch {
    if (!controller.signal.aborted) error.value = true
  } finally {
    if (!controller.signal.aborted) pending.value = false
  }
}, { immediate: true })
</script>
