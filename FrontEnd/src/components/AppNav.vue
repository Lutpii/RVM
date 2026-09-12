<!-- FrontEnd/src/components/AppNav.vue -->
<template>
  <nav class="app-nav">
    <RouterLink
      v-for="item in NAV_ITEMS"
      :key="item.key"
      :to="{ name: item.routeName }"
      :class="['nav-item', item.isCenter ? 'nav-item-center' : '', activeKey === item.key ? 'nav-item-active' : '']"
    >
      <span class="nav-icon">
        <component :is="item.icon" aria-hidden="true" />
      </span>
      <span class="nav-label">{{ $t(item.labelKey) }}</span>
    </RouterLink>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { NAV_ITEMS, resolveActiveNavKey } from '@/utils/navItems'

const route = useRoute()
const activeKey = computed(() => resolveActiveNavKey(route.name))
</script>

<style scoped>
.app-nav {
  display: flex;
  align-items: center;
  justify-content: space-around;
  background: var(--bg-secondary);
  border-top: 1px solid var(--border);
  position: fixed;
  bottom: 0; left: 0; right: 0;
  padding: 8px 4px calc(8px + env(safe-area-inset-bottom));
  z-index: 1000;
}
.nav-item {
  display: flex; flex-direction: column; align-items: center; gap: 2px;
  flex: 1;
  text-decoration: none;
  color: var(--text-muted);
  font-size: 10px;
  padding: 4px 2px;
}
.nav-item-active { color: var(--accent-blue); }
.nav-icon { font-size: 20px; line-height: 0; }

/* Raised center Scan button — see spec §4 and the three finance-app references */
.nav-item-center .nav-icon {
  background: var(--accent-blue);
  color: white;
  width: 52px; height: 52px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px;
  margin-top: -28px;
  box-shadow: 0 4px 12px rgba(78,110,242,0.5);
  border: 4px solid var(--bg-primary);
}
.nav-item-center.nav-item-active .nav-icon { background: var(--accent-purple); }

/* Desktop (≥769px, complementing the project's existing 768px "large mobile"
   cutoff in AdminView.vue): flatten into a top bar instead of a fixed bottom one */
@media (min-width: 769px) {
  .app-nav {
    position: sticky;
    top: 0; bottom: auto;
    border-top: none;
    border-bottom: 1px solid var(--border);
    justify-content: center;
    gap: 40px;
    padding: 12px 16px;
  }
  .nav-item { flex: 0 0 auto; flex-direction: row; gap: 6px; font-size: 13px; }
  .nav-item-center .nav-icon {
    margin-top: 0; width: 34px; height: 34px; font-size: 16px; border-width: 0;
    box-shadow: none;
  }
}
</style>
