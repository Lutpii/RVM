// Kiosk screens where the machine is waiting for the user. The idle timer and
// the session watch run only on these, never while an item is in flight.
// Staff-attention states (machine_fault, machine_error) are deliberately excluded.
export const REAL_IDLE_STEPS = ['insert', 'complete', 'item_unknown', 'item_rejected', 'session_limit']
export const DEMO_IDLE_STEPS = ['choose', 'complete', 'item_rejected', 'save_failed']

export function isIdleStep(kind, step) {
  const steps = kind === 'demo' ? DEMO_IDLE_STEPS : REAL_IDLE_STEPS
  return steps.includes(step)
}
