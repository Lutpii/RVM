import { describe, it, expect, beforeAll, afterAll } from 'vitest'
import { toDatetimeLocalValue } from './toDatetimeLocalValue.js'

// Pinned to match the backend's config('app.timezone') (Asia/Kuala_Lumpur, UTC+8,
// no DST) — this function's whole purpose is converting a UTC API timestamp back
// to the admin's local wall-clock time, so the test must run in that same zone
// regardless of the machine/CI running it, or it would pass/fail based on the
// runner's own timezone instead of proving the actual round-trip.
let originalTz
beforeAll(() => { originalTz = process.env.TZ; process.env.TZ = 'Asia/Kuala_Lumpur' })
afterAll(() => { process.env.TZ = originalTz })

describe('toDatetimeLocalValue', () => {
  it('returns an empty string for a falsy input', () => {
    expect(toDatetimeLocalValue(null)).toBe('')
    expect(toDatetimeLocalValue('')).toBe('')
    expect(toDatetimeLocalValue(undefined)).toBe('')
  })

  it('converts a UTC ISO string to the admin-local (UTC+8) wall-clock value', () => {
    // 14:00 MYT == 06:00 UTC — this is the exact round trip that regressed before:
    // an admin typing 14:00, saving, and reopening the item must see 14:00 again.
    expect(toDatetimeLocalValue('2026-12-24T06:00:00.000000Z')).toBe('2026-12-24T14:00')
  })

  it('pads single-digit month, day, hour, and minute', () => {
    expect(toDatetimeLocalValue('2026-01-04T23:05:00.000000Z')).toBe('2026-01-05T07:05')
  })

  it('rolls over into the next day when the UTC+8 offset crosses midnight', () => {
    expect(toDatetimeLocalValue('2026-06-30T17:30:00.000000Z')).toBe('2026-07-01T01:30')
  })
})
