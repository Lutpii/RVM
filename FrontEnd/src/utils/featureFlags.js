// src/utils/featureFlags.js
// Per-deploy switches, set as build-time env vars (Vercel project settings,
// or FrontEnd/.env.local) — each deploy has its own Google OAuth client, or none.

// "Continue with Google" stays visible but disabled unless this is 'true'.
export const googleLoginEnabled = import.meta.env.VITE_GOOGLE_LOGIN === 'true'

// "Demonstration data (simulated)" badge on the recycler and admin pages, for a
// deploy that runs on generated demo data instead of live machine readings.
export const demoDataLabel = import.meta.env.VITE_DEMO_DATA_LABEL === 'true'
