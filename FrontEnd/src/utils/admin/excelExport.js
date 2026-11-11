// Downloads one admin table as .xlsx from its /admin/<table>/export endpoint.
// Empty filter values are dropped so the backend sees "no filter" for them.

export function cleanParams(params = {}) {
  return Object.fromEntries(
    Object.entries(params).filter(([, v]) => v !== undefined && v !== null && v !== ''),
  )
}

export function excelFilename(prefix, now = new Date()) {
  const pad = n => String(n).padStart(2, '0')
  return `${prefix}_${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}.xlsx`
}

export async function downloadExcel(api, path, params, filenamePrefix) {
  const res = await api.get(path, { params: cleanParams(params), responseType: 'blob' })
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a')
  a.href = url
  a.download = excelFilename(filenamePrefix)
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}
