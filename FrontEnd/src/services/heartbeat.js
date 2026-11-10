// FrontEnd/src/services/heartbeat.js
// Kiosk heartbeat (A02/A05): tells the backend this machine is alive and
// whether the 2-bin compactor is locked by a hardware fault. Only started in a
// kiosk build (VITE_MACHINE_KEY set, see main.js). Never throws.
import { isMachineFault } from '@/utils/compactorFlow'

export const HEARTBEAT_INTERVAL_MS = 60000

export function startHeartbeat({ post, getState, intervalMs = HEARTBEAT_INTERVAL_MS }) {
  let stopped = false

  async function tick() {
    if (stopped) return
    let state = null
    try {
      state = await getState()
    } catch {
      state = null
    }
    const fault = isMachineFault(state)
    try {
      await post('/hardware/heartbeat', { fault, fault_reason: fault ? String(state.fault) : null })
    } catch {
      // offline or backend restarting: the next tick tries again
    }
  }

  tick()
  const timer = setInterval(tick, intervalMs)
  return function stop() {
    stopped = true
    clearInterval(timer)
  }
}
