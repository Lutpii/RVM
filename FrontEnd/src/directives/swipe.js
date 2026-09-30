// Horizontal swipe on a card, for touch/pen only (mouse users keep the
// buttons). Usage:
//   <div v-swipe="{ enabled, onLeft: () => ..., onRight: () => ... }">
//
// While dragging, the card follows the finger and gets
// data-swipe-dir="left|right" plus a --swipe-progress (0..1) CSS variable for
// feedback styling. Released past the distance threshold, or flicked fast
// enough (both in utils/swipeGesture.js), the card flies out that way and the
// matching handler runs. The handler returns
// (or resolves to) false to snap the card back, e.g. when the action is not
// allowed or it only opened a modal; anything else leaves the card off-screen
// for the list to remove it. A mostly-vertical move is left to page scrolling.
import {
  lockAxis, releaseVelocity, resolveSwipe, swipeProgress, VELOCITY_WINDOW_MS,
} from '../utils/swipeGesture.js'

const SNAP_MS = 200

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function isEnabled(opts) {
  return opts.enabled !== false
}

function setOffset(el, dx, animate) {
  el.style.transition = animate && !prefersReducedMotion() ? `transform ${SNAP_MS}ms ease` : 'none'
  el.style.transform = dx ? `translateX(${dx}px)` : ''
}

function setFeedback(el, dx) {
  if (!dx) {
    delete el.dataset.swipeDir
    el.style.removeProperty('--swipe-progress')
    return
  }
  el.dataset.swipeDir = dx > 0 ? 'right' : 'left'
  el.style.setProperty('--swipe-progress', swipeProgress(dx, el.offsetWidth).toFixed(3))
}

function snapBack(el) {
  setOffset(el, 0, true)
  setFeedback(el, 0)
}

function applyTouchAction(el, opts) {
  // Let the browser keep vertical scrolling and pinch-zoom; horizontal moves
  // come to us as pointer events instead of panning the page.
  el.style.touchAction = isEnabled(opts) ? 'pan-y pinch-zoom' : ''
}

function mounted(el, binding) {
  const state = {
    opts: binding.value || {},
    pointerId: null,
    startX: 0,
    startY: 0,
    dx: 0,
    axis: null,
    samples: [], // recent {x, t} for the release velocity
    busy: false,
    suppressClick: false,
  }

  const addSample = (e) => {
    const t = e.timeStamp
    state.samples.push({ x: e.clientX, t })
    // Anything older than the velocity window is never used again.
    while (state.samples.length > 2 && state.samples[0].t < t - VELOCITY_WINDOW_MS * 2) state.samples.shift()
  }

  const onDown = (e) => {
    if (!isEnabled(state.opts) || e.pointerType === 'mouse' || !e.isPrimary) return
    if (state.pointerId !== null || state.busy) return
    state.pointerId = e.pointerId
    state.startX = e.clientX
    state.startY = e.clientY
    state.dx = 0
    state.axis = null
    state.samples = []
    addSample(e)
  }

  const onMove = (e) => {
    if (e.pointerId !== state.pointerId) return
    const dx = e.clientX - state.startX
    const dy = e.clientY - state.startY
    addSample(e)
    if (!state.axis) {
      state.axis = lockAxis(dx, dy)
      if (!state.axis) return
      if (state.axis === 'y') {
        state.pointerId = null // a scroll, not a swipe
        return
      }
      // Keep receiving moves if the finger drifts off the card. Can throw if
      // the pointer is already gone; the swipe still works without it.
      try { el.setPointerCapture(e.pointerId) } catch { /* not capturable */ }
    }
    state.dx = dx
    setOffset(el, dx, false)
    setFeedback(el, dx)
  }

  const onUp = async (e) => {
    if (e.pointerId !== state.pointerId) return
    state.pointerId = null
    if (state.axis !== 'x') return

    // The finger may lift over a button; don't let that count as a tap.
    state.suppressClick = true
    setTimeout(() => { state.suppressClick = false }, 0)

    addSample(e)
    const dir = resolveSwipe(state.dx, el.offsetWidth, releaseVelocity(state.samples))
    const handler = dir === 'right' ? state.opts.onRight : dir === 'left' ? state.opts.onLeft : null
    if (!handler) {
      snapBack(el)
      return
    }

    setOffset(el, dir === 'right' ? el.offsetWidth : -el.offsetWidth, true)
    state.busy = true
    let done = false
    try {
      done = (await handler()) !== false
    } catch {
      done = false
    } finally {
      state.busy = false
    }
    if (!done) {
      snapBack(el)
      return
    }
    // Normally the list drops the card (and its leave transition finishes)
    // well within this window. If it is somehow still here, don't leave it
    // stuck invisible off-screen.
    setTimeout(() => { if (el.isConnected) snapBack(el) }, SNAP_MS + 600)
  }

  const onCancel = (e) => {
    if (e.pointerId !== state.pointerId) return
    state.pointerId = null
    snapBack(el)
  }

  const onClickCapture = (e) => {
    if (!state.suppressClick) return
    e.stopPropagation()
    e.preventDefault()
  }

  state.listeners = [
    ['pointerdown', onDown, false],
    ['pointermove', onMove, false],
    ['pointerup', onUp, false],
    ['pointercancel', onCancel, false],
    ['click', onClickCapture, true],
  ]
  for (const [type, fn, capture] of state.listeners) el.addEventListener(type, fn, capture)

  el._swipe = state
  applyTouchAction(el, state.opts)
}

function updated(el, binding) {
  const state = el._swipe
  if (!state) return
  state.opts = binding.value || {}
  applyTouchAction(el, state.opts)
}

function unmounted(el) {
  const state = el._swipe
  if (!state) return
  for (const [type, fn, capture] of state.listeners) el.removeEventListener(type, fn, capture)
  delete el._swipe
}

export default { mounted, updated, unmounted }
