<template>
  <div class="not-found">
    <div class="nf-content">
      <div class="nf-icon">♻️</div>
      <h1>404</h1>
      <p>Oops! This page got recycled.</p>
      <RouterLink to="/" class="home-btn">← Go Back Home</RouterLink>
    </div>
  </div>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>
<style scoped>
.not-found { min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--bg-primary); }
.nf-content { text-align:center; }
.nf-icon { font-size:64px; animation:spin 3s linear infinite; margin-bottom:16px; }
h1 { font-size:80px; font-weight:900; color:var(--text-primary); line-height:1; }
p  { color:var(--text-secondary); font-size:16px; margin:12px 0 24px; }
.home-btn { padding:12px 28px; background:var(--accent-blue); color:white; border-radius:8px; text-decoration:none; font-weight:600; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
