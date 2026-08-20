import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useRvmStore = defineStore('rvm', () => {
  const session            = ref(null)
  const machine            = ref(null)
  const currentStep        = ref('landing')
  const selectedMaterial   = ref(null)
  const currentTransaction = ref(null)
  const lastError          = ref(null)
  const isGuest            = ref(false)
  const guestMachineCode   = ref(null)

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
    'weigh', 'complete', 'rejected', 'summary'
  ]

  function setStep(step) { currentStep.value = step }
  function setMachine(d) { machine.value = d }
  function setSession(d) { session.value = d }
  function setSelectedMaterial(m) { selectedMaterial.value = m }

  function startGuestSession(machineCode, machineData = null) {
    isGuest.value          = true
    guestMachineCode.value = machineCode
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
  }

  function _guestMockStep(stepName, payload) {
    const POINTS_PER_100G = { aluminum: 10, plastic: 8, glass: 6, paper: 5 }
    if (stepName === 'classify') {
      const selected = payload.material_selected || selectedMaterial.value
      return { success: true, is_valid: true, ai_detected: selected, confidence: 0.95, all_predictions: [], step: 'validated' }
    }
    if (stepName === 'weigh') {
      const material = payload.material_selected || selectedMaterial.value
      const weight   = Math.floor(Math.random() * 400) + 50
      const points   = Math.floor((weight / 100) * (POINTS_PER_100G[material] || 5))
      return { success: true, weight_grams: weight, points_earned: points, material, step: 'weighed' }
    }
    if (stepName === 'complete') {
      return { success: true, points_earned: payload.points_earned || 0, total_points: 0, step: 'complete' }
    }
    if (stepName === 'reject') {
      return { success: false, points_deducted: 0, total_points: 0, step: 'rejected' }
    }
    return { success: true }
  }

  function recordLocalTransaction({ material, weight, points, isValid, deducted = 0 }) {
    localSummary.value.transactions.push({ material, weight, points_earned: points, points_deducted: deducted, is_valid: isValid })
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
      // No points/DB record for guests, but the physical servo still sorts
      // the item — fire-and-forget so a slow/offline AI service can't stall
      // the on-screen flow.
      if (stepName === 'complete' || stepName === 'reject') {
        const material = stepName === 'reject'
          ? 'reject'
          : (payload.ai_detected_type || payload.material_selected || 'reject')
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
      reject:   '/transactions/reject',
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
    setStep('bin_check')
  }

  function resetSession() {
    session.value            = null
    machine.value            = null
    currentStep.value        = 'landing'
    selectedMaterial.value   = null
    currentTransaction.value = null
    lastError.value          = null
    isGuest.value            = false
    guestMachineCode.value   = null
    localSummary.value       = { total_items: 0, points_earned: 0, start_points: 0, transactions: [] }
  }

  return {
    session, machine, currentStep, selectedMaterial, currentTransaction, lastError, steps,
    localSummary, isGuest, guestMachineCode,
    setStep, setMachine, setSession, setSelectedMaterial, recordLocalTransaction,
    startGuestSession, startSession, endSession, getSummary, checkBin, processStep,
    resetTransaction, resetSession,
  }
})
