<template>
  <!-- One card per station name ("RVM UMPSA Gambang"); each place it has
       (Main Lobby, Library, ...) is a section inside that opens on tap. -->
  <article class="station-card">
    <header class="station-head">
      <h4 class="station-name">{{ station.name }}</h4>
      <span class="station-count">{{ t('platform.placeCount', { n: station.places.length }, station.places.length) }}</span>
    </header>
    <LocationCard v-for="place in station.places" :key="place.id" :location="place"
      :expanded="expandedId === place.id" @toggle="$emit('toggle', place)">
      <slot :place="place" />
    </LocationCard>
  </article>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import LocationCard from './LocationCard.vue'

defineProps({ station: { type: Object, required: true }, expandedId: { type: String, default: null } })
defineEmits(['toggle'])
const { t } = useI18n()
</script>

<style scoped>
.station-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 12px 4px; }.station-head { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin: 0 2px 8px; }
.station-name { margin: 0; font-size: 1rem; font-weight: 750; color: var(--text-primary); }
.station-count { flex-shrink: 0; font-size: .75rem; font-weight: 600; color: var(--text-muted); }
</style>
