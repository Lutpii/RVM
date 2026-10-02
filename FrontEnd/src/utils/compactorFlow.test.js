import { describe, it, expect, vi } from 'vitest'
import { ACCEPTED_2BIN, stepAfterClassify, readDepositResult, switchStage, waitForJob, singleFlight, verifiedMaterial, oneDecision, sessionLimitReached, SESSION_LIMIT_PER_MATERIAL } from './compactorFlow.js'

describe('stepAfterClassify', () => {
  it('deposits tin and plastic', () => {
    expect(ACCEPTED_2BIN).toEqual(['aluminum', 'plastic'])
    expect(stepAfterClassify('aluminum')).toBe('deposit')
    expect(stepAfterClassify('plastic')).toBe('deposit')
  })
  it('rejects materials without a bin and flags unknown items', () => {
    for (const m of ['paper', 'glass', 'reject']) expect(stepAfterClassify(m)).toBe('item_rejected')
    expect(stepAfterClassify('unknown')).toBe('item_unknown')
  })
})

describe('readDepositResult', () => {
  it('reads each response shape', () => {
    expect(readDepositResult({ accepted: true, job_id: 'j', will_flush: true, eta_seconds: 46 }))
      .toEqual({ kind: 'accepted', jobId: 'j', willFlush: true, etaSeconds: 46 })
    expect(readDepositResult({ accepted: false, reason: 'mismatch', chamber_material: 'aluminum', chamber_count: 3 }))
      .toEqual({ kind: 'mismatch', chamberMaterial: 'aluminum', chamberCount: 3 })
    expect(readDepositResult({ accepted: false, reason: 'not_accepted' })).toEqual({ kind: 'rejected' })
    expect(readDepositResult(null)).toEqual({ kind: 'error' })
    expect(readDepositResult({ success: false, error: 'machine_unavailable' })).toEqual({ kind: 'error' })
  })
})

describe('switchStage', () => {
  it('follows the machine phase', () => {
    expect(switchStage({ job: { status: 'queued' }, phase: 'compacting' })).toBe(-1)
    expect(switchStage({ job: { status: 'running' }, phase: 'compacting' })).toBe(0)
    expect(switchStage({ job: { status: 'running' }, phase: 'tilting' })).toBe(1)
    expect(switchStage({ job: { status: 'running' }, phase: 'gate' })).toBe(2)
    expect(switchStage({ job: { status: 'dropped' }, phase: 'idle' })).toBe(3)
  })
})

function clock() {
  let t = 0
  return { now: () => t, sleep: async (ms) => { t += ms } }
}

describe('waitForJob', () => {
  it('resolves done once the job has dropped, reporting every state', async () => {
    const states = [
      { profile: '2bin', job: { status: 'queued' } },
      { profile: '2bin', job: { status: 'running' } },
      { profile: '2bin', job: { status: 'dropped' } },
    ]
    const getState = vi.fn(async () => states.shift())
    const onState = vi.fn()
    expect(await waitForJob('j', { getState, onState, ...clock() })).toBe('done')
    expect(getState).toHaveBeenCalledWith('j')
    expect(onState).toHaveBeenCalledTimes(3)
  })
  it('fails when the service lost the job (restart) or the job failed', async () => {
    for (const status of ['unknown', 'failed']) {
      const getState = async () => ({ profile: '2bin', job: { status } })
      expect(await waitForJob('j', { getState, ...clock() })).toBe('failed')
    }
  })
  it('fails after 5 unreachable polls in a row, ignoring legacy answers', async () => {
    const answers = [null, { profile: 'legacy' }, null, null, null]
    const getState = vi.fn(async () => answers.shift())
    expect(await waitForJob('j', { getState, ...clock() })).toBe('failed')
    expect(getState).toHaveBeenCalledTimes(5)
  })
  it('times out instead of polling forever', async () => {
    const getState = async () => ({ profile: '2bin', job: { status: 'running' } })
    expect(await waitForJob('j', { getState, timeoutMs: 3000, intervalMs: 1000, ...clock() })).toBe('timeout')
  })
})

describe('singleFlight', () => {
  it('runs once for overlapping calls (double tap + auto-continue)', async () => {
    let release
    const fn = vi.fn(() => new Promise((r) => { release = r }))
    const once = singleFlight(fn)
    const a = once()
    const b = once()
    expect(fn).toHaveBeenCalledTimes(1)
    release('ok')
    expect(await a).toBe('ok')
    expect(await b).toBe('ok')
    once()
    expect(fn).toHaveBeenCalledTimes(2)
  })
})

describe('verifiedMaterial', () => {
  it('passes a real classification through', () => {
    expect(verifiedMaterial('plastic', { imagePath: 'captures/a.jpg', mock: false, failed: false })).toBe('plastic')
  })
  it('treats a failed, mocked or image-less classification as unknown', () => {
    expect(verifiedMaterial('plastic', { imagePath: 'captures/a.jpg', failed: true })).toBe('unknown')
    expect(verifiedMaterial('aluminum', { imagePath: 'captures/a.jpg', mock: true })).toBe('unknown')
    expect(verifiedMaterial('aluminum', { imagePath: null })).toBe('unknown')
  })
})

describe('oneDecision', () => {
  it('lets only the first of continue / take back / auto-continue through', () => {
    const decision = oneDecision()
    expect(decision.decided()).toBe(false)
    expect(decision.claim()).toBe(true)   // e.g. Take back
    expect(decision.claim()).toBe(false)  // Continue tapped right after
    expect(decision.claim()).toBe(false)  // timer reaching 0
    expect(decision.decided()).toBe(true)
    decision.reset()                      // next material-switch screen
    expect(decision.claim()).toBe(true)
  })
})

describe('sessionLimitReached', () => {
  const txn = (material, is_valid = true) => ({ material, is_valid })
  it('allows up to 3 valid items of each material per session', () => {
    expect(SESSION_LIMIT_PER_MATERIAL).toBe(3)
    const two = [txn('aluminum'), txn('aluminum'), txn('plastic')]
    expect(sessionLimitReached(two, 'aluminum')).toBe(false)
    const three = [...two, txn('aluminum')]
    expect(sessionLimitReached(three, 'aluminum')).toBe(true)
    expect(sessionLimitReached(three, 'plastic')).toBe(false)
  })
  it('does not count rejected items', () => {
    const list = [txn('plastic'), txn('plastic'), txn('plastic', false), txn('unknown', false)]
    expect(sessionLimitReached(list, 'plastic')).toBe(false)
    expect(sessionLimitReached([], 'plastic')).toBe(false)
    expect(sessionLimitReached(undefined, 'plastic')).toBe(false)
  })
})
