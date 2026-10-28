import { describe, it, expect } from 'vitest'
import { voucherLink } from './voucher.js'

describe('voucherLink', () => {
  it('points at the hash-routed voucher check page of the current deploy', () => {
    expect(voucherLink('RVM-ABCD-EFGH', 'https://rvm-eight.vercel.app', '/'))
      .toBe('https://rvm-eight.vercel.app/#/voucher/RVM-ABCD-EFGH')
  })

  it('keeps a sub-path deploy (e.g. the kiosk) in the link', () => {
    expect(voucherLink('RVM-ABCD-EFGH', 'https://192.168.0.100', '/app/'))
      .toBe('https://192.168.0.100/app/#/voucher/RVM-ABCD-EFGH')
  })
})
