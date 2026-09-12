<!-- FrontEnd/src/components/AppNav.vue -->
<template>
  <nav class="app-nav">
    <div class="nav-group" :class="{ 'nav-group-active': leftActive }">
      <div class="nav-strip" aria-hidden="true"></div>
      <RouterLink
        v-for="item in leftItems"
        :key="item.key"
        :to="{ name: item.routeName }"
        :class="['nav-item', activeKey === item.key ? 'nav-item-active' : '']"
      >
        <span class="nav-icon">
          <component :is="item.icon" aria-hidden="true" />
        </span>
        <span class="nav-label">{{ $t(item.labelKey) }}</span>
      </RouterLink>
    </div>

    <RouterLink
      :to="{ name: centerItem.routeName }"
      :class="['nav-item', 'nav-item-center', activeKey === centerItem.key ? 'nav-item-active' : '']"
    >
      <span class="nav-icon">
        <component :is="centerItem.icon" aria-hidden="true" />
      </span>
      <span class="nav-label">{{ $t(centerItem.labelKey) }}</span>
    </RouterLink>

    <div class="nav-group" :class="{ 'nav-group-active': rightActive }">
      <div class="nav-strip" aria-hidden="true"></div>
      <RouterLink
        v-for="item in rightItems"
        :key="item.key"
        :to="{ name: item.routeName }"
        :class="['nav-item', activeKey === item.key ? 'nav-item-active' : '']"
      >
        <span class="nav-icon">
          <component :is="item.icon" aria-hidden="true" />
        </span>
        <span class="nav-label">{{ $t(item.labelKey) }}</span>
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { NAV_ITEMS, resolveActiveNavKey } from '@/utils/navItems'

const route = useRoute()
const activeKey = computed(() => resolveActiveNavKey(route.name))

// Split around the center (Scan) item so it can keep its own permanent
// raised-circle treatment while the two side groups each get an
// independent colored "strip" behind whichever of their 2 tabs is active.
const centerIndex = NAV_ITEMS.findIndex((item) => item.isCenter)
const leftItems   = NAV_ITEMS.slice(0, centerIndex)
const centerItem  = NAV_ITEMS[centerIndex]
const rightItems  = NAV_ITEMS.slice(centerIndex + 1)

const leftActive  = computed(() => leftItems.some((item) => item.key === activeKey.value))
const rightActive = computed(() => rightItems.some((item) => item.key === activeKey.value))
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
/* Each side group (2 tabs) hosts its own colored "strip" behind its icon
   row, so the strip never has to slide across the fixed center Scan slot. */
.nav-group {
  position: relative;
  display: flex;
  flex: 2 1 0;
}
.nav-strip {
  position: absolute;
  top: 0; left: 6px; right: 6px;
  height: 30px;
  border-radius: 15px;
  background: var(--accent-blue);
  opacity: 0;
  transform: scale(0.85);
  transition: opacity 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              transform 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  pointer-events: none;
}
.nav-group-active .nav-strip { opacity: 1; transform: scale(1); }

.nav-item {
  position: relative;
  z-index: 1;
  display: flex; flex-direction: column; align-items: center; gap: 2px;
  flex: 1;
  text-decoration: none;
  color: var(--text-muted);
  font-size: 10px;
  padding: 4px 2px;
  transition: color 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.nav-item-active { color: var(--accent-blue); }
/* Inactive tab sharing an active strip needs a light color to stay legible
   against the blue background instead of the default muted gray. */
.nav-group-active .nav-item:not(.nav-item-active) { color: rgba(255,255,255,0.85); }

.nav-icon {
  font-size: 20px; line-height: 0;
  border-radius: 50%;
  transition: transform 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              background-color 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              box-shadow 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
/* The "notch" — the active icon pokes up out of the strip inside a small
   circle in the nav's own background color, rather than an actual cut-out
   mask (simpler, and doesn't depend on clip-path/mask browser support). */
.nav-group-active .nav-item-active .nav-icon {
  background: var(--bg-secondary);
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  transform: translateY(-4px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.18);
}

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
  /* The colored strip + notch is a mobile bottom-nav pattern (that's what it
     was designed and asked for) — on the desktop top-bar layout, items sit
     in a row with wide gaps between them, so a strip spanning a whole group
     would stretch across that empty gap instead of hugging its 2 icons.
     Simplest correct fix: fall back to the original color-only active state
     here, unchanged from before this feature existed. */
  .nav-group { flex: 0 0 auto; gap: 40px; }
  .nav-strip { display: none; }
  .nav-group-active .nav-item:not(.nav-item-active) { color: var(--text-muted); }
  .nav-item { flex: 0 0 auto; flex-direction: row; gap: 6px; font-size: 13px; }
  .nav-group-active .nav-item-active .nav-icon {
    background: none; width: auto; height: auto; transform: none; box-shadow: none;
  }
  .nav-item-center .nav-icon {
    margin-top: 0; width: 34px; height: 34px; font-size: 16px; border-width: 0;
    box-shadow: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .nav-strip, .nav-item, .nav-icon { transition: none; }
}
</style>
