import { describe, it, expect } from 'vitest'
import { scanOutcome } from './scanResult.js'

describe('scanOutcome', () => {
  it('flags a session on another machine', () => {
    expect(scanOutcome({ code: 'active_session_elsewhere', machine_name: 'RVM A', session_code: 'X' }))
      .toEqual({ kind: 'elsewhere', machineName: 'RVM A', sessionCode: 'X' })
  })
  it('treats any other payload as an error', () => {
    expect(scanOutcome({ message: 'Bad QR', session_code: 'S1' })).toEqual({ kind: 'error', message: 'Bad QR' })
    expect(scanOutcome(undefined)).toEqual({ kind: 'error', message: '' })
  })
})
