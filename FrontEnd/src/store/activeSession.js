// FrontEnd/src/store/activeSession.js
// One shared poll of the visitor's open session for the whole app.
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/store/auth'

const POLL_MS = 4000

export const useActiveSessionStore = defineStore('activeSession', () => {
  const session = ref(null)
  const sheetOpen = ref(false)
  let timer = null
  let latestToken = 0

  async function refresh() {
    const token = ++latestToken
    try {
      const res = await api.get('/user/active-session')
      if (token !== latestToken) return // a newer request or stop() superseded this one
      const next = res.data?.session || null
      const before = session.value?.points_earned
      session.value = next
      if (!next) sheetOpen.value = false
      if (next && before !== undefined && before !== next.points_earned) {
        useAuthStore().fetchMe()
      }
    } catch {
      // keep the last known state; the next poll will retry
    }
  }

  function start() {
    if (timer !== null) return
    if (!useAuthStore().isLoggedIn) return
    timer = setInterval(refresh, POLL_MS)
    refresh()
  }

  function stop() {
    latestToken++ // invalidate in-flight requests
    if (timer !== null) clearInterval(timer)
    timer = null
    session.value = null
    sheetOpen.value = false
  }

  return { session, sheetOpen, refresh, start, stop }
})
