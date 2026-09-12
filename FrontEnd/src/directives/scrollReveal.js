// Scroll-triggered entrance reveal for browser-only views (design spec §7,
// "Scroll Reveal — Subtle" preset). Usage:
//   <div v-reveal>...</div>                  — reveals on its own
//   <div v-reveal="index" v-for="...">...</div> — staggered by loop index,
//     capped at MAX_STAGGER so a long list doesn't keep adding delay forever.
const MAX_STAGGER = 8
const STEP_MS = 60

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function mounted(el, binding) {
  el.classList.add('scroll-reveal')

  if (prefersReducedMotion()) {
    el.classList.add('is-visible')
    return
  }

  const index = typeof binding.value === 'number' ? Math.min(binding.value, MAX_STAGGER) : 0
  el.style.transitionDelay = `${index * STEP_MS}ms`

  const observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        el.classList.add('is-visible')
        observer.unobserve(el)
      }
    }
  }, { threshold: 0.15 })

  observer.observe(el)
  el._revealObserver = observer
}

function unmounted(el) {
  el._revealObserver?.disconnect()
  delete el._revealObserver
}

export default { mounted, unmounted }
