import { describe, it, expect } from 'vitest'
import { mergeActivityFeed } from './activityFeed.js'

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
      pointsChange: 12, timestamp: '2026-09-10T08:00:00Z',
    })
    expect(feed[1]).toEqual({
      id: 'session-5', kind: 'session', description: 'ABC123',
      pointsChange: 12, timestamp: '2026-09-10T07:00:00Z',
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
      pointsChange: -30, timestamp: '2026-09-10T09:00:00Z',
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
