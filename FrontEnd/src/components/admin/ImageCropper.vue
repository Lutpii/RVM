<template>
  <!-- The cropping area only: a fixed 4:3 frame plus a zoom slider. Drag to
       move; pinch, scroll or the slider to zoom. The parent supplies the dialog
       and its buttons, and calls exportFile() (via ref) to get the 800x600 JPEG. -->
  <div class="image-cropper">
    <p class="cropper-hint">{{ t('admin.imageCropper.hint') }}</p>

    <div ref="frameEl" class="cropper-frame"
      @pointerdown="onPointerDown" @pointermove="onPointerMove"
      @pointerup="onPointerEnd" @pointercancel="onPointerEnd" @wheel.prevent="onWheel">
      <img v-if="imageUrl && natural" :src="imageUrl" class="cropper-image" alt="" draggable="false"
        :style="imageStyle" />
      <div v-else-if="!loadError" class="cropper-loading"><div class="cropper-spinner"></div></div>
      <p v-if="loadError" class="cropper-error">{{ t('admin.imageCropper.loadFailed') }}</p>
    </div>

    <label class="cropper-zoom">
      <PhMagnifyingGlassMinus weight="regular" aria-hidden="true" />
      <input type="range" min="1" :max="MAX_ZOOM" step="0.01" :value="view.zoom"
        :aria-label="t('admin.imageCropper.zoom')" :disabled="!natural" @input="onSlider" />
      <PhMagnifyingGlassPlus weight="regular" aria-hidden="true" />
    </label>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { PhMagnifyingGlassMinus, PhMagnifyingGlassPlus } from '@phosphor-icons/vue'
import {
  CROP_OUTPUT, MAX_ZOOM, centeredOffset, clampOffset, coverScale, sourceRect, zoomAround,
} from '@/utils/admin/imageCrop.js'

// `source` is the picked File, or the URL of the reward's current image.
const props = defineProps({ source: { type: [File, Blob, String], required: true } })
const { t } = useI18n()

const frameEl = ref(null)
const imageUrl = ref('')   // object URL of the photo being cropped
const natural = ref(null)  // {width, height} of the photo
const frame = reactive({ width: 0, height: 0 })
const view = reactive({ zoom: 1, x: 0, y: 0 })
const loadError = ref(false)
let imageEl = null
let resizeObserver = null

const scale = computed(() => (natural.value && frame.width ? coverScale(natural.value, frame) * view.zoom : 0))
const imageStyle = computed(() => ({
  width: `${natural.value.width * scale.value}px`,
  height: `${natural.value.height * scale.value}px`,
  transform: `translate(${view.x}px, ${view.y}px)`,
}))

function setView(next) {
  Object.assign(view, next)
}

function reset() {
  if (!natural.value || !frame.width) return
  setView({ zoom: 1, ...centeredOffset(natural.value, frame, coverScale(natural.value, frame)) })
}

function measureFrame() {
  const rect = frameEl.value?.getBoundingClientRect()
  if (!rect?.width) return
  const ratio = frame.width ? rect.width / frame.width : 0
  frame.width = rect.width
  frame.height = rect.height
  // Same zoom, same part of the photo: offsets are in frame pixels, so scale them.
  if (ratio && natural.value) setView({ x: view.x * ratio, y: view.y * ratio })
  else reset()
}

async function load() {
  try {
    // An existing image is fetched into a Blob first so the canvas export below
    // is never blocked as cross-origin. Fails cleanly if the server refuses.
    let blob = props.source
    if (typeof blob === 'string') {
      const res = await fetch(blob, { credentials: 'same-origin' })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      blob = await res.blob()
    }
    imageUrl.value = URL.createObjectURL(blob)
    imageEl = new Image()
    imageEl.src = imageUrl.value
    await imageEl.decode()
    natural.value = { width: imageEl.naturalWidth, height: imageEl.naturalHeight }
    reset()
  } catch {
    loadError.value = true
  }
}

// ── Drag (one finger / mouse) and pinch (two fingers) ──
const pointers = new Map() // pointerId -> {x, y} in frame pixels
let gesture = null         // what the current gesture started from

function framePoint(e) {
  const rect = frameEl.value.getBoundingClientRect()
  return { x: e.clientX - rect.left, y: e.clientY - rect.top }
}

function startGesture() {
  const pts = [...pointers.values()]
  if (pts.length === 1) {
    gesture = { type: 'drag', from: pts[0], x: view.x, y: view.y }
  } else if (pts.length >= 2) {
    const [a, b] = pts
    gesture = { type: 'pinch', distance: Math.hypot(a.x - b.x, a.y - b.y) || 1, zoom: view.zoom }
  } else {
    gesture = null
  }
}

function onPointerDown(e) {
  if (!natural.value) return
  frameEl.value.setPointerCapture?.(e.pointerId)
  pointers.set(e.pointerId, framePoint(e))
  startGesture()
}

function onPointerMove(e) {
  if (!pointers.has(e.pointerId) || !gesture) return
  pointers.set(e.pointerId, framePoint(e))
  const pts = [...pointers.values()]
  if (gesture.type === 'drag') {
    const offset = { x: gesture.x + pts[0].x - gesture.from.x, y: gesture.y + pts[0].y - gesture.from.y }
    setView(clampOffset(offset, natural.value, frame, scale.value))
  } else if (pts.length >= 2) {
    const [a, b] = pts
    const middle = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 }
    const zoom = gesture.zoom * Math.hypot(a.x - b.x, a.y - b.y) / gesture.distance
    setView(zoomAround(view, zoom, middle, natural.value, frame))
  }
}

function onPointerEnd(e) {
  pointers.delete(e.pointerId)
  startGesture() // lifting one finger of a pinch continues as a drag
}

function onWheel(e) {
  if (!natural.value) return
  setView(zoomAround(view, view.zoom * Math.exp(-e.deltaY * 0.0015), framePoint(e), natural.value, frame))
}

function onSlider(e) {
  const middle = { x: frame.width / 2, y: frame.height / 2 }
  setView(zoomAround(view, Number(e.target.value), middle, natural.value, frame))
}

// ── Export the framed part as an 800x600 JPEG ──
function baseName() {
  const name = typeof props.source === 'string' ? props.source.split('/').pop() : props.source.name
  return (name || 'reward').replace(/\.[^.]+$/, '') || 'reward'
}

// Resolves to the File, or null if the photo never loaded or the export failed.
async function exportFile() {
  if (!natural.value) return null
  const canvas = document.createElement('canvas')
  canvas.width = CROP_OUTPUT.width
  canvas.height = CROP_OUTPUT.height
  const ctx = canvas.getContext('2d')
  ctx.fillStyle = '#ffffff' // transparent PNG areas become white, not black
  ctx.fillRect(0, 0, canvas.width, canvas.height)
  ctx.imageSmoothingQuality = 'high'
  const { sx, sy, sw, sh } = sourceRect(natural.value, frame, view)
  ctx.drawImage(imageEl, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height)
  const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9))
  return blob ? new File([blob], `${baseName()}-4x3.jpg`, { type: 'image/jpeg' }) : null
}

defineExpose({ exportFile, ready: computed(() => Boolean(natural.value)) })

onMounted(() => {
  measureFrame()
  resizeObserver = new ResizeObserver(measureFrame)
  resizeObserver.observe(frameEl.value)
  load()
})

onBeforeUnmount(() => {
  resizeObserver?.disconnect()
  if (imageUrl.value) URL.revokeObjectURL(imageUrl.value)
})
</script>

<style scoped>
/* Right padding keeps the text clear of the dialog's close button on phones. */
.cropper-hint { margin: -10px 0 12px; padding-right: 40px; font-size: 12px; color: var(--text-muted); }
.cropper-frame {
  position: relative; width: 100%; aspect-ratio: 4 / 3; overflow: hidden;
  border-radius: 10px; background: var(--bg-hover); border: 1px solid var(--border);
  cursor: grab; touch-action: none; user-select: none;
}
.cropper-frame:active { cursor: grabbing; }
.cropper-image {
  position: absolute; top: 0; left: 0; max-width: none; transform-origin: 0 0;
  pointer-events: none; will-change: transform;
  background: #ffffff; /* transparent PNG areas are saved white, so show them white here too */
}
.cropper-loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.cropper-spinner {
  width: 32px; height: 32px; border: 3px solid var(--border); border-top-color: var(--accent-blue);
  border-radius: 50%; animation: cropper-spin 0.8s linear infinite;
}
@keyframes cropper-spin { to { transform: rotate(360deg); } }
.cropper-error {
  position: absolute; inset: 0; margin: 0; padding: 16px; display: flex; align-items: center; justify-content: center;
  text-align: center; font-size: 13px; color: var(--accent-red);
}
.cropper-zoom { display: flex; align-items: center; gap: 10px; margin: 14px 0 0; color: var(--text-muted); }
.cropper-zoom input { flex: 1; accent-color: var(--accent-green); }
.cropper-zoom :deep(svg) { width: 18px; height: 18px; flex-shrink: 0; }
</style>
