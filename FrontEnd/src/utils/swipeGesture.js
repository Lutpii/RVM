// Pure decision logic for horizontal card swipes (used by directives/swipe.js).
// Kept free of DOM access so the thresholds can be unit-tested directly.

// Movement (px) before we decide whether the gesture is a swipe or a scroll.
export const AXIS_SLOP_PX = 10
// Fraction of the card width a swipe has to travel to count on release...
export const COMMIT_RATIO = 0.35
// ...but never less than this, so narrow cards don't trigger on a nudge.
export const MIN_COMMIT_PX = 80
// A quick flick also counts, like most swipe UIs: Hammer.js' swipe default
// and react-tinder-card's default velocity threshold are both 0.3 px/ms.
export const FLICK_VELOCITY = 0.3
// Minimum travel for a flick. Hammer.js uses 10px; ours is higher because a
// right swipe saves the review immediately.
export const MIN_FLICK_PX = 40
// Only the last bit of the gesture decides the release velocity.
export const VELOCITY_WINDOW_MS = 100

// 'x' once the gesture is clearly sideways, 'y' once it is clearly vertical
// (ties go to 'y' so page scrolling always wins), null while still undecided.
export function lockAxis(dx, dy, slop = AXIS_SLOP_PX) {
  if (Math.max(Math.abs(dx), Math.abs(dy)) < slop) return null
  return Math.abs(dx) > Math.abs(dy) ? 'x' : 'y'
}

export function commitDistance(width) {
  return Math.max(width * COMMIT_RATIO, MIN_COMMIT_PX)
}

// Direction the card was flung on release, or null if it should snap back.
// Either dragged past the distance threshold, or flicked (fast enough, far
// enough, and still moving the same way it was dragged).
// `velocity` is signed px/ms (see releaseVelocity).
export function resolveSwipe(dx, width, velocity = 0) {
  const dir = dx > 0 ? 'right' : 'left'
  if (Math.abs(dx) >= commitDistance(width)) return dir
  const flicked = Math.abs(dx) >= MIN_FLICK_PX
    && Math.abs(velocity) >= FLICK_VELOCITY
    && Math.sign(velocity) === Math.sign(dx)
  return flicked ? dir : null
}

// Signed horizontal velocity (px/ms) at release, from {x, t} samples in
// chronological order (the release point included). Only samples within the
// last VELOCITY_WINDOW_MS count, so a finger that stopped before lifting
// reads as 0 rather than as the speed it had earlier.
export function releaseVelocity(samples) {
  if (samples.length < 2) return 0
  const last = samples[samples.length - 1]
  const recent = samples.filter(s => s.t >= last.t - VELOCITY_WINDOW_MS)
  const first = recent[0]
  const dt = last.t - first.t
  return dt > 0 ? (last.x - first.x) / dt : 0
}

// 0..1 for the drag feedback (hint opacity), reaching 1 at the threshold.
export function swipeProgress(dx, width) {
  return Math.min(Math.abs(dx) / commitDistance(width), 1)
}
