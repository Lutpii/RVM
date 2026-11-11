// Icon + colour avatars a user can pick on the Account page. The keys must
// match BackEnd/app/Support/AvatarIcon.php; the icons are Phosphor (MIT).
import {
  PhLeaf, PhRecycle, PhTree, PhGlobeHemisphereEast, PhSun, PhDrop,
  PhPlant, PhBicycle, PhFish, PhCat, PhHeart, PhButterfly,
} from '@phosphor-icons/vue'

export const AVATAR_ICONS = [
  { key: 'leaf', label: 'Leaf', icon: PhLeaf },
  { key: 'recycle', label: 'Recycle', icon: PhRecycle },
  { key: 'tree', label: 'Tree', icon: PhTree },
  { key: 'globe', label: 'Globe', icon: PhGlobeHemisphereEast },
  { key: 'sun', label: 'Sun', icon: PhSun },
  { key: 'drop', label: 'Water drop', icon: PhDrop },
  { key: 'plant', label: 'Plant', icon: PhPlant },
  { key: 'bicycle', label: 'Bicycle', icon: PhBicycle },
  { key: 'fish', label: 'Fish', icon: PhFish },
  { key: 'cat', label: 'Cat', icon: PhCat },
  { key: 'heart', label: 'Heart', icon: PhHeart },
  { key: 'butterfly', label: 'Butterfly', icon: PhButterfly },
]

// Dark enough for a white icon on top (contrast >= 3:1 for a 24px+ glyph).
export const AVATAR_COLOURS = [
  { key: 'green', label: 'Green', hex: '#16a34a' },
  { key: 'blue', label: 'Blue', hex: '#4e6ef2' },
  { key: 'purple', label: 'Purple', hex: '#9333ea' },
  { key: 'orange', label: 'Orange', hex: '#d97706' },
  { key: 'teal', label: 'Teal', hex: '#0f9488' },
  { key: 'pink', label: 'Pink', hex: '#db2777' },
]

export const DEFAULT_AVATAR = { icon: 'leaf', colour: 'green' }

/** "leaf:green" -> { icon, colour, component, hex }, or null for anything else. */
export function parseAvatar(value) {
  if (typeof value !== 'string') return null
  const [iconKey, colourKey] = value.split(':')
  const icon = AVATAR_ICONS.find(i => i.key === iconKey)
  const colour = AVATAR_COLOURS.find(c => c.key === colourKey)
  if (!icon || !colour || value.split(':').length !== 2) return null
  return { icon: icon.key, colour: colour.key, component: icon.icon, hex: colour.hex }
}

export function formatAvatar(icon, colour) {
  return `${icon}:${colour}`
}

/** "nurul.aina@demo.rvm" -> "nu•••••@demo.rvm" (shown in the header, full address in Account info). */
export function maskEmail(email) {
  if (typeof email !== 'string' || !email.includes('@')) return email || ''
  const [name, domain] = email.split('@')
  const keep = name.slice(0, Math.min(2, name.length))
  return `${keep}${'•'.repeat(5)}@${domain}`
}
