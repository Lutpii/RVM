// FrontEnd/src/utils/activityTimeline.js
// Builds the recycler's Activity timeline (U05). A session's items are folded
// into the session row instead of being listed again, so points are not shown
// twice. Pure functions; the view passes `now`.

const DAY_MS = 24 * 60 * 60 * 1000
const RANGE_DAYS = { '7d': 7, '30d': 30 }

function time(ts) {
  const t = ts ? new Date(ts).getTime() : NaN
  return Number.isFinite(t) ? t : NaN
}

export function buildActivityTimeline(pointsHistory = [], sessions = [], redemptions = []) {
  const sessionIds = new Set(sessions.map((s) => s.id))
  const itemsBySession = new Map()
  const loose = []

  // 'redeemed' rows mirror a RewardRedemption (same event); the redemption source covers them.
  for (const h of pointsHistory.filter((r) => r.type !== 'redeemed')) {
    if (h.session_id != null && sessionIds.has(h.session_id)) {
      if (!itemsBySession.has(h.session_id)) itemsBySession.set(h.session_id, [])
      itemsBySession.get(h.session_id).push(h)
    } else {
      loose.push(h)
    }
  }

  const sessionEntries = sessions.map((s) => {
    const items = itemsBySession.get(s.id) || []
    const materials = [...new Set(items.map((i) => i.transaction?.material_selected).filter(Boolean))]
    return {
      id: `session-${s.id}`,
      kind: 'session',
      sessionCode: s.session_code,
      machineName: s.machine?.name ?? null,
      itemCount: items.length || s.total_items || 0,
      materials,
      pointsChange: items.length ? items.reduce((sum, i) => sum + (i.points_change || 0), 0) : (s.points_earned || 0),
      timestamp: s.started_at,
    }
  })

  const looseEntries = loose.map((h) => {
    const material = h.transaction?.material_selected ?? null
    const isItem = h.session_id != null || material !== null
    return {
      id: `${isItem ? 'points' : 'adjustment'}-${h.id}`,
      kind: isItem ? 'item' : 'adjustment',
      title: h.description,
      material,
      pointsChange: h.points_change,
      timestamp: h.created_at,
    }
  })

  const redemptionEntries = redemptions.map((r) => ({
    id: `redemption-${r.id}`,
    kind: 'redemption',
    title: r.reward_name,
    pointsChange: -r.points_spent,
    timestamp: r.created_at,
  }))

  return [...sessionEntries, ...looseEntries, ...redemptionEntries]
    .sort((a, b) => (time(b.timestamp) || 0) - (time(a.timestamp) || 0))
}

export function filterTimeline(entries = [], { tab = 'all', range = 'all', material = 'all' } = {}, now = new Date()) {
  const days = RANGE_DAYS[range] ?? null
  const cutoff = days === null ? null : now.getTime() - days * DAY_MS
  return entries.filter((e) => {
    if (tab === 'recycling' && !(e.kind === 'session' || e.kind === 'item')) return false
    if (tab === 'rewards' && e.kind !== 'redemption') return false
    if (material !== 'all') {
      const has = e.kind === 'session' ? (e.materials || []).includes(material) : e.material === material
      if (!has) return false
    }
    if (cutoff !== null) {
      const t = time(e.timestamp)
      if (!Number.isFinite(t) || t < cutoff) return false
    }
    return true
  })
}

// How many filters behind the Filter button are set (the tab is not one of them).
export function activeFilterCount({ range = 'all', material = 'all' } = {}) {
  return (range !== 'all' ? 1 : 0) + (material !== 'all' ? 1 : 0)
}

// Groups entries by local calendar day: 'today', 'yesterday', 'date', or 'unknown' (last).
export function groupByDay(entries = [], now = new Date()) {
  const startOf = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime()
  const today = startOf(now)
  const groups = new Map()
  const unknown = []
  for (const e of entries) {
    const t = time(e.timestamp)
    if (!Number.isFinite(t)) { unknown.push(e); continue }
    const day = startOf(new Date(t))
    if (!groups.has(day)) groups.set(day, [])
    groups.get(day).push(e)
  }
  const out = [...groups.entries()].sort((a, b) => b[0] - a[0]).map(([day, list]) => ({
    key: String(day),
    kind: day === today ? 'today' : day === today - DAY_MS ? 'yesterday' : 'date',
    date: new Date(day),
    entries: list,
  }))
  if (unknown.length) out.push({ key: 'unknown', kind: 'unknown', date: null, entries: unknown })
  return out
}
