<template>
  <article class="location-card">
    <button class="location-toggle" type="button" :aria-expanded="expanded" @click="$emit('toggle')">
      <span class="location-heading">
        <span><PhMapPin :size="17" weight="bold" aria-hidden="true" /> {{ location.name }}</span>
        <PhCaretDown :class="{ turned: expanded }" :size="16" weight="bold" aria-hidden="true" />
      </span>
      <span v-for="machine in location.machines" :key="machine.id" class="location-machine">
        <span class="machine-name">
          <component :is="icons[machine.machine_type]" :size="17" weight="regular" aria-hidden="true" />
          {{ t('platform.machine.' + machine.machine_type) }}
          <span v-if="machine.is_demo" class="simulation-chip">{{ t('platform.simulated') }}</span>
        </span>
        <span class="status-pill" :class="'tone-' + pillTone(statusPill(machine))">{{ t('platform.status.' + statusPill(machine)) }}</span>
      </span>
    </button>

    <div v-if="expanded" class="location-details">
      <section v-for="machine in location.machines" :key="machine.id" class="machine-detail">
        <strong>{{ machine.name }}</strong>
        <span v-if="machine.is_demo" class="simulation-chip">{{ t('platform.simulated') }}</span>
        <div v-for="(level, bin) in machine.bins || { fill: machine.fill_level }" :key="bin" class="bin-level">
          <label :for="'level-' + machine.id + '-' + bin">{{ t(bin === 'fill' ? 'platform.fillLevel' : 'session.' + bin) }}</label>
          <progress :id="'level-' + machine.id + '-' + bin" :value="level" max="100" :class="'tone-' + levelTone(level)" />
          <span>{{ level }}%</span>
        </div>
      </section>
      <slot />
    </div>
  </article>
</template>

<script setup>
import { PhCaretDown, PhCube, PhLeaf, PhMapPin, PhRecycle } from '@phosphor-icons/vue'
import { useI18n } from 'vue-i18n'
import { pillTone, statusPill } from '@/utils/platform/machineStatus'

defineProps({ location: { type: Object, required: true }, expanded: Boolean })
defineEmits(['toggle'])
const { t } = useI18n()
const icons = { rvm: PhRecycle, compactor: PhCube, composter: PhLeaf }
const levelTone = level => level >= 90 ? 'bad' : level >= 80 ? 'warn' : 'ok'
</script>

<style scoped>
.location-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.location-toggle { appearance: none; display: block; width: 100%; border: 0; background: transparent; color: var(--text-primary); text-align: start; padding: 12px 14px; cursor: pointer; }
.location-toggle:focus-visible { outline-offset: -3px; }
.location-heading, .location-machine { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.location-heading { font-weight: 750; margin-bottom: 6px; }
.location-heading > span, .machine-name { display: flex; align-items: center; flex-wrap: wrap; gap: 5px; }
.location-heading > span > svg, .machine-name > svg { color: var(--accent-green); flex-shrink: 0; }
.location-machine { font-size: .8125rem; padding: 7px 0; border-top: 1px dashed var(--border); }
.turned { transform: rotate(180deg); }
.status-pill, .simulation-chip { border-radius: 99px; padding: 2px 7px; font-size: .72rem; font-weight: 700; }
.status-pill { text-align: center; }
.simulation-chip, .tone-warn { background: #fef3c7; color: #854d0e; }
.tone-ok { background: #dcfce7; color: #166534; }
.tone-bad { background: #fee2e2; color: #991b1b; }
.tone-muted { background: var(--bg-hover); color: var(--text-muted); }
.location-details { padding: 0 14px 12px; }
.machine-detail { padding: 10px 0; border-top: 1px solid var(--border); font-size: .8125rem; }
.machine-detail > strong { margin-inline-end: 6px; }
.bin-level { display: grid; grid-template-columns: minmax(62px, 30%) 1fr 36px; gap: 6px; align-items: center; margin-top: 5px; }
.bin-level > span { text-align: end; font-variant-numeric: tabular-nums; }
progress { width: 100%; height: .55rem; accent-color: var(--accent-green); }
progress.tone-warn { accent-color: #b45309; }
progress.tone-bad { accent-color: #b91c1c; }
</style>
