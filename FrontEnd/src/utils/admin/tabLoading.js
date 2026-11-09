const TAB_LOADING_FLAG = {
  dashboard: 'loadingStats',
  transactions: 'loadingTransactions',
  users: 'loadingUsers',
  machines: 'loadingMachines',
  sessions: 'loadingSessions',
  audit: 'loadingAudit',
  detection: 'loadingDetectionLogs',
  rewards: 'loadingRewardItems',
}

export function resolveLoadingFlag(tab) {
  return TAB_LOADING_FLAG[tab] ?? null
}
