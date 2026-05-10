<template>
  <div class="dashboard-page">
    <!-- Top nav -->
    <div class="top-nav">
      <div class="nav-left">
        <div class="avatar" v-if="auth.user">{{ auth.user.name?.charAt(0)?.toUpperCase() }}</div>
        <div class="user-info">
          <span class="user-name">{{ auth.user?.name }}</span>
          <span class="user-role">{{ auth.user?.role === 'admin' ? '⚙️ Admin' : '♻️ Recycler' }}</span>
        </div>
      </div>
      <div class="nav-right">
        <button class="ctrl-btn" @click="toggleTheme()">{{ theme === 'dark' ? '☀️' : '🌙' }}</button>
        <button class="ctrl-btn" @click="toggleLang">{{ locale === 'en' ? 'MY' : 'EN' }}</button>
        <RouterLink v-if="auth.isAdmin" to="/admin" class="admin-link">⚙️</RouterLink>
        <button class="logout-btn" @click="handleLogout">{{ $t('nav.logout') }}</button>
      </div>
    </div>

    <!-- Points hero card -->
    <div class="points-hero">
      <div class="points-bg"></div>
      <p class="points-label">{{ $t('dashboard.totalPoints') }}</p>
      <div class="points-number">{{ auth.user?.total_points || 0 }}</div>
      <p class="points-sub">♻️ Keep recycling to earn more!</p>
    </div>

    <!-- Quick action -->
    <div class="section-pad">
      <RouterLink to="/scan" class="scan-action-btn">
        <span class="scan-icon">📷</span>
        <div>
          <strong>{{ $t('dashboard.startRecycling') }}</strong>
          <span>Scan QR at the machine</span>
        </div>
        <span class="arrow">→</span>
      </RouterLink>
    </div>

    <!-- Nearby machines map -->
    <div class="section-pad">
      <h3 class="section-title">📍 {{ $t('dashboard.nearbyMachines') }}</h3>

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
              <span>📍 {{ machine.location_name }}</span>
            </div>
            <span class="machine-code">{{ machine.machine_code }}</span>
          </div>

          <!-- Bin levels -->
          <div class="bin-levels">
            <div v-for="bin in binTypes" :key="bin.id" class="bin-item">
              <span class="bin-icon">{{ bin.icon }}</span>
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

          <div v-if="selectedMachineId === machine.id" class="machine-expanded">
            <div class="map-embed">
              <iframe
                v-if="machine.latitude && machine.longitude"
                :src="`https://maps.google.com/maps?q=${machine.latitude},${machine.longitude}&z=15&output=embed`"
                width="100%" height="160" frameborder="0" style="border-radius:8px;"
                allowfullscreen
              ></iframe>
              <div v-else class="no-map">📍 Location not set for this machine</div>
            </div>
            <a :href="getDirectionsUrl(machine)" target="_blank" class="directions-btn">
              🗺️ Get Directions
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="section-pad">
      <h3 class="section-title">🕒 {{ $t('dashboard.recentActivity') }}</h3>
      <div v-if="loadingHistory" class="loading-placeholder">
        <div class="spinner-sm"></div>
      </div>
      <div v-else-if="recentHistory.length === 0" class="empty-state">
        <p>No recycling activity yet. Start recycling to earn points!</p>
      </div>
      <div v-else class="activity-list">
        <div v-for="h in recentHistory" :key="h.id" class="activity-item">
          <div :class="['activity-dot', h.points_change > 0 ? 'dot-green' : 'dot-red']"></div>
          <div class="activity-info">
            <span class="activity-desc">{{ h.description }}</span>
            <span class="activity-time">{{ formatTime(h.created_at) }}</span>
          </div>
          <span :class="['activity-pts', h.points_change > 0 ? 'pts-green' : 'pts-red']">
            {{ h.points_change > 0 ? '+' : '' }}{{ h.points_change }}
          </span>
        </div>
      </div>
    </div>

    <div style="height:32px"></div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const router      = useRouter()
const auth        = useAuthStore()
const theme       = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale }  = useI18n()

const machines         = ref([])
const recentHistory    = ref([])
const loadingMachines  = ref(true)
const loadingHistory   = ref(true)
const selectedMachineId = ref(null)

const binTypes = [
  { id: 'aluminum', icon: '🥫' },
  { id: 'plastic',  icon: '🧴' },
  { id: 'glass',    icon: '🍶' },
  { id: 'paper',    icon: '📄' },
]

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

function getBinClass(level) {
  if (level >= 90) return 'bin-danger'
  if (level >= 70) return 'bin-warning'
  return 'bin-ok'
}

function selectMachine(machine) {
  selectedMachineId.value = selectedMachineId.value === machine.id ? null : machine.id
}

function getDirectionsUrl(machine) {
  if (machine.latitude && machine.longitude) {
    return `https://maps.google.com/?q=${machine.latitude},${machine.longitude}`
  }
  return `https://maps.google.com/?q=${encodeURIComponent(machine.location_name)}`
}

function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

async function handleLogout() {
  await auth.logout()
  router.push('/')
}

onMounted(async () => {
  // Fetch machines
  try {
    const res = await api.get('/machines')
    machines.value = res.data.machines || []
  } catch { machines.value = [] }
  finally { loadingMachines.value = false }

  // Fetch points history
  try {
    const res = await api.get('/user/points-history')
    recentHistory.value = (res.data.history?.data || []).slice(0, 10)
  } catch { recentHistory.value = [] }
  finally { loadingHistory.value = false }

  // Refresh user data
  await auth.fetchMe()
})
</script>

<style scoped>
.dashboard-page { min-height: 100vh; background: var(--bg-primary); }

.top-nav {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  position: sticky; top: 0; z-index: 10;
}
.nav-left { display: flex; align-items: center; gap: 10px; }
.avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: var(--grad-header); color: white;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 16px;
}
.user-info { display: flex; flex-direction: column; }
.user-name  { font-size: 14px; font-weight: 600; color: var(--text-primary); }
.user-role  { font-size: 11px; color: var(--text-muted); }
.nav-right { display: flex; align-items: center; gap: 8px; }
.ctrl-btn { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-primary); padding: 5px 10px; border-radius: 16px; cursor: pointer; font-size: 12px; }
.admin-link { color: var(--text-secondary); text-decoration: none; font-size: 18px; }
.logout-btn { background: none; border: 1px solid var(--border); color: var(--text-secondary); padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px; }
.logout-btn:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }

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

.section-pad { padding: 20px 16px 0; }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 14px; }

/* Scan button */
.scan-action-btn {
  display: flex; align-items: center; gap: 14px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
  text-decoration: none;
  transition: all 0.2s;
  border-left: 4px solid var(--accent-green);
}
.scan-action-btn:hover { background: var(--bg-hover); transform: translateX(2px); }
.scan-icon { font-size: 28px; }
.scan-action-btn div { flex: 1; display: flex; flex-direction: column; }
.scan-action-btn strong { color: var(--text-primary); font-size: 15px; }
.scan-action-btn span  { color: var(--text-secondary); font-size: 12px; }
.arrow { color: var(--text-muted); font-size: 18px; }

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
.bin-icon { font-size: 14px; width: 20px; text-align: center; }
.bin-bar-wrap { flex: 1; display: flex; align-items: center; gap: 6px; }
.bin-bar { flex: 1; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
.bin-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
.bin-ok      { background: var(--accent-green); }
.bin-warning { background: var(--accent-yellow); }
.bin-danger  { background: var(--accent-red); }
.bin-pct { font-size: 11px; color: var(--text-muted); width: 30px; text-align: right; }
.bin-full-tag { background: var(--accent-red); color: white; font-size: 9px; padding: 1px 5px; border-radius: 3px; font-weight: 700; }

/* Machine expanded */
.machine-expanded { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }
.no-map { padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px; background: var(--bg-hover); border-radius: 8px; }
.directions-btn {
  display: block; text-align: center; margin-top: 8px;
  padding: 10px; background: var(--accent-blue); color: white;
  border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;
}

/* Activity */
.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.empty-state { text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px; background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border); }
.activity-list { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.activity-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--border); }
.activity-item:last-child { border-bottom: none; }
.activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.activity-info { flex: 1; display: flex; flex-direction: column; }
.activity-desc { font-size: 13px; color: var(--text-primary); }
.activity-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.activity-pts  { font-size: 14px; font-weight: 700; }
.pts-green { color: var(--accent-green); }
.pts-red   { color: var(--accent-red); }

.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Responsive ── */
@media (max-width: 480px) {
  .top-nav { padding: 10px 12px; gap: 6px; }
  .user-name { font-size: 13px; }
  .user-role { display: none; }
  .ctrl-btn { padding: 4px 8px; font-size: 11px; }
  .logout-btn { padding: 5px 8px; font-size: 11px; }

  .points-hero { padding: 20px 16px 24px; }
  .points-number { font-size: 40px; letter-spacing: -1px; }

  .section-pad { padding: 14px 12px 0; }
  .section-title { font-size: 14px; }

  .scan-action-btn { padding: 12px; gap: 10px; }
  .scan-icon { font-size: 22px; }
  .scan-action-btn strong { font-size: 13px; }

  .machine-card { padding: 12px; }
  .machine-info strong { font-size: 13px; }

  .activity-item { padding: 10px 12px; gap: 8px; }
  .activity-desc { font-size: 12px; }
  .activity-pts  { font-size: 13px; }
}

@media (max-width: 360px) {
  .points-number { font-size: 34px; }
  .nav-right .ctrl-btn:first-child { display: none; }
}
</style>
