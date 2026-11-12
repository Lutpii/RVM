<!-- FrontEnd/src/components/KioskDemoBanner.vue -->
<!-- Always-visible label on every kiosk screen while the kiosk demonstration
     mode is on (see utils/kioskDemo.js), so no visitor takes the simulated
     capture and AI decision for the live machine. -->
<template>
  <div class="kiosk-demo-banner" role="status">
    <PhFlask weight="bold" aria-hidden="true" />
    <span><strong>{{ $t('demo.kioskBannerTitle') }}</strong> · {{ $t('demo.kioskBannerText') }}</span>
  </div>
</template>

<script setup>
import { PhFlask } from '@phosphor-icons/vue'
</script>

<style scoped>
.kiosk-demo-banner {
  position: fixed; left: 12px; top: 12px; z-index: 9999;
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 12px; border-radius: 999px;
  background: #FEF3C7; color: #92400E; border: 1px solid #F59E0B;
  font-size: 12px; line-height: 1.3; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  pointer-events: none; max-width: calc(100vw - 140px);
}
.kiosk-demo-banner svg { width: 14px; height: 14px; flex-shrink: 0; }
.kiosk-demo-banner strong { font-weight: 800; letter-spacing: 0.3px; }
</style>
