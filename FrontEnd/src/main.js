import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import App from './App.vue'
import router from './router'
import { messages } from './locales'
import scrollReveal from './directives/scrollReveal'
import swipe from './directives/swipe'
import tap from './directives/tap'
import './assets/main.css'
import api, { hasMachineKey } from './services/api'
import { startHeartbeat } from './services/heartbeat'
import { getHardwareState } from './services/compactor'

const pinia = createPinia()

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('rvm_lang') || 'en',
  fallbackLocale: 'en',
  messages,
})

const app = createApp(App)
app.use(pinia)
app.use(router)
app.use(i18n)
app.directive('reveal', scrollReveal)
app.directive('swipe', swipe)
app.directive('tap', tap)
app.mount('#app')

// Kiosk builds (VITE_MACHINE_KEY set) report a heartbeat + fault state every
// minute for as long as the kiosk app is open, whichever screen it is on.
if (hasMachineKey) {
  startHeartbeat({ post: (url, body) => api.post(url, body), getState: () => getHardwareState() })
}
