// Polls the session status while enabled; calls onEnded once when the
// session is no longer active, or the call fails with 401/404.
import { watch, onBeforeUnmount } from 'vue'
import api from '@/services/api'

export function useSessionWatch({ sessionCode, enabled, onEnded, intervalMs = 5000 }) {
  let timer = null
  let ended = false
  let busy = false
  let generation = 0

  const stop = () => { if (timer) { clearInterval(timer); timer = null } }

  const end = () => {
    if (ended) return
    ended = true
    stop()
    onEnded()
  }

  const poll = async () => {
    const code = sessionCode.value
    if (!enabled.value || !code || ended || busy) return
    busy = true
    const requestGeneration = generation
    const isCurrent = () => generation === requestGeneration && enabled.value && sessionCode.value === code
    try {
      const { data } = await api.get(`/sessions/${code}/status`)
      if (isCurrent() && data?.status !== 'active') end()
    } catch (e) {
      const s = e?.response?.status
      if (isCurrent() && (s === 401 || s === 404)) end()
    } finally {
      busy = false
    }
  }

  watch([enabled, sessionCode], () => {
    generation++
    stop()
    if (enabled.value && sessionCode.value) {
      ended = false
      timer = setInterval(poll, intervalMs)
    }
  }, { immediate: true, flush: 'sync' })

  onBeforeUnmount(() => {
    generation++
    stop()
  })
}
