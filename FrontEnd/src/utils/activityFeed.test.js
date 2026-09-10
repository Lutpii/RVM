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
})
