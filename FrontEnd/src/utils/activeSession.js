// FrontEnd/src/utils/activeSession.js
// Totals for the phone's "Session in progress" card, from GET /user/active-session.
export function summarizeSession(session) {
  const items = Array.isArray(session?.items) ? session.items : []
  const machine = session?.machine || {}
  return {
    machineName: machine.name || '',
    location: machine.location || machine.location_name || '',
    isDemo: machine.is_demo === true || session?.is_demo === true,
    items: items.length,
    points: items.reduce((sum, i) => sum + (Number(i.points_earned) || 0), 0),
    carbon: items.reduce((sum, i) => sum + (Number(i.carbon_saved) || 0), 0),
  }
}
