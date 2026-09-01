const TAB_LOADING_FLAG = {
  dashboard: 'loadingStats',
  transactions: 'loadingTransactions',
  users: 'loadingUsers',
  machines: 'loadingMachines',
  sessions: 'loadingSessions',
}

export function resolveLoadingFlag(tab) {
  return TAB_LOADING_FLAG[tab] ?? null
}
