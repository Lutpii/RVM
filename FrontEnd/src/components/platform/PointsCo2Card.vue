<template>
  <section class="platform-balance">
    <div class="balance-label">{{ t('dashboard.platform.totalPoints') }}</div>
    <strong class="balance-points">{{ number(points, 0) }}</strong>
    <div class="balance-carbon">
      <PhGlobe :size="17" weight="bold" aria-hidden="true" />
      <span>{{ number(carbon, 2) }} kg · {{ t('settings.carbonSaved') }}</span>
      <CarbonSourcesNote />
    </div>
  </section>
</template>

<script setup>
import { PhGlobe } from '@phosphor-icons/vue'
import { useI18n } from 'vue-i18n'
import { intlLocale } from '@/composables/useLanguage'
import CarbonSourcesNote from './CarbonSourcesNote.vue'

defineProps({ points: { type: Number, default: 0 }, carbon: { type: Number, default: 0 } })
const { t, locale } = useI18n()

function number(value, digits) {
  return new Intl.NumberFormat(intlLocale(locale.value), {
    minimumFractionDigits: digits, maximumFractionDigits: digits,
  }).format(value)
}
</script>

<style scoped>
.platform-balance { padding: 17px 20px 12px; border-radius: var(--radius); background: #16734e; color: #fff; }
.balance-label { font-size: .875rem; font-weight: 650; }
.balance-points { display: block; margin: 3px 0 6px; font-size: clamp(1.8rem, 5vw, 2.6rem); line-height: 1.1; font-variant-numeric: tabular-nums; }
.balance-carbon { display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px; font-size: .875rem; }
.balance-carbon > svg { align-self: center; }
</style>
