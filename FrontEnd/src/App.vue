<template>
  <div :class="['app-root', theme]" :data-theme="theme">
    <RouterView />
  </div>
</template>

<script setup>
import { ref, provide, onMounted, watch } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const theme = ref(localStorage.getItem('rvm_theme') || 'dark')
const auth  = useAuthStore()

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
    }).catch(() => { /* local toggle already applied; not critical if this sync fails */ })
  }
}

provide('theme', theme)
provide('toggleTheme', toggleTheme)

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
</style>
