<template>
  <main class="mnt" :data-theme="theme">
    <div class="mnt-lang">
      <LanguagePill :languages="LANGUAGES" :current="lang" tone="light" @select="setLang" />
    </div>

    <section class="mnt-card">
      <div class="mnt-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14.7 6.3a4 4 0 0 0-5.4 5.2L3.6 17.2a1.9 1.9 0 0 0 2.7 2.7l5.7-5.7a4 4 0 0 0 5.2-5.4l-2.5 2.5-2.4-.4-.4-2.4z" />
        </svg>
      </div>
      <p class="mnt-brand">Ultra RVM</p>
      <h1>{{ t.title }}</h1>
      <p class="mnt-body">{{ t.body }}</p>
      <p class="mnt-safe">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>
        {{ t.safe }}
      </p>
    </section>

    <footer class="mnt-foot">
      <img src="@/assets/dsme-logo.png" alt="DSME" width="600" height="216" />
      <span>DSME Engineering · Smart Recycling</span>
    </footer>
  </main>
</template>

<script setup>
import { computed, ref, watchEffect } from 'vue'
import { LANGUAGES, resolveStoredLanguage, htmlLang } from '@/composables/useLanguage'
import { maintenanceText } from '@/utils/maintenance'
import LanguagePill from '@/components/LanguagePill.vue'

const store = (() => { try { return localStorage } catch { return undefined } })()
const lang = ref(resolveStoredLanguage(store))
const theme = (() => { try { return store?.getItem('rvm_theme') === 'dark' ? 'dark' : 'light' } catch { return 'light' } })()

const t = computed(() => maintenanceText[lang.value] || maintenanceText.en)

function setLang(code) {
  lang.value = code
  try { store?.setItem('rvm_lang', code) } catch { /* private mode: keep it for this visit only */ }
}

watchEffect(() => {
  document.title = t.value.tab
  document.documentElement.lang = htmlLang(lang.value)
  document.documentElement.setAttribute('data-theme', theme)
})
</script>

<style scoped>
.mnt {
  --m-bg: #f0f4f8;
  --m-card: #ffffff;
  --m-text: #1a202c;
  --m-sub: #4a5568;
  --m-border: #e2e8f0;
  --m-green: #15803d;
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  gap: 28px;
  padding: 72px 16px 24px;
  background:
    radial-gradient(60% 45% at 50% 0%, rgba(78, 110, 242, 0.14), transparent 70%),
    var(--m-bg);
  color: var(--m-text);
  font-family: 'DM Sans', sans-serif;
  position: relative;
}
.mnt[data-theme="dark"] {
  --m-bg: #0d1117;
  --m-card: #1c2333;
  --m-text: #e6edf3;
  --m-sub: #8b949e;
  --m-border: #30363d;
  --m-green: #22c55e;
}

.mnt-lang {
  position: absolute;
  top: 16px;
  right: 16px;
  --lang-size: 38px;
}

.mnt-card {
  width: 100%;
  max-width: 480px;
  background: var(--m-card);
  border: 1px solid var(--m-border);
  border-radius: 12px;
  padding: 40px 28px 32px;
  text-align: center;
  /* auto margins centre the card in the space above the footer, which
     stays pinned to the bottom of the screen */
  margin: auto 0;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
}
.mnt-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: #fff;
  background: linear-gradient(135deg, #4e6ef2 0%, #a855f7 100%);
}
.mnt-brand {
  font: 700 13px 'Plus Jakarta Sans', sans-serif;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #4e6ef2;
  margin-bottom: 8px;
}
h1 {
  font: 800 clamp(24px, 6vw, 30px)/1.2 'Plus Jakarta Sans', sans-serif;
  margin-bottom: 12px;
}
.mnt-body {
  color: var(--m-sub);
  font-size: 16px;
  line-height: 1.6;
}
.mnt-safe {
  margin-top: 20px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--m-green);
  font-weight: 600;
  font-size: 14px;
}

.mnt-foot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  color: var(--m-sub);
  font-size: 13px;
}
.mnt-foot img {
  height: 40px;
  width: auto;
  opacity: 0.85;
}
.mnt[data-theme="dark"] .mnt-foot img {
  background: #fff;
  padding: 6px 10px;
  border-radius: 8px;
}
</style>
