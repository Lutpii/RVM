import api from '@/services/api'

// Client for the 2-bin compactor proxies (TransactionController::hardware*
// -> ai_service/machine_api.py). Never throws: callers treat null as
// "machine unreachable".

export async function getHardwareState(jobId = null) {
  try {
    const res = await api.get('/hardware/state', { params: jobId ? { job: jobId } : {} })
    return res.data
  } catch {
    return null
  }
}

export async function depositItem(material, allowFlush) {
  try {
    const res = await api.post('/hardware/deposit', { material, allow_flush: allowFlush })
    return res.data
  } catch (err) {
    // 400 = material has no bin on this machine; it carries a real answer.
    return err.response?.status === 400 ? err.response.data : null
  }
}

export async function flushChamber() {
  try {
    const res = await api.post('/hardware/flush')
    return res.data
  } catch {
    return null
  }
}

// Unreachable counts as empty so a dead service can't lock the kiosk on
// this screen; the deposit that follows fails visibly instead.
export async function isFlapEmpty() {
  try {
    const res = await api.get('/hardware/flap-check')
    return res.data?.empty !== false
  } catch {
    return true
  }
}
