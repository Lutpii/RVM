// A click that also works right after a swipe. Usage, instead of @click:
//   <button v-tap="() => doSomething()">
//
// After a fast swipe (directives/swipe.js), Chrome treats the swipe as a fling
// and uses the next tap — for about 0.3 s — to stop that fling, so no `click`
// is fired: a button tapped right after a swipe-opened modal "doesn't
// respond". Pointer events still arrive, so for touch/pen this runs the handler
// on the tap's pointerup (see utils/tapGesture.js) and ignores the click that
// may follow. Mouse and keyboard keep using the normal click.
import { isTap } from '../utils/tapGesture.js'

// A click this soon after a handled tap belongs to that same tap.
const CLICK_AFTER_TAP_MS = 700

function mounted(el, binding) {
  const state = { handler: binding.value, down: null, pointerId: null, tappedAt: 0 }

  const run = (e) => {
    if (el.disabled || typeof state.handler !== 'function') return
    state.handler(e)
  }

  const onDown = (e) => {
    if (e.pointerType === 'mouse' || !e.isPrimary) return
    state.pointerId = e.pointerId
    state.down = { x: e.clientX, y: e.clientY, t: e.timeStamp }
  }

  const onUp = (e) => {
    if (e.pointerId !== state.pointerId) return
    const down = state.down
    state.pointerId = null
    state.down = null
    if (!isTap(down, { x: e.clientX, y: e.clientY, t: e.timeStamp })) return
    state.tappedAt = performance.now()
    run(e)
  }

  const onCancel = (e) => {
    if (e.pointerId !== state.pointerId) return
    state.pointerId = null
    state.down = null
  }

  const onClick = (e) => {
    if (performance.now() - state.tappedAt < CLICK_AFTER_TAP_MS) return // already handled on pointerup
    run(e)
  }

  state.listeners = [
    ['pointerdown', onDown],
    ['pointerup', onUp],
    ['pointercancel', onCancel],
    ['click', onClick],
  ]
  for (const [type, fn] of state.listeners) el.addEventListener(type, fn)
  el._tap = state
}

function updated(el, binding) {
  if (el._tap) el._tap.handler = binding.value
}

function unmounted(el) {
  const state = el._tap
  if (!state) return
  for (const [type, fn] of state.listeners) el.removeEventListener(type, fn)
  delete el._tap
}

export default { mounted, updated, unmounted }
