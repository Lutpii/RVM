import { describe, it, expect } from 'vitest'
import { extractQrToken, afterQrExpiry, QR_LIFETIME_SECONDS, QR_MAX_REFRESHES } from './qrToken'

describe('extractQrToken', () => {
  it('extracts the token from a full scan URL', () => {
    expect(extractQrToken('https://10.0.0.5/#/scan?token=ABC123&machine=RVM-001')).toBe('ABC123')
  })

  it('extracts the token from a scan URL pasted without a protocol', () => {
    expect(extractQrToken('10.0.0.5:5173/#/scan?token=ABC123&machine=RVM-001')).toBe('ABC123')
  })

  it('returns the raw value unchanged when it is already a bare token', () => {
    expect(extractQrToken('ABC123')).toBe('ABC123')
  })

  it('trims surrounding whitespace', () => {
    expect(extractQrToken('  ABC123  ')).toBe('ABC123')
  })

  it('decodes a URL-encoded token value', () => {
    expect(extractQrToken('https://host/#/scan?token=AB%2F12&machine=RVM-001')).toBe('AB/12')
  })
})

describe('afterQrExpiry', () => {
  it('shows a new QR 3 times, then sends the kiosk back to its landing page', () => {
    expect(QR_LIFETIME_SECONDS).toBe(100)
    expect(QR_MAX_REFRESHES).toBe(3)
    expect(afterQrExpiry(0)).toBe('refresh')
    expect(afterQrExpiry(1)).toBe('refresh')
    expect(afterQrExpiry(2)).toBe('refresh')
    expect(afterQrExpiry(3)).toBe('leave')
  })
})
