// Small per-class icons shown next to a material name. Fixed lookup only —
// never interpolates the detected string into markup — so it's safe to render via v-html.
export const MATERIAL_ICON_PATHS = {
  // Drink can: oval lid with opening, tapered shoulders, wave label band.
  aluminum: '<ellipse cx="12" cy="4.5" rx="4.5" ry="1.5" fill="currentColor" fill-opacity=".18"/><path d="M7.5 4.5 6.5 7v11l1 1.8c.8.8 2.5 1.2 4.5 1.2s3.7-.4 4.5-1.2l1-1.8V7l-1-2.5" fill="currentColor" fill-opacity=".18"/><path d="M6.5 7h11M6.5 18h11M6.5 11.5c1.8 1.3 3.7 1.3 5.5 0s3.7-1.3 5.5 0"/><path d="M11 4.3h2.4" stroke-width="1.3"/>',
  plastic: '<path d="M10 2h4v3h-4z" fill="currentColor"/><path d="M10.5 5v2L8 9.5V12l1 1-1 1v2l1 1-1 1v2.5A1.5 1.5 0 0 0 9.5 22h5a1.5 1.5 0 0 0 1.5-1.5V18l-1-1 1-1v-2l-1-1 1-1V9.5L13.5 7V5" fill="currentColor" fill-opacity=".18"/><path d="M9 13h6m-6 4h6"/>',
  glass: '<path d="M10 2h4m-3.5 0v6l-3 4.2A3 3 0 0 0 7 14v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-6a3 3 0 0 0-.5-1.8L13.5 8V2" fill="currentColor" fill-opacity=".22"/><path d="M7 15h10m-7 2v2"/>',
  paper: '<path d="M7 20H5a1 1 0 0 1-1-1V5" /><path d="M7 2h8l5 5v13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V2Z" fill="currentColor" fill-opacity=".18"/><path d="M15 2v5h5m-10 4h7m-7 3h7m-7 3h4"/>',
}

export function materialIconSvg(material) {
  return MATERIAL_ICON_PATHS[(material || '').toLowerCase()] || ''
}
