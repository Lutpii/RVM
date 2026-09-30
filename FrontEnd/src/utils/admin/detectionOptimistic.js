// Optimistic Detection Review saves: a reviewed card leaves the list at once
// while the PATCH runs in the background, and comes back if it fails.
//
// "Hidden" ids are tracked as a Map of id -> savedAt, where savedAt is null
// while the save is in flight and a timestamp once the server confirmed it.
// Any list fetched from the server is filtered through it, so a response
// that was already on its way (a refill, the 30s auto-refresh) can't bring a
// just-reviewed card back.

export function removeLog(logs, id) {
  const index = logs.findIndex(log => log.id === id)
  if (index === -1) return { logs, index: -1, log: null }
  return { logs: [...logs.slice(0, index), ...logs.slice(index + 1)], index, log: logs[index] }
}

export function restoreLog(logs, log, index) {
  if (logs.some(item => item.id === log.id)) return logs
  const at = index < 0 ? logs.length : Math.min(index, logs.length)
  return [...logs.slice(0, at), log, ...logs.slice(at)]
}

export function filterHidden(logs, hidden) {
  return hidden.size ? logs.filter(log => !hidden.has(log.id)) : logs
}

// Drop the ids a request started at `requestStartedAt` is guaranteed to
// reflect already (saved before it began); keep in-flight and later saves.
export function pruneSettled(hidden, requestStartedAt) {
  return new Map([...hidden].filter(([, savedAt]) => savedAt === null || savedAt > requestStartedAt))
}
