import { describe, expect, it } from 'vitest'
import { buildAdminQuery, parseAdminQuery } from './adminTabState.js'

const TABS = ['dashboard', 'machines', 'detection']

describe('parseAdminQuery', () => {
  it('defaults to the dashboard with no query', () => {
    expect(parseAdminQuery({}, TABS)).toEqual({ tab: 'dashboard', detectionView: 'pending' })
  })

  it('restores a known tab', () => {
    expect(parseAdminQuery({ tab: 'machines' }, TABS)).toEqual({ tab: 'machines', detectionView: 'pending' })
  })

  it('restores the Detection Review sub-view', () => {
    expect(parseAdminQuery({ tab: 'detection', view: 'history' }, TABS))
      .toEqual({ tab: 'detection', detectionView: 'history' })
  })

  it('falls back to the dashboard for an unknown tab', () => {
    expect(parseAdminQuery({ tab: 'nope' }, TABS).tab).toBe('dashboard')
  })

  it('falls back to pending for an unknown sub-view', () => {
    expect(parseAdminQuery({ tab: 'detection', view: 'nope' }, TABS).detectionView).toBe('pending')
  })

  it('ignores repeated query params (arrays)', () => {
    expect(parseAdminQuery({ tab: ['machines', 'detection'] }, TABS).tab).toBe('dashboard')
  })

  it('tolerates a missing query object', () => {
    expect(parseAdminQuery(undefined, TABS).tab).toBe('dashboard')
  })
})

describe('buildAdminQuery', () => {
  it('keeps the URL clean on the default tab', () => {
    expect(buildAdminQuery({ tab: 'dashboard', detectionView: 'pending' })).toEqual({})
  })

  it('writes the tab', () => {
    expect(buildAdminQuery({ tab: 'machines', detectionView: 'pending' })).toEqual({ tab: 'machines' })
  })

  it('writes the Detection Review sub-view only when it is not the default', () => {
    expect(buildAdminQuery({ tab: 'detection', detectionView: 'pending' })).toEqual({ tab: 'detection' })
    expect(buildAdminQuery({ tab: 'detection', detectionView: 'test' })).toEqual({ tab: 'detection', view: 'test' })
  })

  it('drops the sub-view on other tabs', () => {
    expect(buildAdminQuery({ tab: 'machines', detectionView: 'history' })).toEqual({ tab: 'machines' })
  })

  it('round-trips through parseAdminQuery', () => {
    const state = { tab: 'detection', detectionView: 'history' }
    expect(parseAdminQuery(buildAdminQuery(state), TABS)).toEqual(state)
  })
})
