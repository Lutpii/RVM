import api from '@/services/api'

// Fail closed: only a machine confirmed as is_demo === true counts.
export async function isDemoMachine(machineCode) {
  try {
    const res = await api.get('/machines')
    const machine = (res.data.machines || []).find(m => m.machine_code === machineCode)
    return machine?.is_demo === true
  } catch {
    return false
  }
}
