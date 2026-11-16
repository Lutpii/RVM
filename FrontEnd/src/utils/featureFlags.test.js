import { afterEach, describe, expect, it, vi } from 'vitest'

describe('platformMode', () => {
  afterEach(() => {
    vi.unstubAllEnvs()
    vi.resetModules()
  })

  it('enables platform mode when the flag is true', async () => {
    vi.stubEnv('VITE_PLATFORM_MODE', 'true')

    const { platformMode } = await import('./featureFlags.js')
    expect(platformMode).toBe(true)
  })

  it('defaults to disabled when the flag is unset', async () => {
    vi.stubEnv('VITE_PLATFORM_MODE', undefined)

    const { platformMode } = await import('./featureFlags.js')
    expect(platformMode).toBe(false)
  })
})
