// src/utils/maintenance.js
// Whole-site maintenance switch. While MAINTENANCE_ON is true, every link of a
// production build (landing, login, dashboard, admin, both kiosks, vouchers)
// shows the maintenance page instead of the app, and the app itself is never
// started, so no API call is made from the browser.
//
// Turn it OFF: set MAINTENANCE_ON = false, commit, push (Vercel rebuilds).
// Local `npm run dev` is never blocked; preview the page there with
// VITE_MAINTENANCE_PREVIEW=true. A single deploy can opt out with
// VITE_MAINTENANCE_MODE=false.
export const MAINTENANCE_ON = true

export function isMaintenance(env = import.meta.env, on = MAINTENANCE_ON) {
  if (env.VITE_MAINTENANCE_PREVIEW === 'true') return true
  if (env.VITE_MAINTENANCE_MODE === 'false') return false
  return Boolean(on && env.PROD)
}

export const maintenanceText = {
  en: {
    title: 'Under maintenance',
    body: 'Ultra RVM is temporarily unavailable while we carry out maintenance. Please check back later.',
    safe: 'Your account and points are safe.',
    tab: 'Under maintenance - Ultra RVM',
  },
  my: {
    title: 'Dalam penyelenggaraan',
    body: 'Ultra RVM tidak dapat digunakan buat sementara waktu kerana kerja penyelenggaraan. Sila cuba lagi kemudian.',
    safe: 'Akaun dan mata ganjaran anda selamat.',
    tab: 'Dalam penyelenggaraan - Ultra RVM',
  },
  id: {
    title: 'Sedang dalam pemeliharaan',
    body: 'Ultra RVM untuk sementara tidak dapat digunakan karena sedang dalam pemeliharaan. Silakan kembali lagi nanti.',
    safe: 'Akun dan poin Anda tetap aman.',
    tab: 'Sedang dalam pemeliharaan - Ultra RVM',
  },
}
