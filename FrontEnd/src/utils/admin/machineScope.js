// Admin data switch applied to the machine list: the Machines count and the
// Machine status card follow the switch (all / real only / demo only).
export function machinesInScope(machines, scope) {
  const list = Array.isArray(machines) ? machines : []
  if (scope === 'demo') return list.filter((m) => m.is_demo)
  if (scope === 'real') return list.filter((m) => !m.is_demo)
  return list
}

export function countMachinesInScope(machines, scope) {
  return machinesInScope(machines, scope).length
}
