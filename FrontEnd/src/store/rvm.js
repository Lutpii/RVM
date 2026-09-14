import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import api from '@/services/api'

const KIOSK_STATE_STORAGE_KEY = 'rvm_kiosk_state'

function readKioskState() {
  if (typeof sessionStorage === 'undefined') return null
  try {
    return JSON.parse(sessionStorage.getItem(KIOSK_STATE_STORAGE_KEY) || 'null')
  } catch {
    return null
  }
}

export const useRvmStore = defineStore('rvm', () => {
  const session            = ref(null)
  const machine            = ref(null)
  const currentStep        = ref('landing')
  const selectedMaterial   = ref(null)
  const currentTransaction = ref(null)
  const lastError          = ref(null)
  const isGuest            = ref(false)
  const guestMachineCode   = ref(null)
  // True only once startGuestSession's /hardware/session/start call actually
  // succeeds - both it and the client-only fallback session_code share the
  // same 'GUEST-' prefix, so this flag (not the prefix) is what processStep
  // uses to know whether there's a real backend session to write items to.
  const guestSessionIsReal = ref(false)

  // Local summary tracking — used when no real API session exists
  const localSummary = ref({
    total_items:   0,
    points_earned: 0,
    start_points:  0,
    transactions:  [],
  })

  const steps = [
    'landing', 'qr', 'selection', 'bin_check', 'lid',
    'insert', 'conveyor', 'camera', 'classify', 'validate',
    'weigh', 'complete', 'summary'
  ]

  function persistKioskSession() {
    if (!guestMachineCode.value || !session.value) return
    try {
      sessionStorage.setItem(KIOSK_STATE_STORAGE_KEY, JSON.stringify({
        machineCode: guestMachineCode.value,
        session: session.value,
        machine: machine.value,
        currentStep: currentStep.value,
        selectedMaterial: selectedMaterial.value,
        currentTransaction: currentTransaction.value,
        localSummary: localSummary.value,
        isGuest: isGuest.value,
        guestSessionIsReal: guestSessionIsReal.value,
      }))
    } catch { /* sessionStorage may be unavailable */ }
  }

  function restoreKioskSession(machineCode) {
    const saved = readKioskState()
    if (!saved?.session || saved.machineCode !== machineCode) return false

    session.value            = saved.session
    machine.value            = saved.machine || saved.session.machine || null
    currentStep.value        = saved.currentStep || 'bin_check'
    selectedMaterial.value   = saved.selectedMaterial || null
    currentTransaction.value = saved.currentTransaction || null
    localSummary.value       = saved.localSummary || { total_items: 0, points_earned: 0, start_points: 0, transactions: [] }
    isGuest.value            = !!saved.isGuest
    guestMachineCode.value   = saved.machineCode
    guestSessionIsReal.value = !!saved.guestSessionIsReal
    return true
  }

  function clearKioskSession() {
    try { sessionStorage.removeItem(KIOSK_STATE_STORAGE_KEY) } catch { /* unavailable */ }
  }

  watch(
    [session, machine, currentStep, selectedMaterial, currentTransaction, localSummary, isGuest, guestMachineCode, guestSessionIsReal],
    () => persistKioskSession(),
    { deep: true },
  )

  function setStep(step) { currentStep.value = step }
  function setMachine(d) { machine.value = d }
  function setSession(d) { session.value = d }
  function setSelectedMaterial(m) { selectedMaterial.value = m }

  async function startGuestSession(machineCode, machineData = null) {
    isGuest.value          = true
    guestMachineCode.value = machineCode
    // Placeholder until (if) the real backend session below resolves -
    // guests still get a working local session immediately even if that
    // call is slow, fails, or the machine id isn't known yet.
    session.value = {
      session_code:  'GUEST-' + Date.now().toString(36).toUpperCase(),
      status:        'active',
      user_name:     'Guest',
      current_points: 0,
      start_points:  0,
      end_points:    0,
      points_earned: 0,
      total_items:   0,
      machine: machineData || {
        id: null, name: machineCode, location: '',
        aluminum_level: 0, plastic_level: 0, glass_level: 0, paper_level: 0,
      },
      started_at: new Date().toISOString(),
    }
    localSummary.value = { total_items: 0, points_earned: 0, start_points: 0, transactions: [] }
    setStep('bin_check')

    // Best-effort: swap in a real recycling_sessions row (tied to the shared
    // guest placeholder account server-side) so this guest's items show up
    // in the admin dashboard. Requires a real machine id, which a plain
    // "Continue as Guest" tap on the landing screen (no QR/machine lookup
    // yet) may not have - the client-generated code above is a fine
    // fallback either way, it just won't be reflected in admin stats.
    if (machineData?.id) {
      try {
        const res = await api.post('/hardware/session/start', { machine_id: machineData.id })
        if (res.data.success && session.value) {
          session.value.session_code = res.data.session_code
          guestSessionIsReal.value   = true
        }
      } catch { /* keep the local-only fallback session_code, guestSessionIsReal stays false */ }
    }
  }

  // Points range must match BackEnd/app/Http/Controllers/TransactionController.php's
  // POINTS_MIN/POINTS_MAX — guests never hit the real /transactions/weigh endpoint,
  // so this is the only place their points get decided.
  const GUEST_POINTS_MIN = 15
  const GUEST_POINTS_MAX = 20

  function _guestMockStep(stepName, payload) {
    if (stepName === 'classify') {
      const selected = payload.material_selected || selectedMaterial.value
      return { success: true, is_valid: true, ai_detected: selected, confidence: 0.95, all_predictions: [], step: 'validated' }
    }
    if (stepName === 'weigh') {
      const material = payload.material_selected || selectedMaterial.value
      const weight   = Math.floor(Math.random() * 400) + 50
      const points   = Math.floor(Math.random() * (GUEST_POINTS_MAX - GUEST_POINTS_MIN + 1)) + GUEST_POINTS_MIN
      return { success: true, weight_grams: weight, points_earned: points, material, step: 'weighed' }
    }
    if (stepName === 'complete') {
      return { success: true, points_earned: payload.points_earned || 0, total_points: 0, step: 'complete' }
    }
    return { success: true }
  }

  function recordLocalTransaction({ material, weight, points, isValid, deducted = 0, carbon = 0 }) {
    localSummary.value.transactions.push({ material, weight, points_earned: points, points_deducted: deducted, is_valid: isValid, carbon_saved: carbon })
    if (isValid) {
      localSummary.value.total_items++
      localSummary.value.points_earned += points
    } else {
      localSummary.value.points_earned -= deducted
    }
  }

  async function checkBin(material) {
    if (isGuest.value) {
      return { success: true, bin_full: false, bin_level: 50, message: 'Bin has space.' }
    }
    const res = await api.post('/transactions/check-bin', {
      session_code:      session.value?.session_code,
      material_selected: material,
    })
    return res.data
  }

  async function startSession(machineId, qrToken) {
    const res = await api.post('/sessions/start', { machine_id: machineId, qr_token: qrToken })
    if (res.data.success) {
      session.value = res.data.session
      setStep('bin_check')
    }
    return res.data
  }

  async function endSession() {
    const txns = localSummary.value.transactions || []
    localSummary.value.total_items   = txns.filter(t => t.is_valid).length
    localSummary.value.points_earned = txns.reduce((sum, t) =>
      sum + (t.is_valid ? (t.points_earned || 0) : -(t.points_deducted || 0)), 0)

    if (isGuest.value) {
      if (guestSessionIsReal.value && session.value?.session_code) {
        api.post('/hardware/session/end', { session_code: session.value.session_code }).catch(() => {})
      }
      session.value = {
        ...session.value,
        status:       'completed',
        points_earned: localSummary.value.points_earned,
        total_items:   localSummary.value.total_items,
      }
      setStep('summary')
      return { success: true, session: session.value }
    }

    if (!session.value?.session_code) return
    const res = await api.post(`/sessions/${session.value.session_code}/end`)
    if (res.data.success) {
      session.value = res.data.session
      setStep('summary')
    }
    return res.data
  }

  async function getSummary() {
    if (!session.value?.session_code) return
    const res = await api.get(`/sessions/${session.value.session_code}/summary`)
    return res.data
  }

  async function processStep(stepName, payload = {}) {
    if (isGuest.value) {
      // Classify still hits the real AI service — guests get real detection,
      // not a random guess.
      if (stepName === 'classify') {
        try {
          const res = await api.post('/hardware/classify', { image_path: payload.image_path })
          return res.data
        } catch {
          return _guestMockStep(stepName, payload)
        }
      }
      // Weigh is where a guest's item actually gets written to the DB (as a
      // real transactions row under the shared guest placeholder account —
      // see TransactionController::guestSessionComplete) so it counts in the
      // admin dashboard. Falls back to a client-only mock if there's no real
      // backend session (see startGuestSession) or the call fails, same as
      // before this existed.
      if (stepName === 'weigh' && guestSessionIsReal.value) {
        try {
          const res = await api.post('/hardware/session/complete', {
            session_code:      session.value.session_code,
            ai_detected_type:  payload.ai_detected_type || payload.material_selected,
            image_path:        payload.image_path,
          })
          if (res.data.success) return res.data
        } catch { /* fall through to the local mock below */ }
      }
      // No points/DB record for guests, but the physical servo still sorts
      // the item — fire-and-forget so a slow/offline AI service can't stall
      // the on-screen flow.
      if (stepName === 'complete') {
        const material = payload.ai_detected_type || payload.material_selected || 'reject'
        api.post('/hardware/sort', { material }).catch(() => {})
      }
      return _guestMockStep(stepName, payload)
    }

    const endpoints = {
      open_lid: '/transactions/open-lid',
      insert:   '/transactions/insert-item',
      conveyor: '/transactions/process-conveyor',
      capture:  '/transactions/capture-image',
      classify: '/transactions/classify',
      weigh:    '/transactions/weigh',
      complete: '/transactions/complete',
    }

    const endpoint = endpoints[stepName]
    if (!endpoint) throw new Error(`Unknown step: ${stepName}`)

    const res = await api.post(endpoint, {
      session_code:      session.value?.session_code,
      material_selected: selectedMaterial.value,
      ...payload,
    })

    if (res.data.transaction_id) currentTransaction.value = res.data
    return res.data
  }

  function resetTransaction() {
    currentTransaction.value = null
    selectedMaterial.value   = null
    lastError.value          = null
    // The lid is opened once when the session starts. For every following
    // item, return directly to the insertion step and keep the lid open.
    setStep('insert')
  }

  function resetSession() {
    clearKioskSession()
    session.value            = null
    machine.value            = null
    currentStep.value        = 'landing'
    selectedMaterial.value   = null
    currentTransaction.value = null
    lastError.value          = null
    isGuest.value            = false
    guestMachineCode.value   = null
    guestSessionIsReal.value = false
    localSummary.value       = { total_items: 0, points_earned: 0, start_points: 0, transactions: [] }
  }

  return {
    session, machine, currentStep, selectedMaterial, currentTransaction, lastError, steps,
    localSummary, isGuest, guestMachineCode, guestSessionIsReal,
    setStep, setMachine, setSession, setSelectedMaterial, recordLocalTransaction,
    startGuestSession, startSession, endSession, getSummary, checkBin, processStep,
    restoreKioskSession, resetTransaction, resetSession,
  }
})
