// FrontEnd/src/utils/machineConnectivity.js
// Heartbeat-based machine state shared by the admin view (A02/A05) and the
// recycler dashboard (U06). A machine that never sent a heartbeat is 'never'
// / 'unknown', never 'offline': with no kiosk connected (the cloud demo) that
// must not look like an outage.

export const ONLINE_WINDOW_MS = 3 * 60 * 1000

export function machineConnectivity(machine, now = new Date()) {
  const seen = machine?.last_seen_at ? new Date(machine.last_seen_at).getTime() : NaN
  if (!Number.isFinite(seen)) return 'never'
  return now.getTime() - seen <= ONLINE_WINDOW_MS ? 'online' : 'offline'
}

// What a recycler sees: 'available' | 'offline' | 'fault' | 'unknown'.
export function machineAvailability(machine, now = new Date()) {
  if (machine?.fault_active) return 'fault'
  const c = machineConnectivity(machine, now)
  if (c === 'online') return 'available'
  if (c === 'offline') return 'offline'
  return 'unknown'
}
