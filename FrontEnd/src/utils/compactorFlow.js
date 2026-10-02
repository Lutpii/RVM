// Pure decision helpers for the 2-bin compactor kiosk flow
// (docs/superpowers/specs/2026-10-02-dsme-compactor-flow-design.md).

export const ACCEPTED_2BIN = ['aluminum', 'plastic']

// What the kiosk does right after classification on the 2-bin machine.
export function stepAfterClassify(material) {
  if (material === 'unknown') return 'item_unknown'
  if (!ACCEPTED_2BIN.includes(material)) return 'item_rejected'
  return 'deposit'
}

// Normalizes a /hardware/deposit response; null means the service was unreachable.
export function readDepositResult(data) {
  if (data?.accepted) {
    return { kind: 'accepted', jobId: data.job_id, willFlush: !!data.will_flush, etaSeconds: data.eta_seconds ?? 0 }
  }
  if (data?.reason === 'mismatch') {
    return { kind: 'mismatch', chamberMaterial: data.chamber_material, chamberCount: data.chamber_count ?? 0 }
  }
  if (data?.reason === 'not_accepted') return { kind: 'rejected' }
  return { kind: 'error' }
}

// Active stage of "compact old batch -> move to its bin -> drop new item":
// 0..2 while running, 3 once dropped, -1 while still queued behind other jobs.
export function switchStage(state) {
  const status = state?.job?.status
  if (status === 'dropped') return 3
  if (status !== 'running') return -1
  return { compacting: 0, tilting: 1, gate: 2 }[state.phase] ?? -1
}

// Polls getState(jobId) until the job is finished. 'failed' covers a failed
// job, a job the service no longer knows (it restarted), and 5 unreachable
// polls in a row; 'timeout' stops an endless spinner.
export async function waitForJob(jobId, {
  getState,
  onState = () => {},
  intervalMs = 1000,
  timeoutMs = 180000,
  now = Date.now,
  sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms)),
}) {
  const start = now()
  let misses = 0
  while (now() - start < timeoutMs) {
    const state = await getState(jobId)
    if (state?.profile === '2bin') {
      misses = 0
      onState(state)
      const status = state.job?.status
      if (status === 'dropped' || status === 'flushed') return 'done'
      if (status === 'failed' || status === 'unknown') return 'failed'
    } else if (++misses >= 5) {
      return 'failed'
    }
    await sleep(intervalMs)
  }
  return 'timeout'
}

// Wraps an async fn so overlapping calls share one run.
export function singleFlight(fn) {
  let inFlight = null
  return (...args) => {
    if (!inFlight) {
      inFlight = Promise.resolve(fn(...args)).finally(() => { inFlight = null })
    }
    return inFlight
  }
}

// On the 2-bin machine an unverified classification must never open the
// flap: Laravel's mockClassify() (no image, AI error) and the client-side
// fallbacks pick a material at random, which could drop glass into the
// compactor and award points for it.
export function verifiedMaterial(material, { imagePath, mock = false, failed = false }) {
  if (failed || mock || !imagePath) return 'unknown'
  return material
}

// The material-switch screen has three ways out (Continue, Take back, the
// auto-continue timer). Only the first may act: a Take back followed by a
// Continue would otherwise open the flap after the user was told to take
// the item back.
export function oneDecision() {
  let made = false
  return {
    claim() {
      if (made) return false
      made = true
      return true
    },
    decided: () => made,
    reset() { made = false },
  }
}

// The compactor holds 3 items, so one session may deposit at most 3 of each
// material (rejected items don't count).
export const SESSION_LIMIT_PER_MATERIAL = 3

export function sessionLimitReached(transactions, material) {
  const used = (transactions || []).filter((t) => t.is_valid && t.material === material).length
  return used >= SESSION_LIMIT_PER_MATERIAL
}
