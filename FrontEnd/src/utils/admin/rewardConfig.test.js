import { describe, it, expect } from 'vitest'
import { buildRewardUpdatePayload } from './rewardConfig.js'

describe('buildRewardUpdatePayload', () => {
  const saved = { plastic: 5, aluminum: 8, glass: 5, paper: 3 }

  it('overrides only the target material, keeping saved values for the rest', () => {
    const edits = { plastic: 12, aluminum: 8, glass: NaN, paper: 3 }

    const payload = buildRewardUpdatePayload(saved, edits, 'plastic')

    expect(payload).toEqual({ plastic: 12, aluminum: 8, glass: 5, paper: 3 })
  })

  it('throws when the target material\'s edited value is not a valid integer', () => {
    const edits = { plastic: 5, aluminum: 8, glass: NaN, paper: 3 }

    expect(() => buildRewardUpdatePayload(saved, edits, 'glass')).toThrow(/glass/i)
  })

  it('throws when the target material\'s edited value is negative', () => {
    const edits = { ...saved, paper: -1 }

    expect(() => buildRewardUpdatePayload(saved, edits, 'paper')).toThrow(/paper/i)
  })
})
