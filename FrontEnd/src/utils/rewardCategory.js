// Reward categories are free text typed by the admin in the Reward Catalog
// (live ones are in Malay: E-Dompet, Baucar Makanan, ...). Known ones are
// shown in the chosen language; anything else is shown as typed.
const KNOWN = {
  ewallet: ['e-wallet', 'e-dompet', 'dompet digital', 'ewallet'],
  food: ['food voucher', 'baucar makanan', 'voucher makanan'],
  drink: ['drink voucher', 'baucar minuman', 'voucher minuman'],
  merch: ['merchandise', 'barangan', 'barang'],
  shopping: ['shopping voucher', 'baucar belanja', 'voucher belanja'],
}

export function rewardCategoryKey(raw) {
  const name = String(raw ?? '').trim().toLowerCase().replace(/\s+/g, ' ')
  if (!name) return null
  return Object.keys(KNOWN).find((key) => KNOWN[key].includes(name)) || null
}

export function rewardCategoryLabel(raw, t) {
  const key = rewardCategoryKey(raw)
  return key ? t(`rewards.categories.${key}`) : raw
}

// A reward card's category label: the admin's own translation for this
// language wins (category_en / category_id), then the built-in label for a
// known category, then the category exactly as typed.
export function rewardItemCategoryLabel(item, locale, t) {
  const translated = item?.[`category_${locale}`]
  if (typeof translated === 'string' && translated.trim()) return translated
  return rewardCategoryLabel(item?.category, t)
}
