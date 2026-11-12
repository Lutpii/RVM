// FrontEnd/src/utils/kioskDemo.js
// Demo kiosk (exhibition, no physical machine): the visitor picks the item,
// the capture and the AI decision are simulated and labelled as such.

// K5 tiles, in this order. Glass shows a rejection (the machine does not take it).
export const DEMO_ITEMS = [
  { id: 'plastic',  accepted: true },
  { id: 'aluminum', accepted: true },
  { id: 'paper',    accepted: true },
  { id: 'glass',    accepted: false },
]

// Simulated AI confidence, 0.880–0.980 with 3 decimals.
export function simulatedConfidence(rand = Math.random) {
  const steps = Math.min(100, Math.floor(rand() * 101))
  return (880 + steps) / 1000
}

// A refresh stops the simulated capture/AI chain: go back to the item choice.
const RESUME_TO_CHOOSE = ['camera', 'classify', 'result', 'weigh']

export function resumeStep(step) {
  return RESUME_TO_CHOOSE.includes(step) ? 'choose' : step
}

// The demo kiosk only saves into a session that belongs to its own machine.
// Anything else (unknown ids, a session resumed on a real machine) is refused.
export function sessionMatchesMachine(session, machineId) {
  const sessionMachineId = session?.machine?.id
  if (sessionMachineId == null || machineId == null) return false
  return String(sessionMachineId) === String(machineId)
}
