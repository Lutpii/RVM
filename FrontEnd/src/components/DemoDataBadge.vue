<!-- FrontEnd/src/components/DemoDataBadge.vue -->
<!-- "Demonstration data (simulated)" pill. Shown only on deploys that run on
     generated demo data (VITE_DEMO_DATA_LABEL=true), so nobody mistakes the
     numbers for live machine readings. -->
<template>
  <span v-if="demoDataLabel" class="demo-badge" role="note">
    <PhFlask weight="bold" aria-hidden="true" />
    {{ $t('demo.badge') }}
  </span>
</template>

<script setup>
import { PhFlask } from '@phosphor-icons/vue'
import { demoDataLabel } from '@/utils/featureFlags'
</script>

<style scoped>
.demo-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 999px;
  background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;
  font-size: 11px; font-weight: 700; line-height: 1.4; white-space: nowrap;
}
.demo-badge svg { width: 12px; height: 12px; flex-shrink: 0; }
</style>
