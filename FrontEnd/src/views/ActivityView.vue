<!-- FrontEnd/src/views/ActivityView.vue -->
<template>
  <div class="activity-page">
    <h1 class="page-title">{{ $t('activity.title') }}</h1>

    <p v-if="!loading && hasLoadError" class="load-error-notice">{{ $t('activity.loadError') }}</p>

    <div v-if="loading" class="loading-placeholder">
      <div class="spinner-sm"></div>
    </div>
    <div v-else-if="feed.length === 0" class="empty-state">
      <p>{{ $t('activity.empty') }}</p>
    </div>
    <div v-else class="activity-list">
      <div v-for="entry in feed" :key="entry.id" class="activity-item">
        <div class="activity-icon">{{ entry.kind === 'session' ? '♻️' : (entry.pointsChange > 0 ? '➕' : '➖') }}</div>
        <div class="activity-info">
          <span class="activity-desc">{{ entry.kind === 'session' ? $t('activity.session', { code: entry.description }) : entry.description }}</span>
          <span class="activity-time">{{ formatTime(entry.timestamp) }}</span>
        </div>
        <span v-if="entry.pointsChange != null" :class="['activity-pts', entry.pointsChange > 0 ? 'pts-green' : 'pts-red']">
          {{ entry.pointsChange > 0 ? '+' : '' }}{{ entry.pointsChange }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { mergeActivityFeed } from '@/utils/activityFeed'

const feed    = ref([])
const loading = ref(true)

const pointsHistoryFailed = ref(false)
const sessionsFailed      = ref(false)
const redemptionsFailed   = ref(false)
const hasLoadError = computed(() => pointsHistoryFailed.value || sessionsFailed.value || redemptionsFailed.value)

function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  let pointsHistory = []
  let sessions = []
  let redemptions = []

  try {
    const res = await api.get('/user/points-history')
    pointsHistory = res.data.history?.data || []
  } catch {
    pointsHistory = []
    pointsHistoryFailed.value = true
  }

  try {
    const res = await api.get('/user/sessions')
    sessions = res.data.sessions?.data || []
  } catch {
    sessions = []
    sessionsFailed.value = true
  }

  try {
    const res = await api.get('/user/redemptions')
    redemptions = res.data.redemptions?.data || []
  } catch {
    redemptions = []
    redemptionsFailed.value = true
  }

  feed.value = mergeActivityFeed(pointsHistory, sessions, redemptions)
  loading.value = false
})
</script>

<style scoped>
.activity-page { padding: 20px 16px 32px; max-width: 640px; margin: 0 auto; }
.page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.load-error-notice {
  font-size: 12px; color: var(--accent-yellow);
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 8px 12px; margin-bottom: 12px;
}
.empty-state {
  text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;
  background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border);
}
.activity-list { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.activity-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--border); }
.activity-item:last-child { border-bottom: none; }
.activity-icon { font-size: 16px; flex-shrink: 0; }
.activity-info { flex: 1; display: flex; flex-direction: column; }
.activity-desc { font-size: 13px; color: var(--text-primary); }
.activity-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.activity-pts { font-size: 14px; font-weight: 700; }
.pts-green { color: var(--accent-green); }
.pts-red { color: var(--accent-red); }
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
