import { describe, it, expect } from 'vitest'
import { isIdleStep } from './kioskIdleSteps'

describe('isIdleStep', () => {
  it('real kiosk waiting screens are idle steps', () => {
    for (const s of ['insert', 'complete', 'item_unknown', 'item_rejected', 'session_limit']) {
      expect(isIdleStep('real', s)).toBe(true)
    }
  })

  it('real kiosk in-flight screens are not idle steps', () => {
    for (const s of ['weigh', 'camera', 'classify', 'compacting', 'dropping', 'material_switch', 'bin_check', 'validate_ok', 'machine_fault', 'machine_error']) {
      expect(isIdleStep('real', s)).toBe(false)
    }
  })

  it('demo waiting screens are idle steps', () => {
    for (const s of ['choose', 'complete', 'item_rejected', 'save_failed']) {
      expect(isIdleStep('demo', s)).toBe(true)
    }
  })

  it('demo capture, AI and bin steps are not idle steps', () => {
    for (const s of ['bin_check', 'camera', 'classify', 'validate_ok', 'weigh']) {
      expect(isIdleStep('demo', s)).toBe(false)
    }
  })
})
