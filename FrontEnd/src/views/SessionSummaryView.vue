<template>
  <div class="summary-page">
    <div class="summary-header">
      <h1>{{ $t('app.name') }}</h1>
      <p v-if="auth.user">Welcome, {{ auth.user.name }}</p>
      <div class="header-badges">
        <div class="badge">
          <span class="badge-label">{{ $t('session.totalPoints') }}</span>
          <span class="badge-value">{{ finalPoints }}</span>
        </div>
        <div class="badge">
          <span class="badge-label">{{ $t('session.status') }}</span>
          <span class="badge-value">{{ $t('session.processing') }}</span>
        </div>
      </div>
    </div>

    <div class="progress-bar">
      <div class="progress-fill" style="width:100%"></div>
    </div>

    <div class="summary-body">
      <!-- Trophy animation -->
      <div class="trophy-wrap">
        <div class="trophy">{{ rvm.isGuest ? '♻️' : '🏆' }}</div>
        <div class="confetti" v-for="i in 12" :key="i" :style="confettiStyle(i)"></div>
      </div>

      <h2 class="summary-title">{{ rvm.isGuest ? 'Thank You for Recycling!' : $t('summary.title') }}</h2>

      <div v-if="rvm.isGuest" class="donation-banner">
        <span class="donation-icon">🌱</span>
        <div class="donation-text">
          <strong>{{ earnedPoints }} points donated</strong>
          <span>Your recycling contribution makes a difference!</span>
        </div>
      </div>

      <div class="summary-card" v-if="summary">
        <div class="summary-row">
          <span class="row-label">{{ $t('summary.user') }}</span>
          <span class="row-value">{{ summary.user_name }}</span>
        </div>
        <div class="summary-row">
          <span class="row-label">{{ $t('summary.sessionId') }}</span>
          <span class="row-value mono">{{ summary.session_code }}</span>
        </div>
        <div class="summary-row">
          <span class="row-label">{{ $t('summary.totalItems') }}</span>
          <span class="row-value">{{ summary.total_items || 0 }}</span>
        </div>
        <template v-if="!rvm.isGuest">
          <div class="summary-row">
            <span class="row-label">{{ $t('summary.startPoints') }}</span>
            <span class="row-value">{{ summary.start_points }}</span>
          </div>
          <div class="summary-row">
            <span class="row-label">{{ $t('summary.endPoints') }}</span>
            <span class="row-value">{{ summary.end_points || finalPoints }}</span>
          </div>
        </template>
        <div class="summary-row highlight">
          <span class="row-label">{{ rvm.isGuest ? 'Points Donated' : $t('summary.pointsEarned') }}</span>
          <span class="row-value earned">+{{ earnedPoints }}</span>
        </div>
      </div>

      <!-- Loading state -->
      <div v-else class="summary-loading">
        <div class="spinner-lg"></div>
        <p>Loading summary...</p>
      </div>

      <!-- Transactions breakdown -->
      <div v-if="summary?.transactions?.length" class="transactions-wrap">
        <h3 class="breakdown-title">Items Recycled</h3>
        <div v-for="(t, i) in summary.transactions" :key="i" class="txn-row">
          <span class="txn-icon">{{ getMaterialIcon(t.material) }}</span>
          <div class="txn-info">
            <span class="txn-mat">{{ t.material }}</span>
            <span class="txn-weight">{{ t.weight }}g</span>
          </div>
          <span :class="['txn-pts', t.is_valid ? 'pts-green' : 'pts-red']">
            {{ t.is_valid ? '+' + t.points_earned : '-' + t.points_deducted }}
          </span>
        </div>
      </div>

      <button class="end-btn" @click="goHome">
        {{ $t('summary.endSessionBtn') }}
      </button>
    </div>

    <div class="rvm-footer">
      {{ $t('session.currentStep') }}: <strong class="step-label">Complete</strong>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { useRvmStore } from '@/store/rvm'
import { setKioskToken } from '@/services/api'

const router = useRouter()
const route  = useRoute()
const auth = useAuthStore()
const rvm = useRvmStore()

const summary = ref(null)
const finalPoints = computed(() => summary.value?.end_points ?? 0)
const earnedPoints = computed(() => summary.value?.points_earned ?? 0)

watch(summary, (val) => {
  if (val?.end_points != null) auth.updatePoints(val.end_points)
})

const materialIcons = { aluminum: '🥫', plastic: '🧴', glass: '🍶', paper: '📄' }
function getMaterialIcon(mat) { return materialIcons[mat] || '♻️' }

function confettiStyle(i) {
  const colors = ['#4e6ef2', '#a855f7', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4']
  return {
    left: (Math.random() * 100) + '%',
    animationDelay: (i * 0.15) + 's',
    background: colors[i % colors.length],
    animationDuration: (0.8 + Math.random() * 0.8) + 's',
  }
}

async function goHome() {
  const machineCode = rvm.guestMachineCode || route.params.machineCode
  const isKiosk = route.path.startsWith('/kiosk/')
  setKioskToken(null)
  rvm.resetSession()
  if (isKiosk && machineCode) {
    await auth.logout()
    // Reset the kiosk display to its own default look for the next customer.
    document.documentElement.setAttribute('data-theme', 'dark')
    router.push(`/kiosk/${machineCode}`)
  } else {
    await auth.logout()
    router.push('/')
  }
}

function computeFromTransactions(txns) {
  const total_items = txns.filter(t => t.is_valid).length
  const points_earned = txns.reduce((sum, t) =>
    sum + (t.is_valid ? (t.points_earned || 0) : -(t.points_deducted || 0)), 0)
  return { total_items, points_earned }
}

onMounted(async () => {
  const local = rvm.localSummary
  const computedEnd = local.start_points + local.points_earned

  // Guest mode: build summary from local data, no API call
  if (rvm.isGuest) {
    const transactions = local.transactions
    const { total_items, points_earned } = computeFromTransactions(transactions)
    summary.value = {
      user_name:    'Guest',
      session_code: rvm.session?.session_code || ('GUEST-' + Date.now().toString(36).toUpperCase()),
      start_points: 0,
      end_points:   0,
      points_earned,
      total_items,
      transactions,
    }
    return
  }

  if (rvm.session?.session_code) {
    try {
      const data = await rvm.getSummary()
      const apiSummary = data?.summary
      const transactions = apiSummary?.transactions?.length ? apiSummary.transactions : local.transactions
      const { total_items, points_earned } = computeFromTransactions(transactions)
      summary.value = {
        user_name:    apiSummary?.user_name || auth.user?.name || 'User',
        session_code: apiSummary?.session_code || rvm.session?.session_code,
        start_points: apiSummary?.start_points ?? local.start_points,
        end_points:   apiSummary?.end_points ?? computedEnd,
        points_earned,
        total_items,
        transactions,
      }
    } catch {
      const transactions = local.transactions
      const { total_items, points_earned } = computeFromTransactions(transactions)
      summary.value = {
        user_name:    auth.user?.name || 'User',
        session_code: rvm.session?.session_code,
        start_points: rvm.session?.start_points ?? local.start_points,
        end_points:   rvm.session?.end_points ?? computedEnd,
        points_earned,
        total_items,
        transactions,
      }
    }
  } else {
    const transactions = local.transactions
    const { total_items, points_earned } = computeFromTransactions(transactions)
    summary.value = {
      user_name:    auth.user?.name || 'User',
      session_code: 'LOCAL-' + Date.now().toString(36).toUpperCase(),
      start_points: local.start_points,
      end_points:   computedEnd,
      points_earned,
      total_items,
      transactions,
    }
  }
})
</script>

<style scoped>
.summary-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg-primary);
}

.summary-header {
  background: var(--grad-header);
  padding: 20px 20px 16px;
  text-align: center;
}

.summary-header h1 {
  color: white;
  font-size: 20px;
  font-weight: 800;
  margin-bottom: 4px;
}

.summary-header p {
  color: rgba(255, 255, 255, 0.85);
  font-size: 13px;
  margin-bottom: 12px;
}

.header-badges {
  display: flex;
  gap: 12px;
}

.badge {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  border-radius: 10px;
  padding: 8px 14px;
  flex: 1;
}

.badge-label {
  display: block;
  color: rgba(255, 255, 255, 0.8);
  font-size: 11px;
  margin-bottom: 3px;
}

.badge-value {
  color: white;
  font-size: 18px;
  font-weight: 700;
}

.progress-bar {
  height: 4px;
  background: var(--bg-card);
}

.progress-fill {
  height: 100%;
  background: var(--grad-header);
}

.summary-body {
  flex: 1;
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Trophy */
.trophy-wrap {
  position: relative;
  margin-bottom: 16px;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.trophy {
  font-size: 64px;
  animation: trophy-bounce 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes trophy-bounce {
  from {
    transform: scale(0) rotate(-20deg);
    opacity: 0;
  }

  to {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

.confetti {
  position: absolute;
  width: 8px;
  height: 8px;
  border-radius: 2px;
  animation: confetti-fall linear forwards;
  opacity: 0;
}

@keyframes confetti-fall {
  0% {
    transform: translateY(-40px) rotate(0deg);
    opacity: 1;
  }

  100% {
    transform: translateY(60px) rotate(360deg);
    opacity: 0;
  }
}

.summary-title {
  font-size: 22px;
  font-weight: 800;
  color: var(--accent-green);
  margin-bottom: 20px;
  text-align: center;
}

.donation-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  background: rgba(34,197,94,0.1);
  border: 1px solid rgba(34,197,94,0.3);
  border-radius: 12px;
  padding: 14px 18px;
  width: 100%;
  max-width: 380px;
  margin-bottom: 16px;
}
.donation-icon { font-size: 32px; flex-shrink: 0; }
.donation-text {
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.donation-text strong {
  color: var(--accent-green);
  font-size: 16px;
}
.donation-text span {
  color: var(--text-secondary);
  font-size: 13px;
}

.summary-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 4px 0;
  width: 100%;
  max-width: 380px;
  margin-bottom: 16px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 18px;
  border-bottom: 1px solid var(--border);
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-row.highlight {
  background: rgba(34, 197, 94, 0.05);
}

.row-label {
  color: var(--text-secondary);
  font-size: 14px;
}

.row-value {
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 600;
}

.row-value.mono {
  font-family: monospace;
  font-size: 12px;
}

.row-value.earned {
  color: var(--accent-green);
  font-size: 20px;
  font-weight: 800;
}

.summary-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 40px;
  color: var(--text-muted);
}

/* Transactions */
.transactions-wrap {
  width: 100%;
  max-width: 380px;
  margin-bottom: 20px;
}

.breakdown-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 10px;
}

.txn-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 8px;
  margin-bottom: 8px;
}

.txn-icon {
  font-size: 20px;
}

.txn-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.txn-mat {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary);
  text-transform: capitalize;
}

.txn-weight {
  font-size: 11px;
  color: var(--text-muted);
}

.txn-pts {
  font-size: 14px;
  font-weight: 700;
}

.pts-green {
  color: var(--accent-green);
}

.pts-red {
  color: var(--accent-red);
}

.end-btn {
  width: 100%;
  max-width: 380px;
  padding: 15px;
  background: var(--accent-blue);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 20px;
}

.end-btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.rvm-footer {
  background: var(--bg-card);
  border-top: 1px solid var(--border);
  padding: 12px 20px;
  text-align: center;
  font-size: 13px;
  color: var(--text-muted);
}

.step-label {
  color: var(--accent-blue);
}

.spinner-lg {
  width: 48px;
  height: 48px;
  border: 3px solid var(--border);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
