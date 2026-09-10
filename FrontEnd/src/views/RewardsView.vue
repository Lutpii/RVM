<template>
  <div class="rewards-page">
    <h1 class="page-title">{{ $t('appNav.rewards') }}</h1>
    <div class="empty-state">
      <p>{{ $t('rewards.comingSoon') }}</p>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.rewards-page { padding: 20px 16px 32px; max-width: 640px; margin: 0 auto; }
.page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
.empty-state {
  text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;
  background: var(--bg-card); border-radius: var(--radius); border: 1px solid var(--border);
}
</style>
