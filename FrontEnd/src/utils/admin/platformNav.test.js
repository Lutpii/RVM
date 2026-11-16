import { describe, expect, it } from 'vitest'
import { platformNavGroups } from './platformNav.js'

describe('platformNavGroups', () => {
  it('groups platform, machine and management tabs and keeps the legacy RVM tab id', () => {
    const groups = platformNavGroups(key => key.startsWith('platform.machine.') ? 'Ultra RVM' : key)
    expect(groups.map(group => group.group)).toEqual(['platform', 'machines', 'manage'])
    expect(groups.map(group => group.items.map(item => item.id))).toEqual([
      ['platform-overview'],
      ['dashboard', 'compactor', 'composter'],
      ['machines', 'rewards', 'users', 'transactions', 'sessions', 'audit', 'detection'],
    ])
    expect(groups[1].items[0]).toMatchObject({ id: 'dashboard', label: 'Ultra RVM' })
  })
})
