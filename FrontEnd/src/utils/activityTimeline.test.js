import { describe, it, expect } from 'vitest'
import { buildActivityTimeline, filterTimeline, groupByDay, activeFilterCount } from './activityTimeline.js'

const history = [
  // three items of session 7
  { id: 1, type: 'earned', points_change: 6, session_id: 7, created_at: '2026-11-10T03:01:00Z', description: 'Recycled 20g of plastic', transaction: { material_selected: 'plastic', is_valid: 1 } },
  { id: 2, type: 'earned', points_change: 8, session_id: 7, created_at: '2026-11-10T03:02:00Z', description: 'Recycled 20g of aluminum', transaction: { material_selected: 'aluminum', is_valid: 1 } },
  { id: 3, type: 'deducted', points_change: -2, session_id: 7, created_at: '2026-11-10T03:03:00Z', description: 'Invalid item', transaction: { material_selected: 'glass', is_valid: 0 } },
  // admin adjustment, no session
  { id: 4, type: 'earned', points_change: 8, session_id: null, created_at: '2026-11-09T05:00:00Z', description: 'Admin adjustment by Adi', transaction: null },
  // item of a session that is not in the loaded sessions page
  { id: 5, type: 'earned', points_change: 18, session_id: 99, created_at: '2026-10-01T05:00:00Z', description: 'Recycled 40g of aluminum', transaction: { material_selected: 'aluminum', is_valid: 1 } },
  // redeem mirror row: must not show twice (the redemption source already has it)
  { id: 6, type: 'redeemed', points_change: -100, session_id: null, created_at: '2026-11-08T05:00:00Z', description: 'Redeemed: Mug', transaction: null },
]
const sessions = [
  { id: 7, session_code: 'S7', points_earned: 14, total_items: 3, started_at: '2026-11-10T03:00:00Z', machine: { name: 'RVM DSME' } },
  { id: 8, session_code: 'S8', points_earned: 0, total_items: 0, started_at: '2026-11-07T03:00:00Z', machine: { name: 'RVM UMPSA Gambang' } },
]
const redemptions = [{ id: 9, reward_name: 'Mug', points_spent: 100, created_at: '2026-11-08T05:00:00Z' }]

const timeline = buildActivityTimeline(history, sessions, redemptions)
const byId = (id) => timeline.find((e) => e.id === id)
const ids = (list) => list.map((e) => e.id)

describe('buildActivityTimeline', () => {
  it('folds a session\'s items into the session instead of listing them again', () => {
    expect(ids(timeline)).toEqual(['session-7', 'adjustment-4', 'redemption-9', 'session-8', 'points-5'])
  })

  it('a session carries machine, item count, materials and the net points of its items', () => {
    expect(byId('session-7')).toMatchObject({
      kind: 'session', sessionCode: 'S7', machineName: 'RVM DSME', itemCount: 3,
      materials: ['plastic', 'aluminum', 'glass'], pointsChange: 12,
    })
  })

  it('a session with no loaded items falls back to its own totals', () => {
    expect(byId('session-8')).toMatchObject({ itemCount: 0, materials: [], pointsChange: 0 })
  })

  it('history rows without a session are adjustments, rows of an unloaded session stay as single items', () => {
    expect(byId('adjustment-4')).toMatchObject({ kind: 'adjustment', pointsChange: 8 })
    expect(byId('points-5')).toMatchObject({ kind: 'item', material: 'aluminum', pointsChange: 18 })
  })

  it('redemptions appear once, as negative points', () => {
    expect(timeline.filter((e) => e.kind === 'redemption')).toHaveLength(1)
    expect(byId('redemption-9')).toMatchObject({ title: 'Mug', pointsChange: -100 })
  })

  it('handles missing arguments', () => {
    expect(buildActivityTimeline()).toEqual([])
  })
})

describe('filterTimeline', () => {
  const now = new Date('2026-11-10T12:00:00Z')

  it('tab recycling keeps sessions and single items, tab rewards keeps redemptions', () => {
    expect(ids(filterTimeline(timeline, { tab: 'recycling' }, now))).toEqual(['session-7', 'session-8', 'points-5'])
    expect(ids(filterTimeline(timeline, { tab: 'rewards' }, now))).toEqual(['redemption-9'])
  })

  it('material keeps sessions that contain it and items of it', () => {
    expect(ids(filterTimeline(timeline, { material: 'aluminum' }, now))).toEqual(['session-7', 'points-5'])
    expect(ids(filterTimeline(timeline, { material: 'glass' }, now))).toEqual(['session-7'])
  })

  it('date range keeps recent entries only and drops unreadable timestamps', () => {
    const withBad = [...timeline, { id: 'x', kind: 'adjustment', timestamp: 'nope', pointsChange: 1 }]
    expect(ids(filterTimeline(withBad, { range: '7d' }, now))).toEqual(['session-7', 'adjustment-4', 'redemption-9', 'session-8'])
    expect(ids(filterTimeline(withBad, {}, now))).toContain('x')
  })

  it('filters combine', () => {
    expect(ids(filterTimeline(timeline, { tab: 'recycling', material: 'aluminum', range: '7d' }, now))).toEqual(['session-7'])
  })
})

describe('activeFilterCount', () => {
  it('counts date and material, not the tab', () => {
    expect(activeFilterCount({ tab: 'rewards', range: 'all', material: 'all' })).toBe(0)
    expect(activeFilterCount({ tab: 'all', range: '7d', material: 'plastic' })).toBe(2)
  })
})

describe('groupByDay', () => {
  const now = new Date('2026-11-10T12:00:00') // local time
  it('groups into today, yesterday and dated days, newest first, unreadable last', () => {
    const entries = [
      { id: 'a', timestamp: new Date('2026-11-10T09:00:00').toISOString() },
      { id: 'b', timestamp: new Date('2026-11-10T08:00:00').toISOString() },
      { id: 'c', timestamp: new Date('2026-11-09T20:00:00').toISOString() },
      { id: 'd', timestamp: new Date('2026-11-01T10:00:00').toISOString() },
      { id: 'e', timestamp: null },
    ]
    const groups = groupByDay(entries, now)
    expect(groups.map((g) => [g.kind, g.entries.map((e) => e.id)])).toEqual([
      ['today', ['a', 'b']], ['yesterday', ['c']], ['date', ['d']], ['unknown', ['e']],
    ])
    expect(groups[2].date.getDate()).toBe(1)
  })
})

describe('isDemo', () => {
  it('carries is_demo from sessions and history rows, false otherwise', () => {
    const t = buildActivityTimeline(
      [{ id: 1, session_id: null, points_change: 2, created_at: '2026-11-10T01:00:00Z', is_demo: true, transaction: { material_selected: 'PET' } },
       { id: 2, session_id: null, points_change: 1, created_at: '2026-11-10T02:00:00Z', description: 'x' }],
      [{ id: 1, session_code: 'D1', started_at: '2026-11-10T03:00:00Z', is_demo: true },
       { id: 2, session_code: 'R1', started_at: '2026-11-10T04:00:00Z', is_demo: false },
       { id: 3, session_code: 'O1', started_at: '2026-11-10T05:00:00Z' }],
      [{ id: 9, reward_name: 'R', points_spent: 5, created_at: '2026-11-10T06:00:00Z' }],
    )
    const by = (id) => t.find((e) => e.id === id)
    expect(by('session-1').isDemo).toBe(true)
    expect(by('session-2').isDemo).toBe(false)
    expect(by('session-3').isDemo).toBe(false)
    expect(by('points-1').isDemo).toBe(true)
    expect(by('adjustment-2').isDemo).toBe(false)
    expect(by('redemption-9').isDemo).toBe(false)
  })
})
