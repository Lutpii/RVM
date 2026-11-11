import { describe, it, expect } from 'vitest'
import { parseAvatar, formatAvatar, maskEmail, AVATAR_ICONS, AVATAR_COLOURS } from './avatarIcons.js'

describe('parseAvatar', () => {
  it('reads a valid icon:colour pair', () => {
    const a = parseAvatar('leaf:green')
    expect(a.icon).toBe('leaf')
    expect(a.colour).toBe('green')
    expect(a.hex).toBe('#16a34a')
    expect(a.component).toBeTruthy()
  })
  it('rejects anything not offered', () => {
    for (const bad of [null, '', 'leaf', 'dragon:green', 'leaf:black', 'leaf:green:x', 'https://x/y.png']) {
      expect(parseAvatar(bad)).toBeNull()
    }
  })
  it('round-trips every offered combination', () => {
    for (const i of AVATAR_ICONS) for (const c of AVATAR_COLOURS) {
      expect(parseAvatar(formatAvatar(i.key, c.key))?.icon).toBe(i.key)
    }
  })
})

describe('maskEmail', () => {
  it('keeps the first two letters and the domain', () => {
    expect(maskEmail('nurul.aina@demo.rvm')).toBe('nu•••••@demo.rvm')
    expect(maskEmail('a@x.com')).toBe('a•••••@x.com')
  })
  it('leaves non-emails alone', () => {
    expect(maskEmail('')).toBe('')
    expect(maskEmail(undefined)).toBe('')
    expect(maskEmail('0123456789')).toBe('0123456789')
  })
})
