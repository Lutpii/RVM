function statusOf(change) {
  if (change > 0) return 'credited'
  if (change < 0) return 'deducted'
  return null
}

export function mergeActivityFeed(pointsHistoryEntries = [], sessions = [], redemptions = []) {
  // Exclude type: 'redeemed' points-history rows: RewardController::redeem() writes one of
  // these alongside every RewardRedemption row (same event, same created_at), so leaving them
  // in here would show each redemption twice once the redemptions source below is merged in.
  const pointsEvents = pointsHistoryEntries
    .filter((h) => h.type !== 'redeemed')
    .map((h) => ({
      id: `points-${h.id}`,
      kind: 'points',
      description: h.description,
      pointsChange: h.points_change,
      timestamp: h.created_at,
      material: h.transaction?.material_selected ?? null,
      status: statusOf(h.points_change),
    }))

  const sessionEvents = sessions.map((s) => ({
    id: `session-${s.id}`,
    kind: 'session',
    description: s.session_code,
    pointsChange: s.points_earned,
    timestamp: s.started_at,
    // a session summarises its items, which already appear as points rows
    material: null,
    status: null,
  }))

  const redemptionEvents = redemptions.map((r) => ({
    id: `redemption-${r.id}`,
    kind: 'redemption',
    description: r.reward_name,
    pointsChange: -r.points_spent,
    timestamp: r.created_at,
    material: null,
    status: 'deducted',
  }))

  return [...pointsEvents, ...sessionEvents, ...redemptionEvents].sort(
    (a, b) => new Date(b.timestamp) - new Date(a.timestamp)
  )
}

const RANGE_DAYS = { '7d': 7, '30d': 30 }
const KIND_FOR_FILTER = { recycling: 'session', rewards: 'redemption', points: 'points' }

// Filters a merged activity feed by type and recent date range (U05).
// Pure: `now` is passed in. Entries with a missing or unparsable timestamp
// stay visible under range 'all' and are left out of a ranged view.
export function filterActivityFeed(entries = [], { kind = 'all', range = 'all', material = 'all', status = 'all' } = {}, now = new Date()) {
  const wantedKind = KIND_FOR_FILTER[kind] ?? null
  const days = RANGE_DAYS[range] ?? null
  const cutoff = days === null ? null : now.getTime() - days * 24 * 60 * 60 * 1000

  return entries.filter((e) => {
    if (wantedKind && e.kind !== wantedKind) return false
    if (material !== 'all' && e.material !== material) return false
    if (status !== 'all' && e.status !== status) return false
    if (cutoff === null) return true
    const t = e.timestamp ? new Date(e.timestamp).getTime() : NaN
    return Number.isFinite(t) && t >= cutoff
  })
}
