// Shared by the auth store (reset/clear around a session's lifetime) and
// useIdleLogout (read/reset around actual user activity) so there is exactly
// one place that owns this key — a stale value surviving here past a logout
// is what silently force-expired the very next login (see setAuth/clearAuth
// in store/auth.js).
export const IDLE_ACTIVITY_STORAGE_KEY = 'rvm_last_activity'

export function readIdleActivity() {
  const raw = localStorage.getItem(IDLE_ACTIVITY_STORAGE_KEY)
  const n = raw ? Number(raw) : NaN
  return Number.isFinite(n) ? n : null
}

// Call whenever a session actually starts (login/register/OTP verify) — a
// fresh session is definitionally "active right now," regardless of whatever
// timestamp (or lack of one) was left over from before.
export function resetIdleActivity() {
  localStorage.setItem(IDLE_ACTIVITY_STORAGE_KEY, String(Date.now()))
}

export function clearIdleActivity() {
  localStorage.removeItem(IDLE_ACTIVITY_STORAGE_KEY)
}
