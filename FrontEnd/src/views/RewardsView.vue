<!-- FrontEnd/src/views/RewardsView.vue -->
<template>
  <div class="rewards-page">
    <h1 class="page-title">{{ $t('rewards.title') }}</h1>

    <p v-if="!loading && loadError" class="load-error-notice">{{ $t('rewards.loadError') }}</p>

    <div v-if="categories.length > 1" class="category-chips">
      <button
        v-for="cat in categories" :key="cat"
        :class="['chip', { active: activeCategory === cat }]"
        @click="activeCategory = cat"
      >{{ cat === '__all__' ? $t('rewards.allCategory') : cat }}</button>
    </div>

    <div v-if="loading" class="loading-placeholder"><div class="spinner-sm"></div></div>
    <div v-else-if="!filteredItems.length" class="empty-state"><p>{{ $t('rewards.empty') }}</p></div>
    <div v-else class="reward-grid">
      <div
        v-for="(item, index) in filteredItems" :key="item.id"
        class="reward-card" v-reveal="index"
        @click="detailItem = item; redeemError = ''"
      >
        <img v-if="item.image_url" :src="item.image_url" class="reward-image" alt="" />
        <div v-else class="reward-image reward-image-placeholder">
          <PhGift weight="regular" aria-hidden="true" />
        </div>
        <div class="reward-body">
          <span v-if="item.category" class="reward-category">{{ item.category }}</span>
          <strong class="reward-name">{{ item.name }}</strong>
          <p v-if="item.description" class="reward-desc">{{ item.description }}</p>
          <div class="reward-meta">
            <span class="reward-points">{{ $t('rewards.pointsCost', { points: item.points_cost }) }}</span>
            <span v-if="item.stock !== null" class="reward-stock">{{ $t('rewards.stockLeft', { count: item.stock }) }}</span>
            <span v-else class="reward-stock">{{ $t('rewards.unlimitedStock') }}</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="detailItem" class="modal-overlay" @click.self="detailItem = null">
      <div class="detail-modal">
        <img v-if="detailItem.image_url" :src="detailItem.image_url" class="detail-image" alt="" />
        <div v-else class="detail-image reward-image-placeholder">
          <PhGift weight="regular" aria-hidden="true" />
        </div>
        <div class="detail-body">
          <span v-if="detailItem.category" class="reward-category">{{ detailItem.category }}</span>
          <h3 class="detail-name">{{ detailItem.name }}</h3>
          <p v-if="detailItem.description" class="detail-desc">{{ detailItem.description }}</p>
          <div class="reward-meta">
            <span class="reward-points">{{ $t('rewards.pointsCost', { points: detailItem.points_cost }) }}</span>
            <span v-if="detailItem.stock !== null" class="reward-stock">{{ $t('rewards.stockLeft', { count: detailItem.stock }) }}</span>
            <span v-else class="reward-stock">{{ $t('rewards.unlimitedStock') }}</span>
          </div>
          <p v-if="redeemError" class="redeem-error">{{ redeemError }}</p>
          <div class="confirm-actions">
            <button class="cancel-btn" :disabled="redeemingId === detailItem.id" @click="detailItem = null">{{ $t('rewards.confirmCancel') }}</button>
            <button
              class="redeem-btn"
              :disabled="!detailItem.is_available || (auth.user?.total_points || 0) < detailItem.points_cost || redeemingId === detailItem.id"
              @click="redeem(detailItem)"
            >
              {{ redeemingId === detailItem.id ? $t('rewards.redeeming') : ((auth.user?.total_points || 0) < detailItem.points_cost ? $t('rewards.insufficientPoints') : $t('rewards.redeemBtn')) }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { PhGift } from '@phosphor-icons/vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { useAuthStore } from '@/store/auth'

const { t } = useI18n()
const auth = useAuthStore()
const showToast = inject('showToast')
const items = ref([])
const loading = ref(true)
const loadError = ref(false)
const activeCategory = ref('__all__')
const detailItem = ref(null)
const redeemingId = ref(null)
const redeemError = ref('')

const categories = computed(() => {
  const set = new Set(items.value.map(i => i.category).filter(Boolean))
  return set.size ? ['__all__', ...set] : []
})

const filteredItems = computed(() => {
  if (activeCategory.value === '__all__') return items.value
  return items.value.filter(i => i.category === activeCategory.value)
})

async function fetchItems() {
  loading.value = true
  loadError.value = false
  try {
    const res = await api.get('/user/reward-items')
    items.value = res.data.reward_items || []
  } catch {
    items.value = []
    loadError.value = true
  } finally {
    loading.value = false
  }
}

async function redeem(item) {
  redeemingId.value = item.id
  redeemError.value = ''
  try {
    const res = await api.post(`/user/reward-items/${item.id}/redeem`)
    auth.updatePoints(res.data.total_points)
    detailItem.value = null
    showToast?.(t('rewards.redeemSuccess'))
    await fetchItems()
  } catch (err) {
    redeemError.value = err.response?.data?.message || t('rewards.redeemFailed')
  } finally {
    redeemingId.value = null
  }
}

onMounted(fetchItems)
</script>

<style scoped>
.rewards-page { padding: 20px 16px 32px; max-width: 720px; margin: 0 auto; }
.page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }

.category-chips {
  display: flex; gap: 8px; overflow-x: auto; margin-bottom: 16px; padding-bottom: 4px;
  scrollbar-width: none; -ms-overflow-style: none;
}
.category-chips::-webkit-scrollbar { display: none; }
.chip {
  flex-shrink: 0; padding: 6px 14px; border-radius: 16px; font-size: 13px;
  background: var(--bg-card); border: 1px solid var(--border); color: var(--text-secondary); cursor: pointer;
}
.chip.active { background: var(--accent-blue); color: white; border-color: var(--accent-blue); }

.load-error-notice {
  font-size: 12px; color: var(--accent-yellow);
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 8px 12px; margin-bottom: 12px;
}

.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.spinner-sm { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state {
  text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;
  background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border);
}

.reward-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
.reward-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;
  display: flex; flex-direction: column; cursor: pointer;
}
.reward-image { width: 100%; height: 100px; object-fit: cover; }
.reward-image-placeholder { display: flex; align-items: center; justify-content: center; color: var(--accent-blue); background: var(--bg-hover); }
.reward-image-placeholder :deep(svg) { width: 32px; height: 32px; }
.reward-body { padding: 10px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.reward-category { font-size: 10px; color: var(--accent-blue); text-transform: uppercase; font-weight: 700; }
.reward-name { font-size: 13px; color: var(--text-primary); }
.reward-desc { font-size: 11px; color: var(--text-muted); margin: 0; }
.reward-meta { display: flex; justify-content: space-between; font-size: 11px; color: var(--text-secondary); margin-top: 4px; }
.reward-points { font-weight: 700; color: var(--accent-green); }

.redeem-btn {
  padding: 8px 14px; border: none; border-radius: 8px; font-size: 12px; font-weight: 700;
  background: var(--accent-blue); color: white; cursor: pointer;
}
.redeem-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 200; padding: 16px;
}
.detail-modal {
  background: var(--bg-card); border-radius: var(--radius); overflow: hidden; max-width: 420px; width: 100%;
}
.detail-image { width: 100%; height: 180px; object-fit: cover; }
.detail-body { padding: 20px; display: flex; flex-direction: column; gap: 6px; }
.detail-name { font-size: 17px; color: var(--text-primary); }
.detail-desc { font-size: 13px; color: var(--text-secondary); margin: 0; }
.redeem-error { color: var(--accent-red); font-weight: 600; font-size: 13px; margin: 4px 0 0; }
.confirm-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px; }
.cancel-btn {
  padding: 8px 14px; border-radius: 8px; font-size: 13px; background: var(--bg-hover);
  color: var(--text-primary); border: 1px solid var(--border); cursor: pointer;
}
</style>
