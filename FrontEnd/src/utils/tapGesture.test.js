import { describe, expect, it } from 'vitest'
import { isTap, TAP_MAX_MOVE_PX, TAP_MAX_MS } from './tapGesture.js'

const down = { x: 100, y: 200, t: 1000 }

describe('isTap', () => {
  it('accepts a short press that stays in place', () => {
    expect(isTap(down, { x: 103, y: 198, t: 1120 })).toBe(true)
  })

  it('rejects a finger that moved (a scroll inside the modal ending on a button)', () => {
    expect(isTap(down, { x: 100, y: 200 + TAP_MAX_MOVE_PX + 1, t: 1100 })).toBe(false)
    expect(isTap(down, { x: 100 - TAP_MAX_MOVE_PX - 1, y: 200, t: 1100 })).toBe(false)
  })

  it('rejects a long press', () => {
    expect(isTap(down, { x: 100, y: 200, t: 1000 + TAP_MAX_MS + 1 })).toBe(false)
  })

  it('rejects a release without a matching press', () => {
    expect(isTap(null, { x: 100, y: 200, t: 1100 })).toBe(false)
  })
})
