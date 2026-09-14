<template>
  <footer class="admin-pagination">
    <label class="per-page-control">
      <span>{{ t('admin.pagination.perPage') }}</span>
      <select :value="perPage" @change="changePerPage">
        <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
      </select>
    </label>

    <div class="pagination-right">
      <span class="range-label" aria-live="polite">
        {{ formatNumber(bounds.from) }} – {{ formatNumber(bounds.to) }}
        {{ t('admin.pagination.of') }} {{ formatNumber(total) }}
      </span>

      <nav v-if="total > 0" class="page-buttons" :aria-label="t('admin.pagination.navigation')">
        <button class="page-button arrow" :disabled="currentPage <= 1"
          :aria-label="t('admin.pagination.previous')" @click="changePage(currentPage - 1)">
          <PhCaretLeft weight="bold" aria-hidden="true" />
        </button>

        <template v-for="(item, index) in pageItems" :key="`${item}-${index}`">
          <span v-if="item === 'ellipsis'" class="page-ellipsis" aria-hidden="true">…</span>
          <button v-else :class="['page-button', { active: item === currentPage }]"
            :aria-current="item === currentPage ? 'page' : undefined" @click="changePage(item)">
            {{ item }}
          </button>
        </template>

        <button class="page-button arrow" :disabled="currentPage >= lastPage"
          :aria-label="t('admin.pagination.next')" @click="changePage(currentPage + 1)">
          <PhCaretRight weight="bold" aria-hidden="true" />
        </button>
      </nav>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { PhCaretLeft, PhCaretRight } from '@phosphor-icons/vue'
import { paginationBounds, paginationItems } from '@/utils/admin/paginationLabel.js'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  perPage: { type: Number, required: true },
  total: { type: Number, required: true },
  perPageOptions: { type: Array, default: () => [20, 50, 100, 200] },
})
const emit = defineEmits(['change-page', 'change-per-page'])
const { t, locale } = useI18n()

const bounds = computed(() => paginationBounds(props))
const pageItems = computed(() => paginationItems(props.currentPage, props.lastPage))
const numberFormatter = computed(() => new Intl.NumberFormat(locale.value === 'my' ? 'ms-MY' : 'en-MY'))

function formatNumber(value) {
  return numberFormatter.value.format(value)
}

function changePage(page) {
  if (page < 1 || page > props.lastPage || page === props.currentPage) return
  emit('change-page', page)
}

function changePerPage(event) {
  emit('change-per-page', Number(event.target.value))
}
</script>

<style scoped>
.admin-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 14px;
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid var(--border);
}
.per-page-control,
.pagination-right,
.page-buttons {
  display: flex;
  align-items: center;
}
.per-page-control { gap: 9px; color: var(--text-secondary); font-size: 13px; }
.per-page-control select {
  min-width: 72px;
  min-height: 38px;
  padding: 7px 28px 7px 11px;
  border: 1px solid var(--border);
  border-radius: 9px;
  background: var(--bg-card);
  color: var(--text-primary);
  font: inherit;
  font-weight: 600;
  cursor: pointer;
}
.pagination-right { gap: 14px; margin-left: auto; }
.range-label {
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}
.page-buttons { gap: 4px; }
.page-button {
  min-width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.page-button:hover:not(:disabled):not(.active) {
  border-color: var(--border);
  background: var(--bg-hover);
}
.page-button.active {
  background: rgba(78,110,242,0.14);
  color: var(--accent-blue);
}
.page-button.arrow { border-color: var(--border); background: var(--bg-card); }
.page-button.arrow svg { width: 15px; height: 15px; }
.page-button:disabled { opacity: .42; cursor: not-allowed; }
.page-ellipsis { min-width: 24px; text-align: center; color: var(--text-muted); }

@media (max-width: 700px) {
  .admin-pagination { align-items: flex-start; }
  .pagination-right {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
  }
  .page-buttons { max-width: 100%; overflow-x: auto; }
}
</style>
