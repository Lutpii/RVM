import { describe, it, expect, vi } from 'vitest'
import { cleanParams, excelFilename, downloadExcel } from './excelExport.js'

describe('cleanParams', () => {
  it('drops empty filters but keeps zero and false', () => {
    expect(cleanParams({ search: '', status: 'rejected', machine_id: null, date_from: undefined, page: 0, flag: false }))
      .toEqual({ status: 'rejected', page: 0, flag: false })
  })
})

describe('excelFilename', () => {
  it('uses the local date', () => {
    expect(excelFilename('rvm_transactions', new Date(2026, 10, 11, 23, 30))).toBe('rvm_transactions_2026-11-11.xlsx')
  })
})

describe('downloadExcel', () => {
  it('asks the export endpoint for a blob with the active filters only', async () => {
    const api = { get: vi.fn().mockResolvedValue({ data: new Blob(['x']) }) }
    const link = { click: vi.fn() }
    vi.stubGlobal('document', {
      createElement: vi.fn(() => link),
      body: { appendChild: vi.fn(), removeChild: vi.fn() },
    })
    vi.spyOn(URL, 'createObjectURL').mockReturnValue('blob:x')
    vi.spyOn(URL, 'revokeObjectURL').mockImplementation(() => {})

    await downloadExcel(api, '/admin/transactions/export', { status: 'rejected', search: '' }, 'rvm_transactions')

    expect(api.get).toHaveBeenCalledWith('/admin/transactions/export', { params: { status: 'rejected' }, responseType: 'blob' })
    expect(link.click).toHaveBeenCalled()
    expect(link.download).toMatch(/^rvm_transactions_\d{4}-\d{2}-\d{2}\.xlsx$/)
    vi.unstubAllGlobals()
    vi.restoreAllMocks()
  })
})
