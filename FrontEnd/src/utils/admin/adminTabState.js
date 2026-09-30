// Admin panel menu state <-> URL query (#/admin?tab=detection&view=history),
// so a refresh (or a shared/bookmarked link) reopens the same menu.

export const DEFAULT_ADMIN_TAB = 'dashboard'
export const DETECTION_VIEWS = ['pending', 'history', 'test']
export const DEFAULT_DETECTION_VIEW = 'pending'

// `tabs` is the list of valid tab ids (AdminView's navItems). Anything
// unknown, including a repeated param (which vue-router gives as an array),
// falls back to the default.
export function parseAdminQuery(query, tabs) {
  const tab = tabs.includes(query?.tab) ? query.tab : DEFAULT_ADMIN_TAB
  const detectionView = tab === 'detection' && DETECTION_VIEWS.includes(query?.view)
    ? query.view
    : DEFAULT_DETECTION_VIEW
  return { tab, detectionView }
}

// Defaults are left out so the plain dashboard stays at #/admin.
export function buildAdminQuery({ tab, detectionView }) {
  const query = {}
  if (tab !== DEFAULT_ADMIN_TAB) query.tab = tab
  if (tab === 'detection' && detectionView !== DEFAULT_DETECTION_VIEW) query.view = detectionView
  return query
}
