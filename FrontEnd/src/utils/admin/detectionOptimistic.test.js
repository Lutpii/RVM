import { describe, expect, it } from 'vitest'
import { filterHidden, pruneSettled, removeLog, restoreLog } from './detectionOptimistic.js'

const a = { id: 1 }, b = { id: 2 }, c = { id: 3 }

describe('removeLog', () => {
  it('removes the log and reports where it was', () => {
    expect(removeLog([a, b, c], 2)).toEqual({ logs: [a, c], index: 1, log: b })
  })

  it('leaves the list alone when the id is not there', () => {
    const logs = [a, c]
    expect(removeLog(logs, 2)).toEqual({ logs, index: -1, log: null })
  })

  it('does not mutate the input', () => {
    const logs = [a, b]
    removeLog(logs, 1)
    expect(logs).toEqual([a, b])
  })
})

describe('restoreLog', () => {
  it('puts the log back at its old position', () => {
    expect(restoreLog([a, c], b, 1)).toEqual([a, b, c])
  })

  it('clamps to the end if the list got shorter', () => {
    expect(restoreLog([a], c, 5)).toEqual([a, c])
  })

  it('appends when the old position is unknown', () => {
    expect(restoreLog([a], b, -1)).toEqual([a, b])
  })

  it('does not duplicate a log that is already back (e.g. from a refetch)', () => {
    const logs = [a, b]
    expect(restoreLog(logs, b, 0)).toBe(logs)
  })
})

describe('filterHidden', () => {
  it('drops logs that are hidden', () => {
    const hidden = new Map([[2, null]])
    expect(filterHidden([a, b, c], hidden)).toEqual([a, c])
  })

  it('returns everything when nothing is hidden', () => {
    expect(filterHidden([a, b], new Map())).toEqual([a, b])
  })
})

describe('pruneSettled', () => {
  it('keeps saves that are still in flight (savedAt null)', () => {
    const hidden = new Map([[1, null]])
    expect([...pruneSettled(hidden, 1000).keys()]).toEqual([1])
  })

  it('forgets saves that finished before the request started (server already has them)', () => {
    const hidden = new Map([[1, 500], [2, 1000]])
    expect([...pruneSettled(hidden, 1000).keys()]).toEqual([])
  })

  it('keeps saves that finished after the request started (response may be stale)', () => {
    const hidden = new Map([[1, 1500]])
    expect([...pruneSettled(hidden, 1000).keys()]).toEqual([1])
  })

  it('does not mutate the input', () => {
    const hidden = new Map([[1, 500]])
    pruneSettled(hidden, 1000)
    expect(hidden.size).toBe(1)
  })
})
