// FrontEnd/src/composables/useEscapeToClose.js
// Lets a modal close on the Escape key, and moves focus into it on open.
import { onMounted, onUnmounted, nextTick } from 'vue'

export function escapeListener(close) {
  return (e) => {
    if (e.key === 'Escape' && !e.isComposing) close()
  }
}

// focusRef: a template ref to focus when the modal opens (e.g. its close button).
export function useEscapeToClose(close, focusRef = null) {
  const onKey = escapeListener(close)
  onMounted(async () => {
    window.addEventListener('keydown', onKey)
    await nextTick()
    focusRef?.value?.focus?.()
  })
  onUnmounted(() => window.removeEventListener('keydown', onKey))
}
