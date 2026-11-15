import { describe, it, expect } from 'vitest'
import { kioskLink } from './kioskLink.js'

describe('kioskLink', () => {
  it('builds the real kiosk link on the site the admin is using', () => {
    expect(kioskLink({ machine_code: 'RVM-001', is_demo: false }, 'https://rvm-eight.vercel.app'))
      .toBe('https://rvm-eight.vercel.app/#/kiosk/RVM-001')
  })
  it('sends demo machines to the demo kiosk', () => {
    expect(kioskLink({ machine_code: 'RVM-DEMO', is_demo: true }, 'https://abc.trycloudflare.com/'))
      .toBe('https://abc.trycloudflare.com/#/demo-kiosk/RVM-DEMO')
  })
  it('encodes odd machine codes and returns empty without a code', () => {
    expect(kioskLink({ machine_code: 'RVM 7' }, 'https://x.test')).toBe('https://x.test/#/kiosk/RVM%207')
    expect(kioskLink({}, 'https://x.test')).toBe('')
  })
})
