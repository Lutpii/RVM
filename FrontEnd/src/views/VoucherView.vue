<!-- FrontEnd/src/views/VoucherView.vue -->
<template>
  <div class="voucher-page">
    <div class="voucher-card">
      <div class="voucher-header">
        <h1>{{ $t('app.name') }}</h1>
        <p>{{ $t('voucher.title') }}</p>
      </div>

      <div class="voucher-body">
        <div v-if="loading" class="center"><div class="spinner-sm"></div></div>
        <p v-else-if="error" class="error-msg">{{ error }}</p>
        <template v-else-if="voucher">
          <div :class="['status-badge', voucher.status]">
            <PhCheckCircle v-if="voucher.status === 'valid'" weight="fill" aria-hidden="true" />
            <PhXCircle v-else weight="fill" aria-hidden="true" />
            {{ voucher.status === 'valid' ? $t('voucher.statusValid') : $t('voucher.statusUsed') }}
          </div>

          <strong class="voucher-code">{{ voucher.code }}</strong>

          <dl class="details">
            <dt>{{ $t('voucher.reward') }}</dt><dd>{{ voucher.reward_name }}</dd>
            <dt>{{ $t('voucher.holder') }}</dt><dd>{{ voucher.holder }}</dd>
            <dt>{{ $t('voucher.redeemedAt') }}</dt><dd>{{ formatDate(voucher.redeemed_at) }}</dd>
            <template v-if="voucher.used_at">
              <dt>{{ $t('voucher.usedAt') }}</dt><dd>{{ formatDate(voucher.used_at) }}</dd>
            </template>
          </dl>

          <template v-if="voucher.status === 'valid'">
            <button v-if="auth.isAdmin" class="use-btn" :disabled="marking" @click="markUsed">
              {{ marking ? $t('voucher.marking') : $t('voucher.markUsed') }}
            </button>
            <p v-else class="hint">{{ $t('voucher.adminHint') }}</p>
          </template>
          <p v-if="markError" class="error-msg">{{ markError }}</p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { PhCheckCircle, PhXCircle } from '@phosphor-icons/vue'
import api from '@/services/api'
import { useAuthStore } from '@/store/auth'

const route = useRoute()
const auth = useAuthStore()
const { t } = useI18n()
const voucher = ref(null)
const loading = ref(true)
const error = ref('')
const marking = ref(false)
const markError = ref('')

function formatDate(value) {
  const d = new Date(value)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/vouchers/${encodeURIComponent(route.params.code)}`)
    voucher.value = res.data.voucher
  } catch (err) {
    error.value = err.response?.status === 404 ? t('voucher.notFound') : t('voucher.loadError')
  } finally {
    loading.value = false
  }
}

async function markUsed() {
  marking.value = true
  markError.value = ''
  try {
    const res = await api.post(`/admin/vouchers/${encodeURIComponent(voucher.value.code)}/use`)
    voucher.value = res.data.voucher
  } catch (err) {
    // 409 = someone already marked it; show the fresh "used" state instead of an error
    if (err.response?.status === 409 && err.response.data?.voucher) {
      voucher.value = err.response.data.voucher
    } else {
      markError.value = err.response?.data?.message || t('voucher.markFailed')
    }
  } finally {
    marking.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.voucher-page { min-height: 100dvh; display: flex; align-items: center; justify-content: center; padding: 16px; }
.voucher-card {
  width: 100%; max-width: 400px; background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);
}
.voucher-header {
  padding: 22px 20px; text-align: center; color: white;
  background: linear-gradient(135deg, var(--accent-blue), #a855f7);
}
.voucher-header h1 { font-size: 20px; }
.voucher-header p { font-size: 13px; opacity: 0.9; margin-top: 4px; }
.voucher-body { padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.center { display: flex; justify-content: center; padding: 12px; }
.status-badge {
  display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 999px;
  font-weight: 700; font-size: 14px;
}
.status-badge :deep(svg) { width: 20px; height: 20px; }
.status-badge.valid { color: var(--accent-green); background: color-mix(in srgb, var(--accent-green) 14%, transparent); }
.status-badge.used { color: var(--accent-red); background: color-mix(in srgb, var(--accent-red) 14%, transparent); }
.voucher-code { font-family: ui-monospace, Menlo, monospace; font-size: 20px; letter-spacing: 1px; color: var(--text-primary); }
.details { width: 100%; display: grid; grid-template-columns: auto 1fr; gap: 8px 16px; font-size: 14px; margin: 0; }
.details dt { color: var(--text-muted); }
.details dd { color: var(--text-primary); margin: 0; text-align: right; overflow-wrap: anywhere; }
.use-btn {
  width: 100%; padding: 12px; border: none; border-radius: var(--radius); font-size: 15px; font-weight: 700;
  background: var(--accent-blue); color: white; cursor: pointer;
}
.use-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.hint { font-size: 12px; color: var(--text-muted); text-align: center; margin: 0; }
.error-msg { color: var(--accent-red); font-weight: 600; font-size: 13px; text-align: center; margin: 0; }
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
