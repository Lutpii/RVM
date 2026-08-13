<template>
  <div class="landing-page">
    <!-- Header gradient -->
    <div class="rvm-header">
      <div class="header-controls">
        <button class="theme-btn" @click="toggleTheme()">
          {{ theme === 'dark' ? '☀️' : '🌙' }}
        </button>
        <button class="lang-btn" @click="toggleLang">
          {{ locale === 'en' ? 'MY' : 'EN' }}
        </button>
      </div>
      <h1 class="rvm-title">{{ $t('app.name') }}</h1>
      <div class="header-badges">
        <div class="badge status-badge">
          <span class="badge-label">{{ $t('session.status') }}</span>
          <span class="badge-value">{{ $t('landing.status') }}</span>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="rvm-body">
      <div class="landing-content">
        <!-- Recycle icon -->
        <div class="recycle-icon">♻️</div>

        <h2 class="welcome-title">{{ $t('landing.welcome') }}</h2>
        <p class="welcome-sub">{{ $t('landing.subtitle') }}</p>

        <div class="features-list">
          <div class="feature-item">
            <span class="feature-icon">♻️</span>
            <span>{{ $t('landing.feature1') }}</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon">🏆</span>
            <span>{{ $t('landing.feature2') }}</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon">⚖️</span>
            <span>{{ $t('landing.feature3') }}</span>
          </div>
        </div>

        <button class="start-btn" @click="goToScan">
          <span class="btn-icon">♻</span>
          {{ $t('landing.startBtn') }}
        </button>

        <!-- Login/Register links -->
        <div class="auth-links">
          <RouterLink to="/login" class="auth-link">{{ $t('nav.login') }}</RouterLink>
          <span class="divider">|</span>
          <RouterLink to="/register" class="auth-link">{{ $t('nav.register') }}</RouterLink>
          <span class="divider">|</span>
          <RouterLink to="/dashboard" class="auth-link">{{ $t('nav.dashboard') }}</RouterLink>
        </div>
      </div>
    </div>

    <!-- Footer step indicator -->
    <div class="rvm-footer">
      {{ $t('session.currentStep') }}: <strong class="step-label">{{ $t('landing.step') }}</strong>
    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const router   = useRouter()
const theme    = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale, t } = useI18n()

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

function goToScan() {
  const token = localStorage.getItem('rvm_token')
  if (token) {
    router.push('/scan')
  } else {
    router.push('/login')
  }
}
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg-primary);
}

.rvm-header {
  background: var(--grad-header);
  padding: 24px 20px 20px;
  position: relative;
  border-radius: 0 0 0 0;
}

.header-controls {
  position: absolute;
  top: 16px;
  right: 16px;
  display: flex;
  gap: 8px;
}

.theme-btn, .lang-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 13px;
  backdrop-filter: blur(4px);
  transition: background 0.2s;
}
.theme-btn:hover, .lang-btn:hover { background: rgba(255,255,255,0.3); }

.rvm-title {
  color: white;
  font-size: 26px;
  font-weight: 800;
  text-align: center;
  letter-spacing: -0.5px;
  margin-bottom: 16px;
}

.header-badges {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.badge {
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(8px);
  border-radius: 10px;
  padding: 10px 16px;
  flex: 1;
}
.badge-label { display: block; color: rgba(255,255,255,0.8); font-size: 11px; margin-bottom: 4px; }
.badge-value { color: white; font-size: 18px; font-weight: 700; }

.rvm-body {
  flex: 1;
  background: var(--bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
}

.landing-content {
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.recycle-icon {
  font-size: 64px;
  margin: 0 auto 20px;
  filter: drop-shadow(0 0 24px rgba(34,197,94,0.5));
  animation: float 4s ease-in-out infinite;
}

@keyframes float {
  0%,100% { transform: translateY(0); }
  50%      { transform: translateY(-10px); }
}

.welcome-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 10px;
}

.welcome-sub {
  color: var(--text-secondary);
  margin-bottom: 24px;
  font-size: 15px;
}

.features-list {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
  margin-bottom: 24px;
  text-align: left;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  color: var(--text-secondary);
  font-size: 14px;
}
.feature-item + .feature-item { border-top: 1px solid var(--border); }
.feature-icon { font-size: 18px; }

.start-btn {
  width: 100%;
  padding: 16px 24px;
  background: var(--accent-green);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s;
  box-shadow: 0 4px 20px rgba(34,197,94,0.3);
  margin-bottom: 20px;
}
.start-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(34,197,94,0.4);
}
.btn-icon { font-size: 18px; }

.auth-links {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}
.auth-link {
  color: var(--accent-blue);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}
.auth-link:hover { text-decoration: underline; }
.divider { color: var(--text-muted); }

.rvm-footer {
  background: var(--bg-card);
  border-top: 1px solid var(--border);
  padding: 12px 20px;
  text-align: center;
  font-size: 13px;
  color: var(--text-muted);
}
.step-label { color: var(--accent-blue); }
</style>
