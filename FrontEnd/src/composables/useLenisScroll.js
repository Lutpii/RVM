import Lenis from 'lenis'
import { onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'

// Route names whose component can render on the Raspberry Pi kiosk display —
// 2 kiosk-exclusive + 4 shared routes, per the frontend styling overhaul
// spec §6 — plus 'admin', excluded for a different reason (see below). Lenis
// must never run while any of these is the active route, even the 4 shared
// ones reached via a plain phone/browser path (e.g. /session, /welcome) — §9.
const NO_LENIS_ROUTES = new Set([
  'kiosk-landing', 'kiosk-qr', 'kiosk-session', 'kiosk-summary',
  'welcome', 'thank-you', 'session', 'session-summary',
  // 'admin': AdminView.vue is a fixed sidebar/topbar app-shell whose own
  // `.admin-page { overflow: hidden }` means it never uses window/document
  // scroll in the first place (only its internal `.admin-scroll-area` div
  // does) — Lenis's default window-hooking mode has nothing to attach to
  // there, and inertia scrolling over dense data tables is undesirable on
  // an admin dashboard anyway.
  'admin',
])

let lenis = null
let rafId = null
let reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

function start() {
  if (lenis || reducedMotion) return
  lenis = new Lenis({ duration: 1.1, smoothWheel: true })
  const raf = (time) => {
    lenis?.raf(time)
    rafId = requestAnimationFrame(raf)
  }
  rafId = requestAnimationFrame(raf)
}

function stop() {
  if (rafId != null) cancelAnimationFrame(rafId)
  rafId = null
  lenis?.destroy()
  lenis = null
}

function sync(routeName) {
  if (reducedMotion || NO_LENIS_ROUTES.has(routeName)) {
    stop()
  } else {
    start()
  }
}

export function useLenisScroll() {
  const route = useRoute()

  // Live-updates if the OS-level setting changes mid-session, not just at
  // page load — matches how strictly spec §7 phrases "respects
  // prefers-reduced-motion" (a session-long guarantee, not a one-time check).
  const mql = window.matchMedia('(prefers-reduced-motion: reduce)')
  const onChange = (e) => {
    reducedMotion = e.matches
    sync(route.name)
  }
  mql.addEventListener('change', onChange)

  watch(() => route.name, (name) => sync(name), { immediate: true })

  onUnmounted(() => {
    mql.removeEventListener('change', onChange)
    stop()
  })
}
