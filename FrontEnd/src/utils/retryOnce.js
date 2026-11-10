// FrontEnd/src/utils/retryOnce.js
// Retry a request once when it never got an answer (U04). Paired with an
// idempotency key, the retry of a deposit whose response was lost returns the
// first result instead of crediting twice or failing.

// True when the request never got a response (network down, timeout).
export function isNetworkError(err) {
  return !err?.response
}

export async function retryOnce(fn, isRetryable = isNetworkError) {
  try {
    return await fn()
  } catch (err) {
    if (!isRetryable(err)) throw err
    return await fn()
  }
}

export function newIdempotencyKey() {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') return crypto.randomUUID()
  return `k-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 12)}`
}
