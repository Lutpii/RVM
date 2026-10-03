import { describe, expect, it } from 'vitest'
import {
  CROP_ASPECT, CROP_OUTPUT, MAX_ZOOM,
  coverScale, centeredOffset, clampOffset, zoomAround, sourceRect,
} from './imageCrop.js'

const frame = { width: 400, height: 300 } // 4:3 frame on screen
const tall = { width: 600, height: 1200 }  // portrait photo (a bottle)
const wide = { width: 2000, height: 500 }  // panorama banner

describe('crop constants', () => {
  it('saves 4:3 images at 800x600', () => {
    expect(CROP_ASPECT).toBeCloseTo(4 / 3)
    expect(CROP_OUTPUT).toEqual({ width: 800, height: 600 })
    expect(CROP_OUTPUT.width / CROP_OUTPUT.height).toBeCloseTo(CROP_ASPECT)
  })
})

describe('coverScale', () => {
  it('is the smallest scale at which the image still covers the whole frame', () => {
    expect(coverScale(tall, frame)).toBeCloseTo(400 / 600) // width is the tight side
    expect(coverScale(wide, frame)).toBeCloseTo(300 / 500) // height is the tight side
  })
})

describe('centeredOffset', () => {
  it('puts the middle of the image in the middle of the frame', () => {
    const s = coverScale(tall, frame) // image shown 400 x 800
    expect(centeredOffset(tall, frame, s)).toEqual({ x: 0, y: -250 })
  })
})

describe('clampOffset', () => {
  const s = coverScale(tall, frame) // 400 x 800 on screen

  it('never lets a gap open at any edge of the frame', () => {
    expect(clampOffset({ x: 50, y: 40 }, tall, frame, s)).toEqual({ x: 0, y: 0 })
    expect(clampOffset({ x: -50, y: -900 }, tall, frame, s)).toEqual({ x: 0, y: -500 })
  })

  it('keeps an offset that is already inside the limits', () => {
    expect(clampOffset({ x: 0, y: -123 }, tall, frame, s)).toEqual({ x: 0, y: -123 })
  })
})

describe('zoomAround', () => {
  it('keeps the point under the finger/cursor in place while zooming', () => {
    const start = { zoom: 1, ...centeredOffset(tall, frame, coverScale(tall, frame)) }
    const anchor = { x: 200, y: 150 } // middle of the frame
    const next = zoomAround(start, 2, anchor, tall, frame)
    expect(next.zoom).toBe(2)
    // the image point that was under the anchor is still under it
    const before = (anchor.y - start.y) / coverScale(tall, frame)
    const after = (anchor.y - next.y) / (coverScale(tall, frame) * 2)
    expect(after).toBeCloseTo(before)
  })

  it('limits zoom to 1x..MAX_ZOOM and stays clamped', () => {
    const start = { zoom: 1, x: 0, y: 0 }
    expect(zoomAround(start, 0.3, { x: 0, y: 0 }, wide, frame).zoom).toBe(1)
    const max = zoomAround(start, 99, { x: 400, y: 300 }, wide, frame)
    expect(max.zoom).toBe(MAX_ZOOM)
    const s = coverScale(wide, frame) * MAX_ZOOM
    expect(max).toEqual({ zoom: MAX_ZOOM, ...clampOffset(max, wide, frame, s) })
  })
})

describe('sourceRect', () => {
  it('is the part of the original image that sits inside the frame', () => {
    // shown at 2/3 scale: the 400x300 frame covers 600x450 source px, 250/(2/3) = 375 down
    expect(sourceRect(tall, frame, { zoom: 1, x: 0, y: -250 })).toEqual({ sx: 0, sy: 375, sw: 600, sh: 450 })
  })

  it('always has the 4:3 shape of the frame, also when zoomed in', () => {
    const r = sourceRect(wide, frame, { zoom: 2.5, x: -700, y: -100 })
    expect(r.sw / r.sh).toBeCloseTo(4 / 3)
    expect(r.sx).toBeGreaterThanOrEqual(0)
    expect(r.sy).toBeGreaterThanOrEqual(0)
    expect(r.sx + r.sw).toBeLessThanOrEqual(wide.width + 1e-9)
    expect(r.sy + r.sh).toBeLessThanOrEqual(wide.height + 1e-9)
  })
})
