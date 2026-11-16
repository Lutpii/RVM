<template>
  <section class="platform-admin-card">
    <h3>{{ t('adminUi.platform.simSettings') }} <span class="platform-admin-sim">{{ t('platform.simulated') }}</span></h3>
    <p class="platform-admin-muted">{{ t('adminUi.platform.factorReference') }}</p>
    <form v-if="settings" class="platform-admin-settings-form" @submit.prevent="save">
      <label>
        {{ t('adminUi.platform.pointsPerKg') }}
        <input v-model.number="settings.composter_points_per_kg" type="number" min="1" max="1000" step="1" required />
      </label>
      <label>
        {{ t('adminUi.platform.co2PerKg') }}
        <input v-model.number="settings.composter_kg_co2_per_kg" type="number" min="0.01" max="5" step="any" required />
      </label>
      <button :disabled="saving">{{ t(saving ? 'adminUi.saving' : 'adminUi.save') }}</button>
    </form>
    <p v-else-if="!error" role="status">{{ t('adminUi.platform.load') }}</p>
    <p v-if="error" role="alert">{{ t(error) }}</p>
    <p v-if="saved" role="status">{{ t('adminUi.platform.settingsSaved') }}</p>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const { t } = useI18n()
const settings = ref(null)
const error = ref('')
const saving = ref(false)
const saved = ref(false)

onMounted(async () => {
  try {
    const { data } = await api.get('/admin/platform/settings')
    settings.value = data.settings
  } catch {
    error.value = 'adminUi.platform.failed'
  }
})

async function save() {
  if (saving.value) return
  saved.value = false
  error.value = ''
  saving.value = true
  try {
    const { data } = await api.put('/admin/platform/settings', settings.value)
    settings.value = data.settings
    saved.value = true
  } catch {
    error.value = 'adminUi.platform.saveFailed'
  } finally {
    saving.value = false
  }
}
</script>
