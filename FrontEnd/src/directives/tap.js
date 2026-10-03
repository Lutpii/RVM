// A click that also works right after a swipe. Usage, instead of @click:
//   <button v-tap="() => doSomething()">
//
// After a fast swipe (directives/swipe.js), Chrome treats the swipe as a fling
// and uses the next tap — for about 0.3 s — to stop that fling, so no `click`
// is fired: a button tapped right after a swipe-opened modal "doesn't
// respond". Pointer events still arrive, so for touch/pen this runs the handler
// on the tap's pointerup (see utils/tapGesture.js). Mouse and keyboard keep
// using the normal click.
//
// The browser still sends its own click a moment after that pointerup. By then
// the tap has usually closed or opened a dialog, so that click would land on
// whatever is under the finger now: a card photo (reopening the photo viewer),
// a fresh dialog's backdrop (closing it), or even a material button (saving
// the wrong class). One document-level guard swallows that ghost click.
import { isGhostClick, isTap } from '../utils/tapGesture.js'

let lastTap = null // {x, y, t} of the last tap handled on pointerup
let guardInstalled = false

function installGhostClickGuard() {
  if (guardInstalled) return
  guardInstalled = true
  document.addEventListener('click', (e) => {
    if (!isGhostClick(lastTap, { x: e.clientX, y: e.clientY, t: e.timeStamp })) return
    lastTap = null // one ghost per tap
    e.preventDefault()
    e.stopImmediatePropagation()
  }, true) // capture: runs before any element's own click handler
}

function mounted(el, binding) {
  installGhostClickGuard()
  const state = { handler: binding.value, down: null, pointerId: null }

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
    const up = { x: e.clientX, y: e.clientY, t: e.timeStamp }
    if (!isTap(down, up)) return
    lastTap = up
    run(e)
  }

  const onCancel = (e) => {
    if (e.pointerId !== state.pointerId) return
    state.pointerId = null
    state.down = null
  }

  // Mouse, keyboard (Enter/Space) — and a touch click only if pointerup didn't
  // already handle it (the guard above drops that one before it gets here).
  const onClick = (e) => run(e)

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
