<template>
  <div class="brand-logo-row">
    <img src="@/assets/logo-umpsa-trimmed.png" class="brand-logo-img" alt="UMPSA" />
    <img src="@/assets/dsme-logo.png" class="brand-logo-img" alt="DSME" />
  </div>
</template>

<script setup>
// Both source files are pre-trimmed to their own visual content (no internal
// padding) — see FrontEnd/src/assets/logo-umpsa-trimmed.png — so a single
// shared height keeps the two logos reading as the same size instead of one
// looking smaller because its source canvas had more built-in whitespace.
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
