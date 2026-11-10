<!-- FrontEnd/src/components/admin/MaintenanceHistoryModal.vue -->
<!-- Service history for one machine (A06): open a record (technician + issue),
     close it with the action taken. Not the kiosk "maintenance shutdown". -->
<template>
  <div class="mh-overlay" @click.self="$emit('close')">
    <div class="mh-modal" role="dialog" aria-modal="true" :aria-label="'Service history: ' + machine.name">
      <button class="mh-close" aria-label="Close" @click="$emit('close')"><PhX weight="bold" aria-hidden="true" /></button>
      <h3 class="mh-title">Service History</h3>
      <p class="mh-sub">{{ machine.name }} · {{ machine.machine_code }}</p>

      <form class="mh-form" @submit.prevent="openRecord">
        <div class="mh-row">
          <label class="mh-field">
            <span>Technician *</span>
            <input v-model.trim="form.technician_name" type="text" maxlength="100" placeholder="e.g. Ali (DSME)" />
          </label>
        </div>
        <label class="mh-field">
          <span>Issue *</span>
          <textarea v-model.trim="form.issue" rows="2" maxlength="2000" placeholder="What is wrong with the machine?"></textarea>
        </label>
        <p v-if="formError" class="mh-error">{{ formError }}</p>
        <button class="mh-btn mh-primary" type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Open record' }}</button>
      </form>

      <div v-if="loading" class="mh-loading"><div class="mh-spinner"></div></div>
      <p v-else-if="loadError" class="mh-error">{{ loadError }}</p>
      <p v-else-if="!records.length" class="mh-empty">No maintenance records for this machine yet.</p>
      <ul v-else class="mh-list">
        <li v-for="r in records" :key="r.id" class="mh-item">
          <div class="mh-item-head">
            <strong>{{ r.technician_name }}</strong>
            <span :class="['mh-status', 'mh-' + r.status]">{{ r.status === 'open' ? 'OPEN' : 'CLOSED' }}</span>
          </div>
          <p class="mh-issue">{{ r.issue }}</p>
          <p class="mh-meta">Opened {{ fmt(r.opened_at) }}<template v-if="r.opened_by_name"> by {{ r.opened_by_name }}</template></p>
          <template v-if="r.status === 'closed'">
            <p class="mh-action"><span>Action taken:</span> {{ r.action_taken }}</p>
            <p class="mh-meta">Closed {{ fmt(r.closed_at) }}<template v-if="r.closed_by_name"> by {{ r.closed_by_name }}</template></p>
          </template>
          <div v-else-if="closingId === r.id" class="mh-close-form">
            <textarea v-model.trim="actionTaken" rows="2" maxlength="2000" placeholder="What was done to fix it?"></textarea>
            <p v-if="closeError" class="mh-error">{{ closeError }}</p>
            <div class="mh-close-actions">
              <button class="mh-btn" type="button" @click="cancelClose">Cancel</button>
              <button class="mh-btn mh-primary" type="button" :disabled="saving" @click="closeRecord(r)">Close record</button>
            </div>
          </div>
          <button v-else class="mh-btn" type="button" @click="startClose(r)">Close…</button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { PhX } from '@phosphor-icons/vue'
import api from '@/services/api'

const props = defineProps({
  machine: { type: Object, required: true },
})
defineEmits(['close'])

const records   = ref([])
const loading   = ref(true)
const loadError = ref('')
const saving    = ref(false)
const form      = reactive({ technician_name: '', issue: '' })
const formError = ref('')
const closingId   = ref(null)
const actionTaken = ref('')
const closeError  = ref('')

function fmt(ts) {
  if (!ts) return '—'
  const d = new Date(ts)
  if (Number.isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
    + ', ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

function errorMessage(err, fallback) {
  const data = err?.response?.data
  if (data?.errors) return Object.values(data.errors).flat()[0] || fallback
  return data?.message || fallback
}

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const res = await api.get(`/admin/machines/${props.machine.id}/maintenance-records`)
    records.value = res.data.records || []
  } catch (err) {
    loadError.value = errorMessage(err, 'Maintenance records could not be loaded.')
  } finally {
    loading.value = false
  }
}

async function openRecord() {
  formError.value = ''
  if (!form.technician_name || !form.issue) {
    formError.value = 'Technician and issue are required.'
    return
  }
  saving.value = true
  try {
    const res = await api.post(`/admin/machines/${props.machine.id}/maintenance-records`, { ...form })
    records.value = [res.data.record, ...records.value]
    form.technician_name = ''
    form.issue = ''
  } catch (err) {
    formError.value = errorMessage(err, 'The record could not be saved.')
  } finally {
    saving.value = false
  }
}

function startClose(record) {
  closingId.value = record.id
  actionTaken.value = ''
  closeError.value = ''
}
function cancelClose() { closingId.value = null }

async function closeRecord(record) {
  closeError.value = ''
  if (!actionTaken.value) {
    closeError.value = 'Describe the action taken.'
    return
  }
  saving.value = true
  try {
    const res = await api.patch(`/admin/maintenance-records/${record.id}/close`, { action_taken: actionTaken.value })
    records.value = records.value.map((r) => (r.id === record.id ? res.data.record : r))
    closingId.value = null
  } catch (err) {
    closeError.value = errorMessage(err, 'The record could not be closed.')
    if (err?.response?.status === 409) load()
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.mh-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 300;
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
.mh-modal {
  position: relative; background: var(--bg-card); color: var(--text-primary);
  border-radius: var(--radius, 12px); width: 100%; max-width: 520px;
  max-height: calc(100dvh - 32px); overflow-y: auto; padding: 20px;
  display: flex; flex-direction: column; gap: 12px;
}
.mh-close { position: absolute; top: 12px; right: 12px; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px; }
.mh-title { margin: 0; font-size: 17px; }
.mh-sub { margin: -8px 0 0; font-size: 12px; color: var(--text-muted); }
.mh-form { display: flex; flex-direction: column; gap: 8px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; }
.mh-field { display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--text-secondary); }
.mh-field input, .mh-field textarea, .mh-close-form textarea {
  font: inherit; font-size: 13px; padding: 8px 10px; border-radius: 8px;
  border: 1px solid var(--border); background: var(--bg-input, var(--bg-card)); color: var(--text-primary); resize: vertical;
}
.mh-btn {
  align-self: flex-start; padding: 7px 14px; border-radius: 8px; font-size: 13px; cursor: pointer;
  border: 1px solid var(--border); background: transparent; color: var(--text-primary);
}
.mh-primary { background: var(--accent-blue); border-color: var(--accent-blue); color: #fff; }
.mh-btn:disabled { opacity: 0.6; cursor: default; }
.mh-error { margin: 0; font-size: 12px; color: var(--accent-red); font-weight: 600; }
.mh-empty { margin: 0; font-size: 13px; color: var(--text-muted); }
.mh-loading { display: flex; justify-content: center; padding: 12px; }
.mh-spinner { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: mh-spin 0.7s linear infinite; }
@keyframes mh-spin { to { transform: rotate(360deg); } }
.mh-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.mh-item { border: 1px solid var(--border); border-radius: 8px; padding: 10px 12px; display: flex; flex-direction: column; gap: 4px; }
.mh-item-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.mh-status { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
.mh-open { background: rgba(239, 68, 68, 0.12); color: var(--accent-red); }
.mh-closed { background: rgba(16, 185, 129, 0.12); color: var(--accent-green); }
.mh-issue { margin: 0; font-size: 13px; white-space: pre-wrap; }
.mh-action { margin: 0; font-size: 13px; white-space: pre-wrap; }
.mh-action span { color: var(--text-muted); }
.mh-meta { margin: 0; font-size: 11px; color: var(--text-muted); }
.mh-close-form { display: flex; flex-direction: column; gap: 6px; margin-top: 4px; }
.mh-close-actions { display: flex; gap: 8px; }
</style>
