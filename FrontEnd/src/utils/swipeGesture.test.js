import { describe, expect, it } from 'vitest'
import { commitDistance, lockAxis, releaseVelocity, resolveSwipe, swipeProgress } from './swipeGesture.js'

describe('lockAxis', () => {
  it('stays undecided while the finger has barely moved', () => {
    expect(lockAxis(4, 3)).toBe(null)
  })

  it('locks horizontal when the move is mostly sideways', () => {
    expect(lockAxis(14, 5)).toBe('x')
    expect(lockAxis(-14, 5)).toBe('x')
  })

  it('locks vertical when the move is mostly up/down, so scrolling still works', () => {
    expect(lockAxis(5, 14)).toBe('y')
    expect(lockAxis(5, -14)).toBe('y')
  })

  it('treats an exact diagonal as vertical (scroll wins ties)', () => {
    expect(lockAxis(12, 12)).toBe('y')
  })
})

describe('commitDistance', () => {
  it('is 35% of the card width', () => {
    expect(commitDistance(400)).toBe(140)
  })

  it('never drops below 80px on narrow cards', () => {
    expect(commitDistance(200)).toBe(80)
  })
})

describe('resolveSwipe', () => {
  it('returns right past the threshold', () => {
    expect(resolveSwipe(140, 400)).toBe('right')
  })

  it('returns left past the threshold', () => {
    expect(resolveSwipe(-150, 400)).toBe('left')
  })

  it('returns null when released slowly before the threshold', () => {
    expect(resolveSwipe(139, 400)).toBe(null)
    expect(resolveSwipe(-60, 400)).toBe(null)
    expect(resolveSwipe(0, 400)).toBe(null)
    expect(resolveSwipe(100, 400, 0.1)).toBe(null)
  })

  it('accepts a short fast flick (>= 0.3 px/ms over >= 40px)', () => {
    expect(resolveSwipe(50, 400, 0.5)).toBe('right')
    expect(resolveSwipe(-50, 400, -0.5)).toBe('left')
    expect(resolveSwipe(40, 400, 0.3)).toBe('right')
  })

  it('ignores a flick that is too short, even if fast', () => {
    expect(resolveSwipe(39, 400, 2)).toBe(null)
  })

  it('ignores a flick moving back against the drag direction', () => {
    expect(resolveSwipe(60, 400, -0.8)).toBe(null)
  })
})

describe('releaseVelocity', () => {
  it('measures px/ms over the samples in the last 100ms', () => {
    const samples = [
      { x: 0, t: 0 },     // too old, ignored
      { x: 10, t: 150 },
      { x: 40, t: 200 },
      { x: 70, t: 250 },
    ]
    expect(releaseVelocity(samples)).toBeCloseTo(0.6) // (70 - 10) / (250 - 150)
  })

  it('is signed (negative when moving left)', () => {
    expect(releaseVelocity([{ x: 100, t: 0 }, { x: 40, t: 60 }])).toBeCloseTo(-1)
  })

  it('is 0 when the finger paused before lifting', () => {
    // Last move at t=100, lifted in place at t=400: nothing moved in the window.
    expect(releaseVelocity([{ x: 0, t: 50 }, { x: 60, t: 100 }, { x: 60, t: 400 }])).toBe(0)
  })

  it('is 0 without enough samples', () => {
    expect(releaseVelocity([])).toBe(0)
    expect(releaseVelocity([{ x: 5, t: 10 }])).toBe(0)
  })
})

describe('swipeProgress', () => {
  it('grows from 0 to 1 as the drag approaches the threshold', () => {
    expect(swipeProgress(0, 400)).toBe(0)
    expect(swipeProgress(70, 400)).toBe(0.5)
    expect(swipeProgress(-70, 400)).toBe(0.5)
  })

  it('caps at 1 past the threshold', () => {
    expect(swipeProgress(500, 400)).toBe(1)
  })
})
