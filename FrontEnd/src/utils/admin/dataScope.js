// Admin data switch: which rows the admin panel shows (all / real only / demo only).
// Remembered for the browser session so a refresh keeps the choice.
export const DATA_SCOPES = ['all', 'real', 'demo']
export const DATA_SCOPE_KEY = 'rvm_admin_data_scope'

function defaultStorage() {
  try { return typeof sessionStorage === 'undefined' ? null : sessionStorage } catch { return null }
}

export function readDataScope(storage = defaultStorage()) {
  try {
    const value = storage?.getItem(DATA_SCOPE_KEY)
    return DATA_SCOPES.includes(value) ? value : 'all'
  } catch {
    return 'all'
  }
}

export function writeDataScope(scope, storage = defaultStorage()) {
  if (!DATA_SCOPES.includes(scope)) return
  try { storage?.setItem(DATA_SCOPE_KEY, scope) } catch { /* storage unavailable: keep in memory only */ }
}
