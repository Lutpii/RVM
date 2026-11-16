import { machineAvailability } from '../machineConnectivity.js'

const tones = {
  available: 'ok',
  almost_full: 'warn',
  full: 'bad',
  fault: 'bad',
  offline: 'muted',
  maintenance: 'muted',
}

export function statusPill(machine, now = new Date()) {
  if (Object.hasOwn(tones, machine.status)) return machine.status
  if (machine.status === 'inactive') return 'maintenance'

  const isRvm = !machine.machine_type || machine.machine_type === 'rvm'
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
