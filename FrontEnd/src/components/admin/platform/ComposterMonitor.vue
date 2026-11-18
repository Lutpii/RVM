<template>
  <PlatformMachineDashboard type="composter" :fields="fields">
    <PlatformSettingsCard />
  </PlatformMachineDashboard>
</template>

<script setup>
import PlatformMachineDashboard from './PlatformMachineDashboard.vue'
import PlatformSettingsCard from './PlatformSettingsCard.vue'

const fields = [
  { key: 'fill_level', unit: '%' },
  { key: 'temperature_c', unit: ' °C' },
  { key: 'moisture_pct', unit: '%' },
  { key: 'ph' },
  { key: 'co2_level' },
  { key: 'nh3_level' },
  { key: 'drum_rotations_today' },
  { key: 'kg_today', unit: ' kg' },
  { key: 'phase' },
  { key: 'days_to_maturity' },
  { key: 'in_ideal_range', kind: 'range' },
]
</script>
