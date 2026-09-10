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
    }))

  const sessionEvents = sessions.map((s) => ({
    id: `session-${s.id}`,
    kind: 'session',
    description: s.session_code,
    pointsChange: s.points_earned,
    timestamp: s.started_at,
  }))

  const redemptionEvents = redemptions.map((r) => ({
    id: `redemption-${r.id}`,
    kind: 'redemption',
    description: r.reward_name,
    pointsChange: -r.points_spent,
    timestamp: r.created_at,
  }))

  return [...pointsEvents, ...sessionEvents, ...redemptionEvents].sort(
    (a, b) => new Date(b.timestamp) - new Date(a.timestamp)
  )
}
