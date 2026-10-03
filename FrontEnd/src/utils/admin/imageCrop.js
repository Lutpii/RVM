// Pure geometry for the reward image cropper (components/admin/ImageCropper.vue).
// Kept free of DOM/canvas access so it can be unit-tested directly.
//
// Model: the photo is drawn inside a fixed 4:3 frame at `coverScale * zoom`,
// its top-left corner at (x, y) in frame pixels (x, y <= 0). The photo must
// always cover the whole frame, so no empty edge ever ends up in the saved
// image.

// Every reward image is saved in this shape, so the Rewards menu looks uniform.
export const CROP_ASPECT = 4 / 3
export const CROP_OUTPUT = { width: 800, height: 600 }
export const MAX_ZOOM = 4

// `img` / `frame` are {width, height}.
export function coverScale(img, frame) {
  return Math.max(frame.width / img.width, frame.height / img.height)
}

export function centeredOffset(img, frame, scale) {
  return {
    x: (frame.width - img.width * scale) / 2,
    y: (frame.height - img.height * scale) / 2,
  }
}

// Pull the photo back so none of its edges is inside the frame.
export function clampOffset(offset, img, frame, scale) {
  const minX = frame.width - img.width * scale
  const minY = frame.height - img.height * scale
  return {
    x: Math.min(0, Math.max(minX, offset.x)),
    y: Math.min(0, Math.max(minY, offset.y)),
  }
}

// `state` is {zoom, x, y}; `anchor` is the frame point (finger or cursor) that
// should stay over the same spot of the photo while zooming.
export function zoomAround(state, zoom, anchor, img, frame) {
  const nextZoom = Math.min(MAX_ZOOM, Math.max(1, zoom))
  const base = coverScale(img, frame)
  const ratio = nextZoom / state.zoom
  const offset = {
    x: anchor.x - (anchor.x - state.x) * ratio,
    y: anchor.y - (anchor.y - state.y) * ratio,
  }
  return { zoom: nextZoom, ...clampOffset(offset, img, frame, base * nextZoom) }
}

// The part of the original photo (in its own pixels) that is inside the frame.
export function sourceRect(img, frame, state) {
  const scale = coverScale(img, frame) * state.zoom
  const sw = Math.min(img.width, frame.width / scale)
  const sh = Math.min(img.height, frame.height / scale)
  return {
    sx: Math.min(img.width - sw, Math.max(0, -state.x / scale)),
    sy: Math.min(img.height - sh, Math.max(0, -state.y / scale)),
    sw,
    sh,
  }
}
