<template>
  <details class="carbon-sources" @toggle="load">
    <summary :aria-label="t('platform.sources.title')" :title="t('platform.sources.title')">
      <PhInfo :size="20" weight="bold" aria-hidden="true" />
    </summary>
    <div class="source-note">
      <strong>{{ t('platform.sources.title') }}</strong>
      <p v-if="loading" role="status">{{ t('platform.sources.loading') }}</p>
      <p v-else-if="error" role="status">{{ t('platform.sources.error') }}</p>
      <dl v-else>
        <template v-for="(source, material) in sources" :key="material">
          <dt>{{ t(material === 'food_waste' ? 'platform.sources.food_waste' : 'session.' + material) }}</dt>
          <dd>{{ formatSourceLine(displayedSource(material, source), t) }}</dd>
        </template>
      </dl>
    </div>
  </details>
</template>

<script setup>
import { ref } from 'vue'
import { PhInfo } from '@phosphor-icons/vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { formatSourceLine } from '@/utils/platform/carbonSources'

const { t } = useI18n()
const sources = ref(null)
const loading = ref(false)
const error = ref(false)

function displayedSource(material, source) {
  if (material === 'food_waste' && source.kg_co2e_per_kg !== 0.44) {
    return { ...source, reference: t('platform.sources.adjusted') }
  }
  return source
}

async function load(event) {
  if (!event.currentTarget.open || sources.value || loading.value) return

  loading.value = true
  error.value = false
  try {
    const { data } = await api.get('/carbon/sources')
    sources.value = data.sources
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.carbon-sources { display: inline-block; }
summary { display: inline-grid; place-items: center; min-width: 36px; min-height: 36px; cursor: pointer; list-style: none; }
summary::-webkit-details-marker { display: none; }
.source-note { margin-top: 6px; border: 1px solid var(--border); border-radius: 10px; padding: 12px; color: var(--text-primary); background: var(--bg-card); text-align: start; font-size: .875rem; line-height: 1.45; }
dl { margin: 8px 0 0; }
dt { font-weight: 650; }
dd { margin: 0 0 8px; color: var(--text-muted); }
dd:last-child { margin-bottom: 0; }
p { margin-bottom: 0; }
</style>
