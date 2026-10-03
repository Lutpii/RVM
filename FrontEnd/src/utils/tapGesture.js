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
