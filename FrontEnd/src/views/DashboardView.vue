<template>
  <div class="dashboard-page">
    <!-- Points hero card -->
    <div class="points-hero">
      <div class="points-bg"></div>
      <p class="points-label">{{ $t('dashboard.totalPoints') }}</p>
      <div class="points-number">{{ auth.user?.total_points || 0 }}</div>
      <p class="points-sub"><PhRecycle class="points-sub-icon" weight="regular" /> {{ $t('dashboard.keepRecycling') }}</p>
      <p class="carbon-sub"><PhGlobe class="points-sub-icon" weight="regular" /> {{ $t('dashboard.carbonSaved', { value: (auth.user?.total_carbon_saved || 0).toFixed(2) }) }}</p>
    </div>

    <!-- Nearby machines map -->
    <div class="section-pad">
      <h3 class="section-title"><PhMapPin class="points-sub-icon" weight="regular" /> {{ $t('dashboard.nearbyMachines') }}</h3>

      <div class="map-placeholder" v-if="loadingMachines">
        <div class="map-loading">
          <div class="spinner-sm"></div>
          <span>{{ $t('dashboard.locating') }}</span>
        </div>
      </div>

      <div v-else class="machines-list">
        <div v-for="machine in machines" :key="machine.id" class="machine-card" @click="selectMachine(machine)">
          <div class="machine-header">
            <div class="machine-dot" :class="machine.status === 'active' ? 'dot-green' : 'dot-red'"></div>
            <div class="machine-info">
              <strong>{{ machine.name }}</strong>
              <span><PhMapPin class="points-sub-icon" weight="regular" /> {{ machine.location_name }}</span>
            </div>
            <span class="machine-code">{{ machine.machine_code }}</span>
          </div>

          <!-- Bin levels -->
          <div class="bin-levels">
            <div v-for="bin in binTypes" :key="bin.id" class="bin-item">
              <svg v-if="materialIconSvg(bin.id)" class="bin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(bin.id)"></svg>
              <div class="bin-bar-wrap">
                <div class="bin-bar">
                  <div :class="['bin-fill', getBinClass(machine[bin.id + '_level'])]"
                       :style="{ width: machine[bin.id + '_level'] + '%' }"></div>
                </div>
                <span class="bin-pct">{{ machine[bin.id + '_level'] }}%</span>
              </div>
              <span v-if="machine[bin.id + '_level'] >= 90" class="bin-full-tag">{{ $t('dashboard.full') }}</span>
            </div>
          </div>

          <div class="machine-expanded-wrap" :class="{ expanded: selectedMachineId === machine.id }">
            <div class="machine-expanded-inner">
              <div v-if="selectedMachineId === machine.id" class="machine-expanded">
                <div class="map-embed">
                  <template v-if="machine.latitude && machine.longitude">
                    <div ref="mapContainer" class="leaflet-map"></div>
                    <span class="map-attribution">© OpenStreetMap contributors</span>
                  </template>
                  <div v-else class="no-map"><PhMapPin class="points-sub-icon" weight="regular" /> {{ $t('dashboard.locationNotSet') }}</div>
                </div>
                <a :href="getDirectionsUrl(machine)" target="_blank" class="directions-btn">
                  <PhMapTrifold class="points-sub-icon" weight="regular" /> {{ $t('dashboard.getDirections') }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="height:32px"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIconUrl from 'leaflet/dist/images/marker-icon.png'
import markerIcon2xUrl from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadowUrl from 'leaflet/dist/images/marker-shadow.png'
import { PhRecycle, PhGlobe, PhMapPin, PhMapTrifold } from '@phosphor-icons/vue'
import { materialIconSvg } from '@/utils/materialIcons'

// Leaflet's default marker icon paths are relative and break once bundled by Vite.
// Icon.Default._getIconUrl always prepends an auto-detected imagePath even when the
// url options below are overridden, doubling the path — deleting it falls back to
// the base Icon behavior, which uses these urls as-is.
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2xUrl,
  iconUrl: markerIconUrl,
  shadowUrl: markerShadowUrl,
})

const auth = useAuthStore()

const machines          = ref([])
const loadingMachines    = ref(true)
const selectedMachineId  = ref(null)

const binTypes = [
  { id: 'aluminum' },
  { id: 'plastic' },
  { id: 'glass' },
  { id: 'paper' },
]

function getBinClass(level) {
  if (level >= 90) return 'bin-danger'
  if (level >= 70) return 'bin-warning'
  return 'bin-ok'
}

function selectMachine(machine) {
  selectedMachineId.value = selectedMachineId.value === machine.id ? null : machine.id
}

const mapContainer = ref(null)
let leafletMap = null

function destroyMap() {
  if (leafletMap) {
    leafletMap.remove()
    leafletMap = null
  }
}

watch(selectedMachineId, async (id) => {
  destroyMap()
  if (id == null) return

  const machine = machines.value.find(m => m.id === id)
  if (!machine?.latitude || !machine?.longitude) return

  await nextTick()
  // mapContainer is declared on an element inside v-for, so Vue always binds it
  // as an array of matching elements rather than a single node.
  const container = Array.isArray(mapContainer.value) ? mapContainer.value[0] : mapContainer.value
  if (!container) return

  leafletMap = L.map(container, {
    zoomControl: false,
    dragging: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    attributionControl: false,
  }).setView([machine.latitude, machine.longitude], 16)

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(leafletMap)
  L.marker([machine.latitude, machine.longitude]).addTo(leafletMap)

  // The card's reveal is a CSS grid-row transition (0.25s) — Leaflet measures its
  // container size on init, which is still animating at that point, so tiles render
  // into the wrong viewport until we force a re-measure once the transition settles.
  setTimeout(() => leafletMap?.invalidateSize(), 300)
})

onUnmounted(destroyMap)

function getDirectionsUrl(machine) {
  if (machine.latitude && machine.longitude) {
    return `https://maps.google.com/?q=${machine.latitude},${machine.longitude}`
  }
  return `https://maps.google.com/?q=${encodeURIComponent(machine.location_name)}`
}

onMounted(async () => {
  try {
    const res = await api.get('/machines')
    machines.value = res.data.machines || []
  } catch { machines.value = [] }
  finally { loadingMachines.value = false }

  // Refresh user data (points hero reads auth.user directly)
  await auth.fetchMe()
})
</script>

<style scoped>
.dashboard-page { min-height: 100vh; background: var(--bg-primary); }

/* Points hero */
.points-hero {
  background: var(--grad-header);
  padding: 28px 20px 32px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.points-bg {
  position: absolute; inset: 0;
  background: radial-gradient(circle at 70% 50%, rgba(255,255,255,0.1) 0%, transparent 60%);
}
.points-label { color: rgba(255,255,255,0.8); font-size: 13px; margin-bottom: 8px; }
.points-number { color: white; font-size: 56px; font-weight: 800; line-height: 1; letter-spacing: -2px; }
.points-sub { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 8px; }
.carbon-sub { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 4px; }

.section-pad { padding: 20px 16px 0; }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 14px; }
.points-sub-icon { width: 14px; height: 14px; vertical-align: -2px; margin-right: 2px; }

/* Machines */
.map-placeholder { background: var(--bg-card); border-radius: var(--radius); padding: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border); }
.map-loading { display: flex; align-items: center; gap: 10px; color: var(--text-muted); }

.machine-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 14px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.2s;
}
.machine-card:hover { border-color: var(--accent-blue); }
.machine-header { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
.machine-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
.dot-green { background: var(--accent-green); box-shadow: 0 0 6px rgba(34,197,94,0.5); }
.dot-red   { background: var(--accent-red); }
.machine-info { flex: 1; }
.machine-info strong { display: block; color: var(--text-primary); font-size: 14px; font-weight: 600; }
.machine-info span   { color: var(--text-muted); font-size: 12px; }
.machine-code { background: var(--bg-hover); color: var(--text-muted); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-family: monospace; }

/* Bin levels */
.bin-levels { display: flex; flex-direction: column; gap: 6px; }
.bin-item { display: flex; align-items: center; gap: 8px; }
.bin-icon { width: 16px; height: 16px; flex-shrink: 0; color: var(--text-secondary); }
.bin-bar-wrap { flex: 1; display: flex; align-items: center; gap: 6px; }
.bin-bar { flex: 1; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
.bin-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
.bin-ok      { background: var(--accent-green); }
.bin-warning { background: var(--accent-yellow); }
.bin-danger  { background: var(--accent-red); }
.bin-pct { font-size: 11px; color: var(--text-muted); width: 30px; text-align: right; }
.bin-full-tag { background: var(--accent-red); color: white; font-size: 9px; padding: 1px 5px; border-radius: 3px; font-weight: 700; }

/* Machine expanded */
.machine-expanded-wrap {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.25s ease;
}
.machine-expanded-wrap.expanded { grid-template-rows: 1fr; }
.machine-expanded-inner { overflow: hidden; }
.machine-expanded { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }
.leaflet-map { height: 160px; border-radius: 8px; }
.map-attribution { display: block; margin-top: 4px; font-size: 10px; color: var(--text-muted); text-align: right; }
.no-map { padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px; background: var(--bg-hover); border-radius: 8px; }
.directions-btn {
  display: block; text-align: center; margin-top: 8px;
  padding: 10px; background: var(--accent-blue); color: white;
  border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;
}

.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Responsive ── */
@media (max-width: 480px) {
  .points-hero { padding: 20px 16px 24px; }
  .points-number { font-size: 40px; letter-spacing: -1px; }

  .section-pad { padding: 14px 12px 0; }
  .section-title { font-size: 14px; }

  .machine-card { padding: 12px; }
  .machine-info strong { font-size: 13px; }
}

@media (max-width: 360px) {
  .points-number { font-size: 34px; }
}
</style>
