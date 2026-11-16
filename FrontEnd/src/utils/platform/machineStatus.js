import { machineAvailability } from '../machineConnectivity.js'

const tones = {
  available: 'ok',
  almost_full: 'warn',
  full: 'bad',
  fault: 'bad',
  offline: 'muted',
  maintenance: 'muted',
}

// A row without machine_type comes from an OFF API or a legacy record: Ultra RVM.
export function isRvmMachine(machine) {
  return !machine?.machine_type || machine.machine_type === 'rvm'
}

export function statusPill(machine, now = new Date()) {
  if (Object.hasOwn(tones, machine.status)) return machine.status
  if (machine.status === 'inactive') return 'maintenance'

  const isRvm = isRvmMachine(machine)
  const availability = machineAvailability(machine, now)
  if (availability === 'fault' || (isRvm && availability === 'offline')) return availability

  const fill = isRvm && machine.bins
    ? Math.max(...Object.values(machine.bins).map(Number), 0)
    : Number(machine.fill_level)
  if (fill >= 90) return 'full'
  if (fill >= 80) return 'almost_full'
  return 'available'
}

export function pillTone(status) {
  return tones[status] ?? 'muted'
}
