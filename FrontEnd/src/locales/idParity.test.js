// Bahasa Indonesia must cover every English message, keep its placeholders
// ({name}, {points}, ...) and use valid UTF-8.
import { describe, it, expect } from 'vitest'
import { readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import { messages } from './index.js'

const leaves = (obj, prefix = '') =>
  Object.entries(obj).flatMap(([k, v]) =>
    v && typeof v === 'object' ? leaves(v, `${prefix}${k}.`) : [[`${prefix}${k}`, v]])

const placeholders = (s) => [...String(s).matchAll(/\{(\w+)\}/g)].map((m) => m[1]).sort()

describe('Bahasa Indonesia messages', () => {
  it('exist', () => {
    expect(messages.id).toBeTruthy()
  })

  it('have exactly the English keys', () => {
    const en = leaves(messages.en).map(([k]) => k).sort()
    const id = leaves(messages.id ?? {}).map(([k]) => k).sort()
    expect(id.filter((k) => !en.includes(k))).toEqual([])
    expect(en.filter((k) => !id.includes(k))).toEqual([])
  })

  it('are non-empty strings that keep the English placeholders', () => {
    const id = Object.fromEntries(leaves(messages.id ?? {}))
    const bad = leaves(messages.en).filter(([k, v]) =>
      typeof id[k] !== 'string' || !id[k].trim() ||
      JSON.stringify(placeholders(id[k])) !== JSON.stringify(placeholders(v)))
    expect(bad.map(([k]) => k)).toEqual([])
  })

  it('file is valid UTF-8', () => {
    const bytes = readFileSync(fileURLToPath(new URL('./id.js', import.meta.url)))
    expect(() => new TextDecoder('utf-8', { fatal: true }).decode(bytes)).not.toThrow()
  })
})
