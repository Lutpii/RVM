<!-- FrontEnd/src/views/PrivacyView.vue -->
<template>
  <div class="privacy-page">
    <RouterLink to="/" class="page-back" :aria-label="$t('notFound.homeBtn')">←</RouterLink>
    <article class="privacy-card">
      <h1>{{ doc.title }}</h1>
      <p class="updated">{{ doc.updated }}</p>
      <p>{{ doc.intro }}</p>

      <section v-for="section in doc.sections" :key="section.title">
        <h2>{{ section.title }}</h2>
        <p v-if="section.text">{{ section.text }}</p>
        <ul v-if="section.items">
          <li v-for="item in section.items" :key="item">{{ item }}</li>
        </ul>
      </section>

      <p class="contact">
        {{ doc.contact }} <a :href="`mailto:${CONTACT_EMAIL}`">{{ CONTACT_EMAIL }}</a>
      </p>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { content } from './privacyContent.js'

// Linked from the Google OAuth consent screen (Branding → privacy policy
// link), so keep it public and keep it in step with what the app actually
// stores: users / recycling_sessions / transactions / points_history /
// reward_redemptions / detection_logs.
const CONTACT_EMAIL = 'lutpiy03@gmail.com'

const { locale } = useI18n()
const doc = computed(() => content[locale.value] || content.en)
</script>

<style scoped>
.privacy-page { min-height: 100dvh; padding: 72px 16px 40px; }
.page-back {
  position: fixed; top: 16px; left: 16px; z-index: 10;
  width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
  background: var(--bg-card); color: var(--text-primary);
  border: 1px solid var(--border); border-radius: 12px;
  box-shadow: var(--shadow); text-decoration: none; font-size: 20px;
}
.privacy-card {
  max-width: 720px; margin: 0 auto; padding: 28px 24px;
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  color: var(--text-secondary); font-size: 14px; line-height: 1.65;
}
h1 { font-size: 24px; color: var(--text-primary); }
.updated { font-size: 12px; color: var(--text-muted); margin: 4px 0 16px; }
section { margin-top: 20px; }
h2 { font-size: 16px; color: var(--text-primary); margin-bottom: 6px; }
ul { padding-left: 20px; display: flex; flex-direction: column; gap: 6px; }
.contact { margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); }
.contact a { color: var(--accent-blue); }
</style>
