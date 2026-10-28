<!-- FrontEnd/src/components/VoucherModal.vue -->
<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="voucher-modal">
      <h3 class="voucher-title">{{ fresh ? $t('rewards.voucherReady') : voucher.reward_name }}</h3>
      <p v-if="fresh" class="voucher-reward">{{ voucher.reward_name }}</p>

      <div :class="['qr-box', { used: isUsed }]">
        <img v-if="qrDataUrl" :src="qrDataUrl" class="qr-img" :alt="voucher.voucher_code" />
        <div v-else class="spinner-sm"></div>
        <span v-if="isUsed" class="used-stamp">{{ $t('rewards.statusUsed') }}</span>
      </div>

      <strong class="voucher-code">{{ voucher.voucher_code }}</strong>
      <p class="voucher-date">{{ $t('rewards.redeemedOn', { date: formatDate(voucher.created_at) }) }}</p>
      <p v-if="saveError" class="save-error">{{ saveError }}</p>

      <div class="voucher-actions">
        <button class="cancel-btn" @click="$emit('close')">{{ $t('rewards.close') }}</button>
        <button class="save-btn" :disabled="!qrDataUrl || saving" @click="saveImage">
          <PhDownloadSimple weight="bold" aria-hidden="true" /> {{ $t('rewards.saveImage') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { PhDownloadSimple } from '@phosphor-icons/vue'
import { voucherQrDataUrl, voucherImageDataUrl } from '@/utils/voucher'

const props = defineProps({
  voucher: { type: Object, required: true },
  // true right after redeeming: headline "your voucher is ready" instead of the reward name
  fresh: { type: Boolean, default: false },
})
defineEmits(['close'])

const { t } = useI18n()
const qrDataUrl = ref('')
const saving = ref(false)
const saveError = ref('')
const isUsed = computed(() => !!props.voucher.used_at)

function formatDate(value) {
  const d = new Date(value)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

async function saveImage() {
  saving.value = true
  saveError.value = ''
  try {
    const dataUrl = await voucherImageDataUrl({
      code: props.voucher.voucher_code,
      rewardName: props.voucher.reward_name,
      dateText: t('rewards.redeemedOn', { date: formatDate(props.voucher.created_at) }),
      qrDataUrl: qrDataUrl.value,
      title: t('rewards.voucherImageTitle'),
      footer: t('rewards.voucherImageFooter'),
    })
    const a = document.createElement('a')
    a.href = dataUrl
    a.download = `voucher-${props.voucher.voucher_code}.png`
    document.body.appendChild(a)
    a.click()
    a.remove()
  } catch {
    saveError.value = t('rewards.saveImageFailed')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  qrDataUrl.value = await voucherQrDataUrl(props.voucher.voucher_code)
})
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 210; padding: 16px;
}
.voucher-modal {
  background: var(--bg-card); border-radius: var(--radius); max-width: 360px; width: 100%;
  max-height: calc(100dvh - 32px); overflow-y: auto;
  padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 6px; text-align: center;
}
.voucher-title { font-size: 17px; color: var(--text-primary); }
.voucher-reward { font-size: 14px; color: var(--text-secondary); margin: 0; font-weight: 600; }
/* White behind the QR in both themes — scanners need the contrast. */
.qr-box {
  position: relative; width: 220px; height: 220px; margin: 8px 0; padding: 10px;
  background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center;
}
.qr-box.used .qr-img { opacity: 0.25; }
.qr-img { width: 100%; height: 100%; display: block; }
.used-stamp {
  position: absolute; padding: 6px 14px; border: 2px solid var(--accent-red); border-radius: 8px;
  color: var(--accent-red); font-weight: 800; text-transform: uppercase; background: #fff; transform: rotate(-12deg);
}
.voucher-code { font-family: ui-monospace, Menlo, monospace; font-size: 18px; letter-spacing: 1px; color: var(--text-primary); }
.voucher-date { font-size: 12px; color: var(--text-muted); margin: 0; }
.save-error { color: var(--accent-red); font-weight: 600; font-size: 13px; margin: 4px 0 0; }
.voucher-actions { display: flex; gap: 8px; justify-content: center; margin-top: 12px; width: 100%; }
.cancel-btn {
  padding: 8px 14px; border-radius: 8px; font-size: 13px; background: var(--bg-hover);
  color: var(--text-primary); border: 1px solid var(--border); cursor: pointer;
}
.save-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 14px; border: none; border-radius: 8px; font-size: 13px; font-weight: 700;
  background: var(--accent-blue); color: white; cursor: pointer;
}
.save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.spinner-sm { width: 20px; height: 20px; border: 2px solid #ddd; border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
