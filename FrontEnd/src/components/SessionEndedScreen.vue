<template>
  <div class="session-ended-screen" role="status">
    <h2>{{ $t('kiosk.sessionEnded') }}</h2>
  </div>
</template>

<style scoped>
.session-ended-screen {
  position: fixed;
  inset: 0;
  z-index: 10001;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  color: var(--text-primary);
}
.session-ended-screen h2 { font-size: 2.5rem; margin: 0; }
</style>
