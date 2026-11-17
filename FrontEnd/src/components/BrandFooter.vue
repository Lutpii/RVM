<template>
  <div class="brand-logo-row">
    <img src="@/assets/dsme-logo.png" class="brand-logo-img" alt="DSME" width="600" height="216" />
  </div>
</template>

<script setup>
// DSME branding only (the web is exhibited on behalf of DSME Engineering).
// Callers control the actual height by setting --brand-logo-height on an
// ancestor (it's an inherited custom property, so it reaches in here across
// the component boundary); 96px only applies with no ancestor override.
</script>

<style scoped>
.brand-logo-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: clamp(10px, 4vw, 22px);
}

.brand-logo-img {
  height: var(--brand-logo-height, 96px);
  width: auto;
  opacity: 0.85;
}
</style>
