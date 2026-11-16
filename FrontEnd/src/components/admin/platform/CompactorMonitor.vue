<template>
  <MonitorPanel type="compactor" :fields="fields" />
</template>

<script setup>
import MonitorPanel from './MonitorPanel.vue'

const fields = [
  { key: 'fill_level', unit: '%' },
  { key: 'cycles_today' },
  { key: 'batches_today' },
  { key: 'items_today' },
  { key: 'non_target_today' },
  { key: 'last_emptied_at', kind: 'date' },
  { key: 'safety_ok', kind: 'sensor' },
  { key: 'smoke_ok', kind: 'sensor' },
  { key: 'online', kind: 'online' },
]
</script>
