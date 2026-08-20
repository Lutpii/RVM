<template>
  <div class="welcome-splash">
    <div class="welcome-bg"></div>

    <div class="welcome-content">
      <img src="@/assets/dsme-logo.png" class="dsme-logo" alt="DSME Engineering" />

      <div class="welcome-icon-float">
        <svg class="welcome-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="width:1em;height:1em">
          <path d="M3 12a9 9 0 0 1 15-6.7L21 8"/>
          <path d="M21 3v5h-5"/>
          <path d="M21 12a9 9 0 0 1-15 6.7L3 16"/>
          <path d="M3 21v-5h5"/>
        </svg>
      </div>

      <h1 class="welcome-title">Thank You, <span class="user-name">{{ userName }}</span>!</h1>
      <p class="welcome-sub">Every item you recycle makes a difference.</p>

      <div class="progress-bar">
        <div class="progress-fill"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()

// On the kiosk, the kiosk's own browser session isn't logged in (only holds a
// kiosk_token) — the ended session's name comes via ?name= instead.
const userName = computed(() => auth.user?.name || route.query.name || 'there')

onMounted(() => {
  const target = route.query.redirect || '/'
  setTimeout(() => {
    router.replace(target)
  }, 3000)
})
</script>

<style scoped>
.welcome-splash {
  min-height: 100vh;
  background: var(--bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.welcome-bg {
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 30%, rgba(34,197,94,0.25) 0%, transparent 60%);
}

.welcome-content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 20px 40px;
  text-align: center;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

.dsme-logo {
  height: 56px;
  width: auto;
  margin-bottom: 8px;
  opacity: 0.9;
}

.welcome-icon-float {
  animation: float 4s cubic-bezier(0.45, 0, 0.55, 1) infinite;
}

.welcome-icon {
  font-size: 72px;
  filter: drop-shadow(0 0 30px rgba(34,197,94,0.5));
  animation: pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes pop {
  from { transform: scale(0.5); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

@keyframes float {
  0%,100% { transform: translateY(0); }
  50%      { transform: translateY(-10px); }
}

.welcome-title {
  font-size: 32px;
  font-weight: 800;
  color: var(--text-primary);
  margin: 4px 0 0;
}

.user-name { color: var(--accent-green); }

.welcome-sub {
  font-size: 15px;
  color: var(--text-secondary);
  margin: 0;
}

.progress-bar {
  width: 220px;
  height: 4px;
  background: var(--border);
  border-radius: 2px;
  overflow: hidden;
  margin-top: 12px;
}

.progress-fill {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, var(--accent-green), var(--accent-blue));
  border-radius: 2px;
  animation: fillProgress 3s linear forwards;
}

@keyframes fillProgress {
  to { width: 100%; }
}
</style>
