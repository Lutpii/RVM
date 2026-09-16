import { onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { IDLE_ACTIVITY_STORAGE_KEY, readIdleActivity, clearIdleActivity } from '@/utils/idleActivity'

const IDLE_TIMEOUT_MS = 30 * 60 * 1000
// Avoid writing to localStorage on every mousemove — only the elapsed-time
// resolution actually matters for a 30-minute timeout.
const ACTIVITY_WRITE_THROTTLE_MS = 5000
const ACTIVITY_EVENTS = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll']

// Called once per component tree (from App.vue) — arms/disarms itself as the
// user logs in/out or enters/leaves the kiosk flow, so callers don't need to
// think about when it's active.
export function useIdleLogout(showToast) {
  const auth = useAuthStore()
  const route = useRoute()
  const router = useRouter()
  const { t } = useI18n()

  let timer = null
  let lastWrite = 0
  let armed = false
  let expiring = false

  function clearTimer() {
    if (timer != null) clearTimeout(timer)
    timer = null
  }

  async function expire() {
    // Re-entrancy guard: the backend enforces the same idle timeout on its
    // own clock (EnforceIdleTimeout middleware) and can 401 the /auth/logout
    // call below via the global axios interceptor, which does its own hard
    // `window.location.hash = '/login'` redirect concurrently with this
    // function's — without this guard the two races and nests a `redirect`
    // query inside itself (?redirect=/login?redirect=/dashboard).
    if (expiring) return
    expiring = true
    disarm()
    await auth.logout()
    clearIdleActivity()
    showToast?.(t('auth.sessionExpired'), 'error')
    if (route.name !== 'login') {
      router.push({ name: 'login', query: { redirect: route.fullPath } })
    }
    expiring = false
  }

  // (Re)schedules the timeout relative to a given "last activity" timestamp —
  // used both when arming fresh (login, or app reopened after being closed
  // overnight — the elapsed time already exceeds the timeout) and each time
  // activity resets the clock.
  function scheduleFrom(lastActivityTs) {
    clearTimer()
    const remaining = IDLE_TIMEOUT_MS - (Date.now() - lastActivityTs)
    if (remaining <= 0) {
      expire()
      return
    }
    timer = setTimeout(expire, remaining)
  }

  function onActivity() {
    const now = Date.now()
    if (now - lastWrite < ACTIVITY_WRITE_THROTTLE_MS) return
    lastWrite = now
    localStorage.setItem(IDLE_ACTIVITY_STORAGE_KEY, String(now))
    scheduleFrom(now)
  }

  function attachListeners() {
    ACTIVITY_EVENTS.forEach((evt) => window.addEventListener(evt, onActivity, { passive: true }))
  }

  function detachListeners() {
    ACTIVITY_EVENTS.forEach((evt) => window.removeEventListener(evt, onActivity))
  }

  function arm() {
    if (armed) return
    armed = true
    const last = readIdleActivity() ?? Date.now()
    localStorage.setItem(IDLE_ACTIVITY_STORAGE_KEY, String(last))
    attachListeners()
    scheduleFrom(last)
  }

  function disarm() {
    if (!armed) return
    armed = false
    detachListeners()
    clearTimer()
  }

  // The kiosk flow (a physical machine's own browser) authenticates guests
  // and phone-scanned users differently and must never be idle-logged-out
  // out from under the hardware — see router/index.js's suspendForKiosk/
  // rehydrate handling for the same distinction.
  function isKioskPath(path) {
    return path.startsWith('/kiosk/')
  }

  watch(
    [() => auth.isLoggedIn, () => route.path],
    ([loggedIn, path]) => {
      if (loggedIn && !isKioskPath(path)) arm()
      else disarm()
    },
    { immediate: true }
  )

  onUnmounted(disarm)
}
