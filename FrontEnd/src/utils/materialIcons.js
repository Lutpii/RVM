// Small per-class icons shown next to a material name. Fixed lookup only —
// never interpolates the detected string into markup — so it's safe to render via v-html.
export const MATERIAL_ICON_PATHS = {
  aluminum: '<path d="M7 4h10l-1 2.5v11A1.5 1.5 0 0 1 14.5 19h-5A1.5 1.5 0 0 1 8 17.5v-11L7 4Z"/><path d="M8 4V3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1"/><path d="M8 9h8"/>',
  plastic:  '<path d="M10 2h4v3l1.5 2v13.5A1.5 1.5 0 0 1 14 22h-4a1.5 1.5 0 0 1-1.5-1.5V7L10 5V2Z"/><path d="M8.5 11h7"/>',
  glass:    '<path d="M10.5 2h3v5l2 3v10a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 8.5 20V10l2-3V2Z" fill="currentColor" fill-opacity="0.15"/><path d="M9 15h6"/>',
  paper:    '<path d="M6 3h9l3 3v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M15 3v3h3"/><path d="M8 12h8M8 16h8M8 8h4"/>',
}

export function materialIconSvg(material) {
  return MATERIAL_ICON_PATHS[(material || '').toLowerCase()] || ''
}
