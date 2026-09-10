<!-- FrontEnd/src/views/RewardsView.vue -->
<template>
  <div class="rewards-page">
    <h1 class="page-title">{{ $t('rewards.title') }}</h1>

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
      <div v-for="item in filteredItems" :key="item.id" class="reward-card">
        <img v-if="item.image_url" :src="item.image_url" class="reward-image" alt="" />
        <div v-else class="reward-image reward-image-placeholder">🎁</div>
        <div class="reward-body">
          <span v-if="item.category" class="reward-category">{{ item.category }}</span>
          <strong class="reward-name">{{ item.name }}</strong>
          <p v-if="item.description" class="reward-desc">{{ item.description }}</p>
          <div class="reward-meta">
            <span class="reward-points">{{ $t('rewards.pointsCost', { points: item.points_cost }) }}</span>
            <span v-if="item.stock !== null" class="reward-stock">{{ $t('rewards.stockLeft', { count: item.stock }) }}</span>
            <span v-else class="reward-stock">{{ $t('rewards.unlimitedStock') }}</span>
          </div>
          <button
            class="redeem-btn"
            :disabled="!item.is_available || auth.user.total_points < item.points_cost || redeemingId === item.id"
            @click="confirmingItem = item"
          >
            {{ redeemingId === item.id ? $t('rewards.redeeming') : (auth.user.total_points < item.points_cost ? $t('rewards.insufficientPoints') : $t('rewards.redeemBtn')) }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="confirmingItem" class="modal-overlay" @click.self="confirmingItem = null">
      <div class="confirm-modal">
        <h3>{{ $t('rewards.confirmTitle') }}</h3>
        <p>{{ $t('rewards.confirmBody', { points: confirmingItem.points_cost }) }}</p>
        <div class="confirm-actions">
          <button class="cancel-btn" @click="confirmingItem = null">{{ $t('rewards.confirmCancel') }}</button>
          <button class="redeem-btn" @click="redeem(confirmingItem)">{{ $t('rewards.confirmYes') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/store/auth'

const auth = useAuthStore()
const items = ref([])
const loading = ref(true)
const activeCategory = ref('__all__')
const confirmingItem = ref(null)
const redeemingId = ref(null)

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
  try {
    const res = await api.get('/user/reward-items')
    items.value = res.data.reward_items || []
  } catch {
    items.value = []
  } finally {
    loading.value = false
  }
}

async function redeem(item) {
  redeemingId.value = item.id
  try {
    const res = await api.post(`/user/reward-items/${item.id}/redeem`)
    auth.updatePoints(res.data.total_points)
    confirmingItem.value = null
    await fetchItems()
  } catch {
    confirmingItem.value = null
  } finally {
    redeemingId.value = null
  }
}

onMounted(fetchItems)
</script>

<style scoped>
.rewards-page { padding: 20px 16px 32px; max-width: 720px; margin: 0 auto; }
.page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }

.category-chips { display: flex; gap: 8px; overflow-x: auto; margin-bottom: 16px; padding-bottom: 4px; }
.chip {
  flex-shrink: 0; padding: 6px 14px; border-radius: 16px; font-size: 13px;
  background: var(--bg-card); border: 1px solid var(--border); color: var(--text-secondary); cursor: pointer;
}
.chip.active { background: var(--accent-blue); color: white; border-color: var(--accent-blue); }

.loading-placeholder { display: flex; justify-content: center; padding: 24px; }
.empty-state {
  text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;
  background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border);
}

.reward-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
.reward-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; display: flex; flex-direction: column; }
.reward-image { width: 100%; height: 100px; object-fit: cover; }
.reward-image-placeholder { display: flex; align-items: center; justify-content: center; font-size: 32px; background: var(--bg-hover); }
.reward-body { padding: 10px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.reward-category { font-size: 10px; color: var(--accent-blue); text-transform: uppercase; font-weight: 700; }
.reward-name { font-size: 13px; color: var(--text-primary); }
.reward-desc { font-size: 11px; color: var(--text-muted); margin: 0; }
.reward-meta { display: flex; justify-content: space-between; font-size: 11px; color: var(--text-secondary); margin-top: 4px; }
.reward-points { font-weight: 700; color: var(--accent-green); }

.redeem-btn {
  margin-top: 8px; padding: 8px; border: none; border-radius: 8px; font-size: 12px; font-weight: 700;
  background: var(--accent-blue); color: white; cursor: pointer;
}
.redeem-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 200; padding: 16px;
}
.confirm-modal {
  background: var(--bg-card); border-radius: var(--radius); padding: 20px; max-width: 360px; width: 100%;
}
.confirm-modal h3 { color: var(--text-primary); margin-bottom: 8px; }
.confirm-modal p { color: var(--text-secondary); font-size: 13px; margin-bottom: 16px; }
.confirm-actions { display: flex; gap: 8px; justify-content: flex-end; }
.cancel-btn {
  padding: 8px 14px; border-radius: 8px; font-size: 13px; background: var(--bg-hover);
  color: var(--text-primary); border: 1px solid var(--border); cursor: pointer;
}
</style>
