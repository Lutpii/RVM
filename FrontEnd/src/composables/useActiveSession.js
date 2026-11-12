// FrontEnd/src/composables/useActiveSession.js
// Polls the visitor's open session while the component is mounted.
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/store/auth'

export function useActiveSession({ intervalMs = 4000 } = {}) {
  const auth = useAuthStore()
  const session = ref(null)
  let timer = null
  let stopped = false

  async function refresh() {
    try {
      const res = await api.get('/user/active-session')
      if (stopped) return
      const next = res.data?.session || null
      const before = session.value?.points_earned
      session.value = next
      if (next && before !== undefined && before !== next.points_earned) {
        auth.fetchMe()
      }
    } catch {
      // keep the last known state; the next poll will retry
    }
  }

  onMounted(() => {
    stopped = false
    refresh()
    timer = setInterval(refresh, intervalMs)
  })
  onUnmounted(() => {
    stopped = true
    clearInterval(timer)
    timer = null
  })

  return { session, refresh }
}
