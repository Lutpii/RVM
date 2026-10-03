// Pure decision logic for a touch/pen tap (used by directives/tap.js).
// Kept free of DOM access so the thresholds can be unit-tested directly.

// A finger that drifts further than this was scrolling, not tapping.
export const TAP_MAX_MOVE_PX = 10
// Longer than this is a long-press, not a tap.
export const TAP_MAX_MS = 600

// `down` / `up` are {x, y, t}; `down` is null if the press didn't start here.
export function isTap(down, up) {
  if (!down) return false
  return Math.abs(up.x - down.x) <= TAP_MAX_MOVE_PX
    && Math.abs(up.y - down.y) <= TAP_MAX_MOVE_PX
    && up.t - down.t <= TAP_MAX_MS
}

// After a tap has been handled on pointerup, the browser still sends its own
// click at the same spot a moment later. If the tap closed or opened a
// dialog, that click lands on whatever is under the finger now (a photo, a
// backdrop, another button). A click this soon and this close is that ghost.
export const GHOST_CLICK_MS = 700
export const GHOST_CLICK_RADIUS_PX = 30

// `tap` / `click` are {x, y, t}; `tap` is null if no tap was handled.
export function isGhostClick(tap, click) {
  if (!tap) return false
  return click.t - tap.t <= GHOST_CLICK_MS
    && Math.abs(click.x - tap.x) <= GHOST_CLICK_RADIUS_PX
    && Math.abs(click.y - tap.y) <= GHOST_CLICK_RADIUS_PX
}
