// FrontEnd/src/utils/resolveCachedPoints.js
// Decides whether a locally-cached points value (saved on logout, to survive
// a kiosk-session-just-ended race where the backend hasn't committed the
// points update yet) should override what the backend just returned on
// login. Only trusts the cache within maxAgeMs of when it was saved and only
// when it's actually higher — an older or lower cached value is stale and
// must never win, or a genuine points decrease (e.g. redeeming a reward on
// a different device) would be silently reverted.
export function resolveCachedPoints(raw, backendPoints, now = Date.now(), maxAgeMs = 60_000) {
  if (raw === null || raw === undefined) return backendPoints

  let cached = null
  try {
    const parsed = JSON.parse(raw)
    if (parsed && typeof parsed.points === 'number' && typeof parsed.savedAt === 'number') {
      cached = parsed
    }
  } catch {
    // Pre-migration format (a bare number string) or corrupt — treat as stale.
  }

  if (!cached) return backendPoints
  if (now - cached.savedAt >= maxAgeMs) return backendPoints
  if (cached.points <= (backendPoints ?? 0)) return backendPoints

  return cached.points
}
