// src/utils/voucher.js
import QRCode from 'qrcode'

// The QR on a redeemed reward opens the public check page (VoucherView.vue),
// so whoever scans it sees whether it is still valid.
export function voucherLink(code, origin = window.location.origin, pathname = window.location.pathname) {
  return `${origin}${pathname}#/voucher/${encodeURIComponent(code)}`
}

export function voucherQrDataUrl(code) {
  return QRCode.toDataURL(voucherLink(code), { width: 480, margin: 1, errorCorrectionLevel: 'M' })
}

function loadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => resolve(img)
    img.onerror = reject
    img.src = src
  })
}

// Wraps text to the canvas width — reward names can be long
// ("Voucher Belanja RM10 (Jaya Grocer / 99 Speedmart)").
function wrapLines(ctx, text, maxWidth) {
  const words = String(text).split(/\s+/)
  const lines = []
  let line = ''
  for (const word of words) {
    const next = line ? `${line} ${word}` : word
    if (ctx.measureText(next).width > maxWidth && line) {
      lines.push(line)
      line = word
    } else {
      line = next
    }
  }
  if (line) lines.push(line)
  return lines
}

// One PNG with everything a shop needs (QR, code, reward, date), so the user
// can keep it in their gallery and show it offline.
export async function voucherImageDataUrl({ code, rewardName, dateText, qrDataUrl, title, footer }) {
  const width = 600
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')
  ctx.font = 'bold 30px system-ui, sans-serif'
  const nameLines = wrapLines(ctx, rewardName, width - 80)
  const height = 300 + nameLines.length * 38 + 420
  canvas.width = width
  canvas.height = height

  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, width, height)
  ctx.fillStyle = '#4f6ef7'
  ctx.fillRect(0, 0, width, 90)

  ctx.textAlign = 'center'
  ctx.fillStyle = '#ffffff'
  ctx.font = 'bold 30px system-ui, sans-serif'
  ctx.fillText(title, width / 2, 57)

  ctx.fillStyle = '#111827'
  let y = 145
  for (const line of nameLines) {
    ctx.fillText(line, width / 2, y)
    y += 38
  }

  const qr = await loadImage(qrDataUrl)
  ctx.drawImage(qr, (width - 380) / 2, y, 380, 380)
  y += 420

  ctx.font = 'bold 34px ui-monospace, Menlo, monospace'
  ctx.fillText(code, width / 2, y)
  y += 40
  ctx.font = '20px system-ui, sans-serif'
  ctx.fillStyle = '#6b7280'
  ctx.fillText(dateText, width / 2, y)
  y += 32
  ctx.fillText(footer, width / 2, y)

  return canvas.toDataURL('image/png')
}
