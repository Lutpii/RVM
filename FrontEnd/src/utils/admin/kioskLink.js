// The kiosk screen link for a machine, on whichever site the admin is using
// (live Vercel or the local tunnel). Demo machines open the demo kiosk.
export function kioskLink(machine, origin) {
  const code = machine?.machine_code
  if (!code) return ''
  const base = String(origin || '').replace(/\/+$/, '')
  return `${base}/#/${machine.is_demo ? 'demo-kiosk' : 'kiosk'}/${encodeURIComponent(code)}`
}
