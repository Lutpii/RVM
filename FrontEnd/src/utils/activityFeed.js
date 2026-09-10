export function mergeActivityFeed(pointsHistoryEntries = [], sessions = []) {
  const pointsEvents = pointsHistoryEntries.map((h) => ({
    id: `points-${h.id}`,
    kind: 'points',
    description: h.description,
    pointsChange: h.points_change,
    timestamp: h.created_at,
  }))

  const sessionEvents = sessions.map((s) => ({
    id: `session-${s.id}`,
    kind: 'session',
    description: s.session_code,
    pointsChange: s.points_earned,
    timestamp: s.started_at,
  }))

  return [...pointsEvents, ...sessionEvents].sort(
    (a, b) => new Date(b.timestamp) - new Date(a.timestamp)
  )
}
