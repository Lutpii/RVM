<template>
  <!-- Paste a Google Maps link (Share, Copy link); the backend reads the pin's
       coordinates and place name, the parent form fills its fields from them. -->
  <div class="maps-link">
    <label :for="id">{{ t('adminUi.mapsLink') }}</label>
    <div class="maps-link-row">
      <input :id="id" v-model="url" type="url" inputmode="url" :placeholder="t('adminUi.mapsLinkPlaceholder')"
        @paste="onPaste" @keydown.enter.prevent="resolve" />
      <button type="button" class="maps-link-btn" :disabled="!url.trim() || busy" @click="resolve">
        {{ busy ? t('adminUi.mapsLinkReading') : t('adminUi.mapsLinkUse') }}
      </button>
    </div>
    <p v-if="error" class="form-error">{{ error }}</p>
    <p v-else-if="found" class="field-hint">{{ t('adminUi.mapsLinkFound', found) }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

defineProps({ id: { type: String, required: true } })
const emit = defineEmits(['resolved'])
const { t } = useI18n()

const url = ref('')
const busy = ref(false)
const error = ref('')
const found = ref(null)

async function resolve() {
  if (!url.value.trim() || busy.value) return
  busy.value = true
  error.value = ''
  found.value = null
  try {
    const { data } = await api.post('/admin/maps/resolve', { url: url.value.trim() })
    found.value = { lat: data.latitude, lng: data.longitude }
    emit('resolved', { latitude: data.latitude, longitude: data.longitude, place: data.place })
  } catch (e) {
    error.value = e.response?.data?.message || t('adminUi.mapsLinkFailed')
  } finally {
    busy.value = false
  }
}

// Pasting is the normal way in: read the link right away.
function onPaste(event) {
  const text = event.clipboardData?.getData('text')?.trim()
  if (!text) return
  event.preventDefault()
  url.value = text
  resolve()
}
</script>

<style scoped>
/* Same look as the admin form fields around it (AdminView's styles are scoped
   and do not reach inside this component). */
.maps-link { margin-bottom: 14px; }
.maps-link label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 7px; }
.maps-link-row { display: flex; gap: 8px; }
.maps-link-row input {
  flex: 1; min-width: 0; min-height: 44px; padding: 10px 13px; box-sizing: border-box;
  background: var(--bg-hover); border: 1px solid var(--border); border-radius: 8px;
  color: var(--text-primary); font: inherit; font-size: 14px; outline: none;
}
.maps-link-row input:focus { border-color: var(--accent-blue); }
.maps-link-btn {
  flex-shrink: 0; min-height: 44px; padding: 0 14px; border-radius: 8px;
  border: 1px solid var(--accent-blue); background: transparent; color: var(--accent-blue);
  font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;
}
.maps-link-btn:hover:not(:disabled) { background: rgba(78, 110, 242, 0.08); }
.maps-link-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.field-hint { margin: 6px 0 0; font-size: 12px; color: var(--text-muted); }
.form-error { margin: 6px 0 0; font-size: 12px; color: var(--accent-red); }
</style>
