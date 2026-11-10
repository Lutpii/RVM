import { describe, it, expect } from 'vitest'
import { mergeActivityFeed, filterActivityFeed } from './activityFeed.js'

describe('mergeActivityFeed', () => {
  it('merges points-history and session entries into one list', () => {
    const pointsHistory = [
      { id: 1, description: 'Recycled 40g of plastic', points_change: 12, created_at: '2026-09-10T08:00:00Z' },
    ]
    const sessions = [
      { id: 5, session_code: 'ABC123', points_earned: 12, started_at: '2026-09-10T07:00:00Z' },
    ]

    const feed = mergeActivityFeed(pointsHistory, sessions)

    expect(feed).toHaveLength(2)
    expect(feed[0]).toEqual({
      id: 'points-1', kind: 'points', description: 'Recycled 40g of plastic',
      pointsChange: 12, timestamp: '2026-09-10T08:00:00Z', material: null, status: 'credited',
    })
    expect(feed[1]).toEqual({
      id: 'session-5', kind: 'session', description: 'ABC123',
      pointsChange: 12, timestamp: '2026-09-10T07:00:00Z', material: null, status: null,
    })
  })

  it('sorts merged entries newest first regardless of source order', () => {
    const pointsHistory = [
      { id: 1, description: 'old', points_change: 5, created_at: '2026-09-01T00:00:00Z' },
    ]
    const sessions = [
      { id: 2, session_code: 'NEW', points_earned: 10, started_at: '2026-09-09T00:00:00Z' },
    ]

    const feed = mergeActivityFeed(pointsHistory, sessions)

    expect(feed.map((e) => e.id)).toEqual(['session-2', 'points-1'])
  })

  it('returns an empty array when both sources are empty', () => {
    expect(mergeActivityFeed([], [])).toEqual([])
  })

  it('defaults missing arguments to empty arrays', () => {
    expect(mergeActivityFeed()).toEqual([])
  })

  it('merges a third redemptions source in with the other two, still sorted newest first', () => {
    const pointsHistory = [
      { id: 1, description: 'Recycled 40g of plastic', points_change: 12, created_at: '2026-09-10T08:00:00Z' },
    ]
    const sessions = []
    const redemptions = [
      { id: 9, reward_name: 'Coffee Voucher', points_spent: 30, created_at: '2026-09-10T09:00:00Z' },
    ]

    const feed = mergeActivityFeed(pointsHistory, sessions, redemptions)

    expect(feed.map((e) => e.id)).toEqual(['redemption-9', 'points-1'])
    expect(feed[0]).toEqual({
      id: 'redemption-9', kind: 'redemption', description: 'Coffee Voucher',
      pointsChange: -30, timestamp: '2026-09-10T09:00:00Z', material: null, status: 'deducted',
    })
  })

  it('defaults the redemptions argument to an empty array too', () => {
    expect(mergeActivityFeed([{ id: 1, description: 'x', points_change: 1, created_at: '2026-01-01' }])).toHaveLength(1)
  })

  // Regression test: the backend's redeem() endpoint (RewardController::redeem) writes both a
  // RewardRedemption row AND a PointsHistory row (type: 'redeemed', description: "Redeemed: <name>")
  // for the same event, with the same created_at. Without filtering, merging in the redemptions
  // source would show every redemption twice.
  it('excludes points-history entries of type "redeemed" so a redemption is not double-counted against the redemptions source', () => {
    const pointsHistory = [
      { id: 1, description: 'Recycled 40g of plastic', points_change: 12, created_at: '2026-09-10T08:00:00Z', type: 'earned' },
      { id: 2, description: 'Redeemed: Coffee Voucher', points_change: -50, created_at: '2026-09-10T09:00:00Z', type: 'redeemed' },
    ]
    const sessions = []
    const redemptions = [
      { id: 9, reward_name: 'Coffee Voucher', points_spent: 50, created_at: '2026-09-10T09:00:00Z' },
    ]

    const feed = mergeActivityFeed(pointsHistory, sessions, redemptions)

    expect(feed.map((e) => e.id)).toEqual(['redemption-9', 'points-1'])
  })
})

describe('filterActivityFeed', () => {
  const now = new Date('2026-11-10T12:00:00Z')
  const feed = [
    { id: 's1', kind: 'session',    timestamp: '2026-11-09T10:00:00Z' },
    { id: 'r1', kind: 'redemption', timestamp: '2026-10-20T10:00:00Z' },
    { id: 'p1', kind: 'points',     timestamp: '2026-09-01T10:00:00Z' },
    { id: 'x1', kind: 'points',     timestamp: null },
    { id: 'x2', kind: 'points',     timestamp: 'not-a-date' },
  ]
  const ids = (list) => list.map((e) => e.id)

  it('returns everything with default filters', () => {
    expect(ids(filterActivityFeed(feed, {}, now))).toEqual(['s1', 'r1', 'p1', 'x1', 'x2'])
  })
  it('kind=recycling keeps only sessions', () => {
    expect(ids(filterActivityFeed(feed, { kind: 'recycling' }, now))).toEqual(['s1'])
  })
  it('kind=rewards keeps only redemptions', () => {
    expect(ids(filterActivityFeed(feed, { kind: 'rewards' }, now))).toEqual(['r1'])
  })
  it('kind=points keeps only points entries', () => {
    expect(ids(filterActivityFeed(feed, { kind: 'points' }, now))).toEqual(['p1', 'x1', 'x2'])
  })
  it('range=7d keeps the last 7 days only', () => {
    expect(ids(filterActivityFeed(feed, { range: '7d' }, now))).toEqual(['s1'])
  })
  it('range=30d keeps the last 30 days', () => {
    expect(ids(filterActivityFeed(feed, { range: '30d' }, now))).toEqual(['s1', 'r1'])
  })
  it('missing or invalid timestamps stay under all and drop under a range', () => {
    expect(ids(filterActivityFeed(feed, { kind: 'points' }, now))).toContain('x1')
    expect(ids(filterActivityFeed(feed, { kind: 'points', range: '30d' }, now))).toEqual([])
  })
  it('combines kind and range', () => {
    expect(ids(filterActivityFeed(feed, { kind: 'rewards', range: '7d' }, now))).toEqual([])
  })
})

describe('material and status (U05)', () => {
  const now = new Date('2026-11-10T12:00:00Z')
  const merged = mergeActivityFeed(
    [
      { id: 1, type: 'earned', description: 'a', points_change: 5, created_at: '2026-11-09T10:00:00Z', transaction: { material_selected: 'plastic', is_valid: 1 } },
      { id: 2, type: 'deducted', description: 'b', points_change: -2, created_at: '2026-11-08T10:00:00Z', transaction: { material_selected: 'glass', is_valid: 0 } },
      { id: 3, type: 'earned', description: 'c', points_change: 4, created_at: '2026-10-01T10:00:00Z', transaction: { material_selected: 'aluminum', is_valid: 1 } },
      { id: 4, type: 'earned', description: 'admin', points_change: 10, created_at: '2026-11-09T09:00:00Z', transaction: null },
    ],
    [{ id: 7, session_code: 'S7', points_earned: 0, started_at: '2026-11-09T08:00:00Z' }],
    [{ id: 9, reward_name: 'Mug', points_spent: 50, created_at: '2026-11-07T10:00:00Z' }],
  )
  const ids = (list) => list.map((e) => e.id)

  it('maps the transaction material and the credited/deducted status', () => {
    const p1 = merged.find((e) => e.id === 'points-1')
    const p2 = merged.find((e) => e.id === 'points-2')
    expect(p1.material).toBe('plastic')
    expect(p1.status).toBe('credited')
    expect(p2.material).toBe('glass')
    expect(p2.status).toBe('deducted')
    expect(merged.find((e) => e.id === 'session-7').status).toBeNull()
    expect(merged.find((e) => e.id === 'redemption-9').status).toBe('deducted')
    expect(merged.find((e) => e.id === 'points-4').material).toBeNull()
  })
  it('material filter keeps only that material and drops entries without one', () => {
    expect(ids(filterActivityFeed(merged, { material: 'plastic' }, now))).toEqual(['points-1'])
    expect(ids(filterActivityFeed(merged, { material: 'all' }, now))).toContain('points-4')
  })
  it('status filter keeps credited or deducted entries, redemptions count as deducted', () => {
    expect(ids(filterActivityFeed(merged, { status: 'deducted' }, now))).toEqual(['points-2', 'redemption-9'])
    expect(ids(filterActivityFeed(merged, { status: 'credited' }, now))).toEqual(['points-1', 'points-4', 'points-3'])
  })
  it('combines kind, material and range', () => {
    expect(ids(filterActivityFeed(merged, { kind: 'points', material: 'aluminum', range: '7d' }, now))).toEqual([])
    expect(ids(filterActivityFeed(merged, { kind: 'points', material: 'aluminum' }, now))).toEqual(['points-3'])
  })
})
