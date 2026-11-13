// Keys added for Dr Rohana's usability review (Start Recycling hero, actionable
// Activity empty state, "connection code" wording, camera error recovery,
// demo data badge). Both languages must define every one of them, and the
// recycler-facing text must no longer say "token".
import { describe, it, expect } from 'vitest'
import { messages } from './index.js'

const KEYS = [
  'dashboard.readyTitle', 'dashboard.readyHint', 'dashboard.startRecycling',
  'activity.empty', 'activity.emptyHint',
  'scan.subtitle', 'scan.orManual', 'scan.manualHint', 'scan.tokenPlaceholder', 'scan.connectBtn',
  'scan.permissionDenied', 'scan.tryAgain', 'scan.useCodeInstead',
  'kioskQr.tokenLabel',
  'demo.badge', 'demo.kioskBannerTitle', 'demo.kioskBannerText',
  'activity.demoChip', 'activity.demoNote',
  'phoneSession.happy', 'phoneSession.connectedTo', 'phoneSession.demoMachine', 'phoneSession.connecting',
  'phoneSession.inProgress', 'phoneSession.summary', 'phoneSession.view', 'phoneSession.simNote', 'phoneSession.openDetails', 'phoneSession.pillLine',
  'phoneSession.co2Saved', 'phoneSession.endSession', 'phoneSession.endConfirmTitle', 'phoneSession.endConfirmText',
  'phoneSession.endConfirmCancel', 'phoneSession.ended', 'phoneSession.endFailed',
  'demoKiosk.notDemoMachine', 'demoKiosk.chooseTitle', 'demoKiosk.chooseHint',
  'demoKiosk.items.plastic', 'demoKiosk.items.aluminum', 'demoKiosk.items.paper', 'demoKiosk.items.glass',
  'demoKiosk.simCapture', 'demoKiosk.simAi', 'demoKiosk.simulated', 'demoKiosk.glassRejected',
  'demoKiosk.saveFailedTitle', 'demoKiosk.saveFailedText', 'demoKiosk.tryAgain', 'demoKiosk.tryAnother', 'summary.acceptedRejected',
  'admin.dataScope.label', 'admin.dataScope.all', 'admin.dataScope.real', 'admin.dataScope.demo',
  'admin.demoChip', 'admin.machine.isDemo', 'admin.machine.isDemoHint',
  'summary.backToStart',
]

const get = (obj, path) => path.split('.').reduce((o, k) => o?.[k], obj)

describe('usability + demo kiosk keys', () => {
  for (const locale of Object.keys(messages)) {
    it(`are all defined in "${locale}"`, () => {
      const missing = KEYS.filter((k) => typeof get(messages[locale], k) !== 'string' || !get(messages[locale], k))
      expect(missing).toEqual([])
    })

    it(`say "connection code", not "token", in "${locale}"`, () => {
      const scanTexts = ['scan.tokenPlaceholder', 'scan.manualHint', 'scan.httpsRequired', 'scan.cameraUnavailable', 'kioskQr.tokenLabel']
        .map((k) => get(messages[locale], k))
      for (const text of scanTexts) expect(text.toLowerCase()).not.toContain('token')
    })
  }
})
