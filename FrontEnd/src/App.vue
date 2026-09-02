<template>
  <div :class="['app-root', theme]" :data-theme="theme">
    <RouterView />

    <Transition name="toast-fade">
      <div v-if="toastState.show" :class="['toast', toastState.type]" role="status">
        <span class="toast-icon">{{ toastState.type === 'error' ? '⚠️' : '✅' }}</span>
        {{ toastState.message }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, provide, onMounted, watch } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const theme = ref(localStorage.getItem('rvm_theme') || 'dark')
const auth  = useAuthStore()

const toastState = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
function showToast(message, type = 'success') {
  toastState.value = { show: true, message, type }
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toastState.value.show = false }, 3500)
}

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  localStorage.setItem('rvm_theme', theme.value)

  // Persist to the account (not just this browser) so it can follow the
  // user anywhere theme is applied from their identity, e.g. the RVM kiosk.
  if (auth.isLoggedIn) {
    api.put('/user/profile', { theme_preference: theme.value }).then((res) => {
      if (res.data.success && auth.user) {
        const updated = { ...auth.user, theme_preference: theme.value }
        auth.user = updated
        localStorage.setItem('rvm_user', JSON.stringify(updated))
      }
    }).catch(() => {
      // Local toggle already applied (this browser looks right either way),
      // but a silent failure here means the kiosk sync (which reads
      // theme_preference from the account, not this browser) would still
      // show the old theme with no clue why — worth surfacing.
      showToast('Failed to save theme to your account — it may not follow you to the kiosk.', 'error')
    })
  }
}

// For applying a specific theme rather than flipping the current one — e.g.
// the kiosk syncing to whichever user just scanned in. Setting the DOM
// attribute directly (the old approach in KioskQrView/WelcomeView/
// GoodbyeView) never worked: App.vue's own :data-theme="theme" binding on
// .app-root sits deeper in the DOM and matches the same CSS selector, so it
// always won over anything set on document.documentElement outside Vue's
// reactivity. Going through this ref instead is what actually renders.
function setTheme(value) {
  theme.value = value === 'light' ? 'light' : 'dark'
  localStorage.setItem('rvm_theme', theme.value)
}

provide('theme', theme)
provide('toggleTheme', toggleTheme)
provide('setTheme', setTheme)

watch(theme, (val) => {
  document.documentElement.setAttribute('data-theme', val)
})

onMounted(() => {
  document.documentElement.setAttribute('data-theme', theme.value)
})
</script>

<style>
:root {
  --bg-primary:    #0d1117;
  --bg-secondary:  #161b22;
  --bg-card:       #1c2333;
  --bg-hover:      #21262d;
  --text-primary:  #e6edf3;
  --text-secondary:#8b949e;
  --text-muted:    #6e7681;
  --accent-blue:   #4e6ef2;
  --accent-purple: #a855f7;
  --accent-green:  #22c55e;
  --accent-red:    #ef4444;
  --accent-yellow: #f59e0b;
  --grad-header:   linear-gradient(135deg, #4e6ef2 0%, #a855f7 100%);
  --border:        #30363d;
  --shadow:        0 4px 24px rgba(0,0,0,0.4);
  --radius:        12px;
}

[data-theme="light"] {
  --bg-primary:    #f0f4f8;
  --bg-secondary:  #ffffff;
  --bg-card:       #ffffff;
  --bg-hover:      #f6f8fa;
  --text-primary:  #1a202c;
  --text-secondary:#4a5568;
  --text-muted:    #718096;
  --border:        #e2e8f0;
  --shadow:        0 4px 24px rgba(0,0,0,0.1);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Syne', 'DM Sans', sans-serif;
  background: var(--bg-primary);
  color: var(--text-primary);
  min-height: 100vh;
  transition: background 0.3s, color 0.3s;
}

.app-root { min-height: 100vh; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--bg-secondary); }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

/* Global transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-up-enter-active { transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1); }
.slide-up-enter-from { opacity: 0; transform: translateY(30px); }

/* Toast (app-wide — theme sync failures etc.) */
.toast {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  display: flex; align-items: center; gap: 8px;
  background: var(--bg-card); border: 1px solid var(--border);
  border-left: 4px solid var(--accent-green);
  color: var(--text-primary); padding: 12px 18px;
  border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.35);
  font-size: 13.5px; z-index: 300; max-width: 90vw;
}
.toast.error { border-left-color: var(--accent-red); }
.toast-icon { font-size: 14px; flex-shrink: 0; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translate(-50%, 8px); }
@media (prefers-reduced-motion: reduce) {
  .toast-fade-enter-active, .toast-fade-leave-active { transition: none; }
}
</style>
