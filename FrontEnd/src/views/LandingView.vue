<template>
  <div class="landing-page">
    <!-- Header gradient -->
    <div class="rvm-header">
      <div class="header-top">
        <h1 class="rvm-title">{{ $t('app.name') }}</h1>
        <div class="header-controls">
          <button class="theme-btn" @click="toggleTheme()" :aria-label="theme === 'dark' ? $t('landing.switchToLight') : $t('landing.switchToDark')">
            <PhSun v-if="theme === 'dark'" weight="regular" aria-hidden="true" />
            <PhMoon v-else weight="regular" aria-hidden="true" />
          </button>
          <button class="lang-btn" @click="toggleLang" :aria-label="locale === 'en' ? 'Switch to Bahasa Melayu' : 'Switch to English'">
            {{ locale === 'en' ? 'MY' : 'EN' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="rvm-body">
      <div class="landing-content">
        <!-- Recycle icon -->
        <PhRecycle class="recycle-icon" weight="regular" />

        <h2 class="welcome-title">{{ $t('landing.welcome') }}</h2>
        <p class="welcome-sub">{{ $t('landing.subtitle') }}</p>

        <div class="features-list">
          <div class="feature-item">
            <PhRecycle class="feature-icon feature-icon-green" weight="regular" />
            <span>{{ $t('landing.feature1') }}</span>
          </div>
          <div class="feature-item">
            <PhTrophy class="feature-icon feature-icon-yellow" weight="regular" />
            <span>{{ $t('landing.feature2') }}</span>
          </div>
          <div class="feature-item">
            <PhGlobe class="feature-icon feature-icon-blue" weight="regular" />
            <span>{{ $t('landing.feature3') }}</span>
          </div>
        </div>

        <button class="start-btn" @click="goToScan">
          <PhRecycle class="btn-icon" weight="regular" />
          {{ $t('landing.startBtn') }}
        </button>

        <!-- Login/Register links -->
        <div class="auth-links">
          <RouterLink to="/login" class="auth-link">{{ $t('nav.login') }}</RouterLink>
          <span class="divider">|</span>
          <RouterLink to="/register" class="auth-link">{{ $t('nav.register') }}</RouterLink>
        </div>
      </div>
    </div>

    <footer class="landing-brand-footer">
      <BrandFooter class="landing-brand-logo" />
      <span>UMPSA &nbsp;·&nbsp; Eco Smart Campus</span>
    </footer>
  </div>
</template>

<script setup>
import { inject } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { PhSun, PhMoon, PhRecycle, PhTrophy, PhGlobe } from '@phosphor-icons/vue'
import BrandFooter from '@/components/BrandFooter.vue'

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
  min-height: 100dvh;
  height: 100vh;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  background: var(--bg-secondary);
  overflow: hidden;
}

.rvm-header {
  flex-shrink: 0;
  background: var(--grad-header);
  padding: 24px 20px 20px;
  border-radius: 0 0 0 0;
}

.header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.header-controls {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.theme-btn, .lang-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  min-width: 38px;
  min-height: 36px;
  padding: 6px 10px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
  transition: background 0.2s;
}
.theme-btn:hover, .lang-btn:hover { background: rgba(255,255,255,0.3); }

.rvm-title {
  color: white;
  font-size: 26px;
  font-weight: 800;
  text-align: left;
  letter-spacing: -0.5px;
  margin: 0;
}

.rvm-body {
  flex: 1;
  min-height: 0;
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
  color: var(--accent-green);
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
.feature-icon { font-size: 18px; flex-shrink: 0; }
.feature-icon-green  { color: var(--accent-green); }
.feature-icon-yellow { color: var(--accent-yellow); }
.feature-icon-blue   { color: var(--accent-blue); }

.start-btn {
  width: 100%;
  padding: 16px 24px;
  background: #15803d; /* was var(--accent-green); darkened for 4.5:1 contrast with white text */
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
  color: #7b93ff; /* was var(--accent-blue); brightened for 4.5:1 contrast on dark theme */
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}
.auth-link:hover { text-decoration: underline; }
.divider { color: var(--text-muted); }

.landing-brand-footer {
  flex-shrink: 0;
  padding: 10px 16px max(20px, env(safe-area-inset-bottom));
  background: var(--bg-secondary);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  color: var(--text-muted);
  font-size: 13px;
  text-align: center;
}

.landing-brand-logo {
  --brand-logo-height: 96px;
  max-width: calc(100% - 32px);
}

/* --accent-blue has no light-theme override, so the dark-tuned color above
   also fails once the background flips to white — override again here. */
[data-theme="light"] .auth-link {
  color: #3f5fee;
}

@media (min-width: 769px) {
  .header-top { position: relative; justify-content: center; }
  .rvm-title { text-align: center; }
  .header-controls { position: absolute; right: 0; }
}

@media (max-width: 480px) {
  .rvm-header { padding: 18px 16px 16px; }
  .header-top { gap: 12px; margin-bottom: 14px; }
  .rvm-title { flex: 1; min-width: 0; font-size: 21px; line-height: 1.15; }
  .header-controls { gap: 6px; }
  .theme-btn, .lang-btn { min-width: 36px; min-height: 36px; padding: 6px 9px; }
  .landing-brand-footer {
    padding-bottom: max(16px, env(safe-area-inset-bottom));
    gap: 3px;
    font-size: 11px;
  }
  .landing-brand-logo { --brand-logo-height: 76px; }
}

@media (max-height: 700px) {
  .rvm-header { padding: 12px 16px 10px; }
  .header-top { margin-bottom: 8px; }
  .rvm-title { font-size: 20px; }
  .rvm-body { padding: 12px 20px; }
  .recycle-icon { font-size: 44px; margin-bottom: 8px; }
  .welcome-title { font-size: 20px; margin-bottom: 6px; }
  .welcome-sub { margin-bottom: 12px; font-size: 13px; }
  .features-list { padding: 8px 12px; margin-bottom: 12px; }
  .feature-item { padding: 4px 0; font-size: 12px; }
  .feature-icon { font-size: 16px; }
  .start-btn { padding: 11px 20px; margin-bottom: 10px; font-size: 14px; }
  .auth-link { font-size: 12px; }
  .landing-brand-footer {
    padding: 4px 16px max(10px, env(safe-area-inset-bottom));
    gap: 3px;
    font-size: 11px;
  }
  .landing-brand-logo { --brand-logo-height: 56px; }
}
</style>
