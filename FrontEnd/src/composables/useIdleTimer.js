// Kiosk idle timer: after idleMs without input, shows a countdown overlay
// for countdownMs, then calls onExpire once. Runs only while isActive.
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

export const IDLE_MS = 60000
export const IDLE_COUNTDOWN_MS = 10000
const TICK_MS = 100

export function useIdleTimer({ isActive, onExpire, idleMs = IDLE_MS, countdownMs = IDLE_COUNTDOWN_MS }) {
  const showOverlay = ref(false)
  const msLeft = ref(countdownMs)
  let timer = null
  let tick = null
  let deadline = 0

  const clear = () => {
    if (timer) { clearTimeout(timer); timer = null }
    if (tick) { clearInterval(tick); tick = null }
  }

  const stop = () => {
    clear()
    showOverlay.value = false
    msLeft.value = countdownMs
  }

  const start = () => {
    stop()
    if (!isActive.value) return
    timer = setTimeout(() => {
      timer = null
      showOverlay.value = true
      deadline = Date.now() + countdownMs
      msLeft.value = countdownMs
      tick = setInterval(() => {
        msLeft.value = Math.max(0, deadline - Date.now())
        if (msLeft.value <= 0) {
          clear()
          showOverlay.value = false
          onExpire()
        }
      }, TICK_MS)
    }, idleMs)
  }

  const poke = () => { if (isActive.value) start() }

  watch(isActive, (on) => { if (on) start(); else stop() })

  onMounted(() => {
    window.addEventListener('pointerdown', poke)
    window.addEventListener('keydown', poke)
    if (isActive.value) start()
  })
  onBeforeUnmount(() => {
    window.removeEventListener('pointerdown', poke)
    window.removeEventListener('keydown', poke)
    clear()
  })

  return { showOverlay, msLeft, poke }
}
