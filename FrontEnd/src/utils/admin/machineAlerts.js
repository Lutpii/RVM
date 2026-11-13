// FrontEnd/src/utils/admin/machineAlerts.js
// Online/offline and fault alerts derived from kiosk heartbeats (A02/A05).
// A machine that has never sent a heartbeat is 'never', not 'offline': on a
// deploy with no kiosk connected (the cloud demo) that must not look like an
// outage. Bin-level alerts stay in the existing Bin Alerts card.

import { ONLINE_WINDOW_MS, machineConnectivity } from '@/utils/machineConnectivity'

export { ONLINE_WINDOW_MS, machineConnectivity }

export function buildMachineAlerts(machines = [], now = new Date()) {
  const faults = []
  const offline = []
  for (const machine of machines) {
    if (machine.fault_active) {
      faults.push({ machineId: machine.id, name: machine.name, type: 'fault', since: machine.fault_since ?? null, detail: machine.fault_reason ?? null, isDemo: Boolean(machine.is_demo) })
    }
    if (machineConnectivity(machine, now) === 'offline') {
      offline.push({ machineId: machine.id, name: machine.name, type: 'offline', since: machine.last_seen_at, detail: null, isDemo: Boolean(machine.is_demo) })
    }
  }
  return [...faults, ...offline]
}

export function connectivitySummary(machines = [], now = new Date()) {
  let online = 0
  let anyReported = false
  for (const machine of machines) {
    const c = machineConnectivity(machine, now)
    if (c !== 'never') anyReported = true
    if (c === 'online') online++
  }
  return { online, total: machines.length, anyReported }
}
