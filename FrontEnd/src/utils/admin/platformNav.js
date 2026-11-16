export function platformNavGroups(t) {
  return [
    { group: 'platform', items: [{ id: 'platform-overview', label: t('adminUi.platform.overview') }] },
    { group: 'machines', items: [
      { id: 'dashboard', label: t('platform.machine.rvm') },
      { id: 'compactor', label: t('platform.machine.compactor') },
      { id: 'composter', label: t('platform.machine.composter') },
    ] },
    { group: 'manage', items: [
      { id: 'machines', label: t('adminUi.navMachines') },
      { id: 'rewards', label: t('adminUi.navRewards') },
      { id: 'users', label: t('adminUi.navUsers') },
      { id: 'transactions', label: t('adminUi.navTransactions') },
      { id: 'sessions', label: t('adminUi.navSessions') },
      { id: 'audit', label: t('adminUi.navAudit') },
      { id: 'detection', label: t('admin.detectionReview.title') },
    ] },
  ]
}
