<template>
  <div v-if="rows.length" class="platform-admin-table-wrap">
    <table class="platform-admin-table">
      <thead><tr>
        <th scope="col">{{ t('adminUi.platform.date') }}</th>
        <th scope="col">{{ t('adminUi.navUsers') }}</th>
        <th scope="col">{{ t('adminUi.navMachines') }}</th>
        <th scope="col">{{ t(type === 'compactor' ? 'adminUi.platform.batchItems' : 'adminUi.platform.mass') }}</th>
        <th scope="col">{{ t('adminUi.platform.pointsIssued') }}</th>
        <th scope="col">{{ t('adminUi.platform.carbonSaved') }}</th>
      </tr></thead>
      <tbody>
        <tr v-for="row in rows" :key="row.id">
          <td>{{ date(row.created_at) }}</td>
          <td>{{ row.user_display_no == null ? '—' : formatUserId({ display_no: row.user_display_no }) }}</td>
          <td>{{ row.machine_name }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></td>
          <td>{{ number(type === 'compactor' ? row.plastic_count + row.aluminum_count : row.weight_kg) }}</td>
          <td>{{ number(row.points_earned) }}</td>
          <td>{{ number(row.carbon_kg) }} kg</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p v-else class="platform-admin-muted">{{ t('adminUi.platform.noActivity') }}</p>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { intlLocale } from '@/composables/useLanguage'
import { formatUserId } from '@/utils/admin/userCode'

defineProps({ rows: { type: Array, required: true }, type: { type: String, required: true } })
const { t, locale } = useI18n()
const date = value => new Date(value).toLocaleString(intlLocale(locale.value), { dateStyle: 'short', timeStyle: 'short' })
const number = value => new Intl.NumberFormat(intlLocale(locale.value), { maximumFractionDigits: 3 }).format(value)
</script>
