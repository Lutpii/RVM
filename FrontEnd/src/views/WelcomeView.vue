<template>
  <div class="welcome-splash">
    <div class="welcome-bg"></div>

    <div class="welcome-content">
      <img src="@/assets/dsme-logo.png" class="dsme-logo" alt="DSME Engineering" />

      <div class="welcome-icon-float">
        <div class="welcome-icon">♻️</div>
      </div>

      <h1 class="welcome-title">Welcome, <span class="user-name">{{ userName }}</span>!</h1>
      <p class="welcome-sub">Let's get recycling.</p>

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
// kiosk_token) — the phone-scanned user's name comes via ?name= instead.
const userName = computed(() => auth.user?.name || route.query.name || 'there')

onMounted(() => {
  // Default to light. A logged-in user's own dark preference wins — either
  // this browser's own session (regular web login), or the phone-scanned
  // kiosk user's preference passed via ?theme= (this browser isn't logged
  // in on the kiosk, only the phone is).
  document.documentElement.setAttribute('data-theme', route.query.theme || auth.user?.theme_preference || 'light')

  const target = route.query.redirect || '/dashboard'
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
  background: radial-gradient(ellipse at 50% 30%, rgba(78,110,242,0.25) 0%, transparent 60%);
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
  filter: drop-shadow(0 0 30px rgba(78,110,242,0.5));
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

.user-name { color: var(--accent-blue); }

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
  background: linear-gradient(90deg, var(--accent-blue), var(--accent-green));
  border-radius: 2px;
  animation: fillProgress 3s linear forwards;
}

@keyframes fillProgress {
  to { width: 100%; }
}
</style>
