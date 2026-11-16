<template>
  <section class="wrap-pad section platform-machines" aria-labelledby="platform-machines-title">
    <div class="wrap">
      <div class="section-head">
        <h2 id="platform-machines-title" class="section-title">{{ t('landing.platform.machinesTitle') }}</h2>
      </div>
      <ul class="machine-platform-grid">
        <li v-for="machine in machines" :key="machine.type" class="card machine-platform-card">
          <span class="machine-platform-icon"><component :is="machine.icon" :size="24" weight="bold" aria-hidden="true" /></span>
          <div>
            <h3>{{ t('platform.machine.' + machine.type) }}</h3>
            <span v-if="machine.type !== 'rvm'" class="platform-simulation">{{ t('platform.simulated') }}</span>
            <p>{{ t('landing.platform.accepts.' + machine.type) }}</p>
            <strong>{{ t('landing.platform.earns.' + machine.type) }}</strong>
          </div>
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
import { PhCube, PhLeaf, PhRecycle } from '@phosphor-icons/vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const machines = [
  { type: 'rvm', icon: PhRecycle },
  { type: 'compactor', icon: PhCube },
  { type: 'composter', icon: PhLeaf },
]
</script>

<style scoped>
.wrap { max-width: 1160px; margin: 0 auto; }
.section-head { margin-bottom: 18px; text-align: center; }
.section-title { margin: 0; font-size: clamp(1.45rem, 3vw, 2rem); font-weight: 800; letter-spacing: -.01em; }
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; min-width: 0; }
.machine-platform-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; padding: 0; margin: 0; list-style: none; }
.machine-platform-card { padding: 18px; display: flex; align-items: flex-start; gap: 12px; }
.machine-platform-icon { display: grid; place-items: center; width: 42px; height: 42px; flex: 0 0 42px; border-radius: 10px; background: #dcfce7; color: #166534; }
h3 { font-size: 1.05rem; margin: 0; }
p { color: var(--text-muted); margin: 7px 0; }
strong { font-size: .875rem; }
.platform-simulation { display: inline-block; margin-top: 3px; padding: 2px 6px; border-radius: 99px; background: #fef3c7; color: #854d0e; font-size: .7rem; font-weight: 700; }
@media (max-width: 730px) {
  .machine-platform-grid { grid-template-columns: 1fr; }
}
</style>
