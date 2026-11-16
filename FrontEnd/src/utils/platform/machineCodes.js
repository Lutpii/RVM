// Mirrors AdminController::TYPE_CODE_SUFFIX: one Add Machine form with several types
// creates one machine per type, the code gets "-RVM" / "-CMP" / "-CPS".
const SUFFIX = { rvm: 'RVM', compactor: 'CMP', composter: 'CPS' }

export function plannedCodes(code, types) {
  if (types.length <= 1) return types.length ? [code] : []
  return types.map((type) => `${code}-${SUFFIX[type]}`)
}
