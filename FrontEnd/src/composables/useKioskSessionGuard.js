// Shared by both kiosks: ends an idle session (idle timer) and notices a
// session that ended elsewhere (status watch), showing "Session ended" for 3 s
// before clearing the local session and going to the kiosk landing page.
import { ref, computed, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { setKioskToken } from '@/services/api'
import { useRvmStore } from '@/store/rvm'
import { useIdleTimer } from '@/composables/useIdleTimer'
import { useSessionWatch } from '@/composables/useSessionWatch'
import { isIdleStep } from '@/utils/kioskIdleSteps'

export const SESSION_ENDED_MS = 3000

export function useKioskSessionGuard({ kind, step, gate, isGuest, sessionCode, endAction, onBeforeLeave, landingPath }) {
  const router = useRouter()
  const rvm = useRvmStore()
  const endingSession = ref(false)
  const leaving = ref(false)
  const sessionEnded = ref(false)
  let unmounted = false

  const onIdleStep = computed(() => gate.value && isIdleStep(kind, step.value))

  const idle = useIdleTimer({
    isActive: onIdleStep,
    onExpire: () => { if (!leaving.value && !endingSession.value) endAction() },
  })

  function onEnded() {
    if (leaving.value || unmounted) return
    leaving.value = true
    sessionEnded.value = true
    const target = landingPath()
    if (onBeforeLeave) onBeforeLeave()
    setTimeout(() => {
      if (unmounted) return
      setKioskToken(null)
      rvm.resetSession()
      router.replace(target)
    }, SESSION_ENDED_MS)
  }

  useSessionWatch({
    sessionCode,
    enabled: computed(() => onIdleStep.value && !isGuest.value && !endingSession.value && !leaving.value),
    onEnded,
  })

  onBeforeUnmount(() => { unmounted = true })

  return {
    showOverlay: computed(() => idle.showOverlay.value && !leaving.value),
    msLeft: idle.msLeft,
    poke: idle.poke,
    sessionEnded,
    endingSession,
  }
}
