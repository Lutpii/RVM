import { describe, it, expect, vi } from 'vitest'
import { escapeListener } from './useEscapeToClose.js'

describe('escapeListener', () => {
  it('calls close on Escape', () => {
    const close = vi.fn()
    escapeListener(close)({ key: 'Escape' })
    expect(close).toHaveBeenCalledTimes(1)
  })
  it('ignores other keys and IME composition', () => {
    const close = vi.fn()
    const onKey = escapeListener(close)
    onKey({ key: 'Enter' })
    onKey({ key: 'Escape', isComposing: true })
    expect(close).not.toHaveBeenCalled()
  })
})
