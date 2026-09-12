/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,jsx,ts,tsx}',
  ],
  corePlugins: {
    // Preflight is disabled on purpose: the project already ships its own
    // global reset in src/assets/main.css tuned to ~8,400 lines of existing
    // hand-written view CSS. Tailwind's fuller Preflight (stripping default
    // button/list/heading/form styles, etc.) was never accounted for by
    // that CSS and would risk a visual regression across the whole app.
    preflight: false,
  },
  theme: {
    extend: {
      colors: {
        'bg-primary': 'var(--bg-primary)',
        'bg-secondary': 'var(--bg-secondary)',
        'bg-card': 'var(--bg-card)',
        'bg-hover': 'var(--bg-hover)',
        'text-primary': 'var(--text-primary)',
        'text-secondary': 'var(--text-secondary)',
        'text-muted': 'var(--text-muted)',
        'accent-blue': 'var(--accent-blue)',
        'accent-purple': 'var(--accent-purple)',
        'accent-green': 'var(--accent-green)',
        'accent-red': 'var(--accent-red)',
        'accent-yellow': 'var(--accent-yellow)',
        border: 'var(--border)',
      },
      borderRadius: {
        DEFAULT: 'var(--radius)',
      },
      boxShadow: {
        DEFAULT: 'var(--shadow)',
      },
      spacing: {
        // Minimum touch-target size for kiosk views (spec §4). Config-only
        // in this plan — first consumed when the 6 Pi-reachable views are
        // migrated in a later phase.
        'kiosk-touch': '56px',
      },
    },
  },
  plugins: [],
}
