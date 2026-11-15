// The user code shown in the admin tables and Excel exports
// (AdminController::displayUserId on the backend). display_no is the
// registration-order number; the database id is only a fallback.
export function formatUserId(user) {
  if (!user) return 'GUEST'
  const no = String(user.display_no ?? user.id)
  if (user.role === 'admin') return 'ADMIN-' + no.padStart(3, '0')
  return 'USR-' + no.padStart(4, '0')
}
