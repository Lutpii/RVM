<template>
  <div class="landing">
    <!-- ═══ Hero (gradient, nav inside) ═══ -->
    <header class="hero">
      <div class="wrap hero-inner">
        <nav class="topnav" :aria-label="$t('landing.navLabel')">
          <RouterLink to="/" class="brand" aria-label="RVM, Reverse Vending Machine">
            <span class="brand-mark"><PhRecycle :size="22" aria-hidden="true" /></span>
            <span class="brand-name">RVM</span>
          </RouterLink>
          <div class="nav-actions">
            <button type="button" class="nav-round" @click="toggleTheme()"
              :aria-label="theme === 'dark' ? $t('landing.switchToLight') : $t('landing.switchToDark')">
              <PhSun v-if="theme === 'dark'" :size="19" aria-hidden="true" />
              <PhMoon v-else :size="19" aria-hidden="true" />
            </button>
            <button type="button" class="nav-round nav-lang" @click="toggleLang"
              :aria-label="locale === 'en' ? 'MY – Switch to Bahasa Melayu' : 'EN – Switch to English'">
              {{ locale === 'en' ? 'MY' : 'EN' }}
            </button>
            <RouterLink to="/login" class="nav-login">{{ $t('nav.login') }}</RouterLink>
          </div>
        </nav>

        <div class="hero-grid">
          <div class="hero-copy">
            <span class="hero-badge"><PhLeaf :size="15" weight="bold" aria-hidden="true" /> {{ $t('landing.heroBadge') }}</span>
            <h1 class="hero-title">{{ $t('landing.heroTitle1') }}<br>{{ $t('landing.heroTitle2') }}</h1>
            <p class="hero-text">{{ $t('landing.heroText') }}</p>
            <div class="hero-buttons">
              <button type="button" class="btn-primary" @click="goToScan">
                <PhScan :size="20" weight="bold" aria-hidden="true" /> {{ $t('landing.startRecycling') }}
              </button>
              <RouterLink to="/register" class="btn-outline">{{ $t('landing.createAccount') }}</RouterLink>
            </div>
            <span class="hero-login">{{ $t('landing.haveAccount') }} <RouterLink to="/login">{{ $t('nav.login') }}</RouterLink></span>
          </div>

          <!-- CO2 saved this month, live from every machine -->
          <section class="co2-card" :aria-label="$t('landing.co2Month')">
            <div class="co2-head">
              <span class="tile-icon tile-teal"><PhLeaf :size="24" aria-hidden="true" /></span>
              <span class="co2-head-text">
                <strong>{{ $t('landing.co2Month') }}</strong>
                <span>{{ monthLabel }}</span>
              </span>
            </div>
            <div class="co2-value-row">
              <span class="co2-value">{{ kg(stats.month.carbon_saved_kg) }}</span>
              <span class="co2-items">{{ $t('landing.fromItems', { n: num(stats.month.items_recycled) }) }}</span>
            </div>
            <div class="co2-bar" role="img" :aria-label="barLabel">
              <span v-for="m in monthMaterials" :key="m.key" :class="`seg-${m.key}`" :style="{ width: m.share + '%' }"></span>
            </div>
            <div class="co2-legend">
              <div v-for="m in monthMaterials" :key="m.key" class="legend-item">
                <span class="legend-name"><span :class="['legend-dot', `seg-${m.key}`]"></span>{{ m.label }}</span>
                <span class="legend-kg">{{ kg(m.kg) }}</span>
              </div>
            </div>
            <span class="live-note"><span class="live-dot"></span>{{ $t('landing.liveNote') }}</span>
          </section>
        </div>
      </div>
    </header>

    <!-- ═══ Impact strip (overlaps the hero) ═══ -->
    <section class="wrap-pad impact-section" :aria-label="$t('landing.impactLabel')">
      <div class="wrap card impact">
        <div class="impact-grid">
          <div class="impact-tile">
            <span class="tile-icon tile-blue"><PhRecycle :size="24" aria-hidden="true" /></span>
            <span class="impact-text"><strong>{{ num(stats.items_recycled) }}</strong><span>{{ $t('landing.statItems') }}</span></span>
          </div>
          <div class="impact-tile">
            <span class="tile-icon tile-teal"><PhLeaf :size="24" aria-hidden="true" /></span>
            <span class="impact-text"><strong>{{ kg(stats.carbon_saved_kg) }}</strong><span>{{ $t('landing.statCo2') }}</span></span>
          </div>
          <div class="impact-tile">
            <span class="tile-icon tile-orange"><PhDeviceTablet :size="24" aria-hidden="true" /></span>
            <span class="impact-text"><strong>{{ num(stats.machines) }}</strong><span>{{ $t('landing.statMachines') }}</span></span>
          </div>
        </div>
        <span class="impact-foot"><span class="live-dot"></span>{{ $t('landing.liveAll') }}</span>
      </div>
    </section>

    <!-- ═══ How it works ═══ -->
    <section class="wrap-pad section how" aria-labelledby="how-title">
      <div class="wrap how-inner">
        <div class="section-head">
          <h2 id="how-title" class="section-title">{{ $t('landing.howTitle') }}</h2>
          <p class="section-sub">{{ $t('landing.howSub') }}</p>
        </div>
        <ol class="steps">
          <li v-for="(s, i) in steps" :key="i" class="card step">
            <div class="step-top">
              <span :class="['tile-icon', 'tile-lg', s.tone]"><component :is="s.icon" :size="26" aria-hidden="true" /></span>
              <span class="step-num" aria-hidden="true">{{ i + 1 }}</span>
            </div>
            <h3 class="step-title">{{ $t(s.title) }}</h3>
            <p class="step-text">{{ $t(s.text) }}</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- ═══ What goes in + rewards ═══ -->
    <section class="wrap-pad section">
      <div class="wrap duo">
        <div class="card info-card">
          <h2 class="card-title">{{ $t('landing.goesInTitle') }}</h2>
          <ul class="chips" :aria-label="$t('landing.goesInTitle')">
            <li v-for="c in acceptedChips" :key="c" class="chip-ok"><PhCheck :size="18" weight="bold" aria-hidden="true" />{{ $t(c) }}</li>
          </ul>
          <p class="card-text">{{ $t('landing.goesInText') }}</p>
        </div>

        <div class="card info-card">
          <div class="card-title-row">
            <span class="tile-icon tile-purple"><PhGift :size="24" aria-hidden="true" /></span>
            <h2 class="card-title">{{ $t('landing.rewardsTitle') }}</h2>
          </div>
          <p class="card-text">{{ $t('landing.rewardsText') }}</p>
          <!-- Official artwork from each brand, unmodified; Boost's white logo sits on its own red. -->
          <ul class="wallets" :aria-label="$t('landing.ewalletsLabel')">
            <li class="wallet"><img :src="logoTng" alt="Touch 'n Go eWallet" class="w-tng" /></li>
            <li class="wallet"><img :src="logoGrab" alt="GrabPay" class="w-grab" /></li>
            <li class="wallet wallet-boost"><img :src="logoBoost" alt="Boost" class="w-boost" /></li>
            <li class="wallet"><img :src="logoShopee" alt="ShopeePay" class="w-shopee" /></li>
          </ul>
          <!-- /rewards needs a login; the router sends guests to /login first. -->
          <RouterLink to="/rewards" class="see-rewards">{{ $t('landing.seeRewards') }} <PhCaretRight :size="16" weight="bold" aria-hidden="true" /></RouterLink>
        </div>
      </div>
    </section>

    <!-- ═══ Final call to action ═══ -->
    <section class="wrap-pad section">
      <div class="wrap cta">
        <div class="cta-copy">
          <h2 class="cta-title">{{ $t('landing.ctaTitle') }}</h2>
          <p class="cta-text">{{ $t('landing.ctaText') }}</p>
        </div>
        <button type="button" class="btn-cta" @click="goToScan">
          <PhScan :size="20" weight="bold" aria-hidden="true" /> {{ $t('landing.startRecycling') }}
        </button>
      </div>
    </section>

    <!-- ═══ Footer: centred stack on every width ═══ -->
    <footer class="wrap-pad foot">
      <div class="wrap foot-inner">
        <div class="foot-logos">
          <img :src="logoUmpsa" alt="UMPSA" class="logo-umpsa" width="255" height="192" />
          <span class="foot-divider" aria-hidden="true"></span>
          <img :src="logoDsme" alt="DSME" class="logo-dsme" width="600" height="216" />
        </div>
        <span class="foot-tagline">{{ $t('landing.footerTagline') }}</span>
        <nav class="foot-links" :aria-label="$t('landing.footerLabel')">
          <RouterLink to="/login">{{ $t('nav.login') }}</RouterLink>
          <RouterLink to="/register">{{ $t('nav.register') }}</RouterLink>
          <RouterLink to="/privacy" class="muted">{{ $t('landing.privacy') }}</RouterLink>
        </nav>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import {
  PhSun, PhMoon, PhRecycle, PhLeaf, PhScan, PhCheck, PhGift, PhCaretRight,
  PhDeviceTablet, PhQrCode, PhTrayArrowDown, PhCoins,
} from '@phosphor-icons/vue'
import logoUmpsa from '@/assets/logo-umpsa-trimmed.webp'
import logoDsme from '@/assets/dsme-logo.png'
import logoTng from '@/assets/ewallet/tng.png'
import logoGrab from '@/assets/ewallet/grabpay.png'
import logoBoost from '@/assets/ewallet/boost.svg'
import logoShopee from '@/assets/ewallet/shopeepay.png'

const router = useRouter()
const theme = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale, t } = useI18n()

function toggleLang() {
  locale.value = locale.value === 'en' ? 'my' : 'en'
  localStorage.setItem('rvm_lang', locale.value)
}

function goToScan() {
  router.push(localStorage.getItem('rvm_token') ? '/scan' : '/login')
}

const steps = [
  { icon: PhQrCode, tone: 'tile-blue', title: 'landing.step1Title', text: 'landing.step1Text' },
  { icon: PhTrayArrowDown, tone: 'tile-teal', title: 'landing.step2Title', text: 'landing.step2Text' },
  { icon: PhCoins, tone: 'tile-green', title: 'landing.step3Title', text: 'landing.step3Text' },
]
const acceptedChips = ['landing.chipPlastic', 'landing.chipCans', 'landing.chipPaper']

// ── Live numbers (GET /api/stats/public) ──
const stats = ref({
  items_recycled: null, carbon_saved_kg: null, machines: null,
  month: { year: null, month: null, items_recycled: null, carbon_saved_kg: null, carbon_by_material_kg: {} },
})

onMounted(async () => {
  try {
    const res = await api.get('/stats/public')
    if (res.data?.stats) stats.value = res.data.stats
  } catch { /* numbers stay as "—" */ }
})

const intlLocale = computed(() => (locale.value === 'my' ? 'ms-MY' : 'en-MY'))
function num(n) { return n == null ? '—' : Number(n).toLocaleString('en-MY') }
function kg(n) {
  if (n == null) return '—'
  const v = Number(n) || 0
  return `${v >= 100 ? Math.round(v).toLocaleString('en-MY') : v.toFixed(1)} kg`
}

const monthLabel = computed(() => {
  const { year, month } = stats.value.month
  const d = year && month ? new Date(year, month - 1, 1) : new Date()
  return d.toLocaleDateString(intlLocale.value, { month: 'long', year: 'numeric' })
})

const MONTH_MATERIALS = [
  { key: 'plastic', label: 'landing.matPlastic' },
  { key: 'aluminum', label: 'landing.matAluminium' },
  { key: 'paper', label: 'landing.matPaper' },
  // Glass is not accepted; old rows from machines that once took it only
  // show up here when they exist, so the legend still adds up.
  { key: 'glass', label: 'landing.matGlass', optional: true },
]
const monthMaterials = computed(() => {
  const by = stats.value.month.carbon_by_material_kg || {}
  const total = MONTH_MATERIALS.reduce((sum, m) => sum + (Number(by[m.key]) || 0), 0)
  return MONTH_MATERIALS.filter(m => !m.optional || (Number(by[m.key]) || 0) > 0).map(m => {
    const v = Number(by[m.key]) || 0
    return { key: m.key, label: t(m.label), kg: stats.value.month.carbon_by_material_kg ? v : null, share: total ? (v / total) * 100 : 0 }
  })
})
const barLabel = computed(() => monthMaterials.value.map(m => `${m.label} ${kg(m.kg)}`).join(', '))
</script>

<style scoped>
/* 10px corners for cards and buttons, pills fully round (same as the Account page). */
.landing { min-height: 100vh; min-height: 100dvh; background: var(--bg-primary); color: var(--text-primary); }
.wrap { max-width: 1160px; margin: 0 auto; box-sizing: border-box; }
.wrap-pad { padding-left: 24px; padding-right: 24px; }
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; box-sizing: border-box; min-width: 0; }
h1, h2, h3 { font-family: 'Plus Jakarta Sans', 'DM Sans', sans-serif; }

/* ── Hero ── */
.hero { background: var(--grad-header); color: #fff; padding: 20px 24px 124px; }
.hero-inner { display: flex; flex-direction: column; gap: 48px; }
.topnav { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.brand { display: flex; align-items: center; gap: 10px; min-height: 44px; color: #fff; text-decoration: none; }
.brand-mark { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.brand-name { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 800; }
.nav-actions { display: flex; align-items: center; gap: 8px; }
.nav-round { min-width: 44px; height: 44px; padding: 0; border-radius: 999px; border: 0; background: rgba(255,255,255,0.18); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font: inherit; }
.nav-lang { padding: 0 12px; font-size: 14px; font-weight: 700; }
.nav-round:hover { background: rgba(255,255,255,0.28); }
.nav-login { height: 44px; padding: 0 18px; box-sizing: border-box; display: inline-flex; align-items: center; border: 1.5px solid rgba(255,255,255,0.85); border-radius: 999px; color: #fff; font-size: 14px; font-weight: 700; text-decoration: none; }
.nav-login:hover { background: rgba(255,255,255,0.12); }

.hero-grid { display: flex; flex-wrap: wrap; align-items: center; gap: 48px; }
.hero-copy { flex: 1 1 440px; min-width: 0; display: flex; flex-direction: column; gap: 20px; }
.hero-badge { align-self: flex-start; display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,0.18); font-size: 13px; font-weight: 600; }
.hero-title { margin: 0; font-size: clamp(34px, 4.6vw, 56px); line-height: 1.08; font-weight: 800; letter-spacing: -0.02em; }
.hero-text { margin: 0; max-width: 520px; font-size: 18px; line-height: 1.55; opacity: 0.95; }
.hero-buttons { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 4px; max-width: 520px; }
.btn-primary, .btn-outline {
  flex: 1 1 200px; min-height: 52px; padding: 0 22px; box-sizing: border-box; border-radius: 10px;
  display: inline-flex; align-items: center; justify-content: center; gap: 10px;
  font: inherit; font-size: 16px; font-weight: 700; text-decoration: none; cursor: pointer;
}
.btn-primary { border: 0; background: #fff; color: #3b5bdb; box-shadow: 0 8px 24px rgba(15,23,42,0.18); }
.btn-primary:hover { filter: brightness(0.97); }
.btn-outline { border: 1.5px solid rgba(255,255,255,0.85); color: #fff; background: transparent; }
.btn-outline:hover { background: rgba(255,255,255,0.12); }
.hero-login { display: flex; align-items: center; flex-wrap: wrap; gap: 4px; font-size: 14px; opacity: 0.95; }
.hero-login a { min-height: 44px; display: inline-flex; align-items: center; padding: 0 4px; color: #fff; font-weight: 700; text-decoration: underline; }

.co2-card {
  flex: 1 1 340px; min-width: 0; max-width: 440px; margin: 0 auto; box-sizing: border-box;
  background: var(--bg-card); color: var(--text-primary); border-radius: 10px; padding: 22px;
  box-shadow: 0 16px 40px rgba(15,23,42,0.22); display: flex; flex-direction: column; gap: 16px;
}
.co2-head { display: flex; align-items: center; gap: 12px; }
.co2-head-text { display: flex; flex-direction: column; }
.co2-head-text strong { font-size: 15px; }
.co2-head-text span { font-size: 13px; color: var(--text-muted); }
.co2-value-row { display: flex; align-items: baseline; flex-wrap: wrap; gap: 10px; }
.co2-value { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 48px; font-weight: 800; line-height: 1; color: #0f766e; }
:global([data-theme="dark"]) .co2-value { color: #2dd4bf; }
.co2-items { font-size: 14px; color: var(--text-muted); }
.co2-bar { display: flex; height: 12px; border-radius: 999px; overflow: hidden; background: var(--border); }
.seg-plastic { background: #4e6ef2; }
.seg-aluminum { background: #d97706; }
.seg-paper { background: #0f9488; }
.seg-glass { background: #9333ea; }
.co2-legend { display: grid; grid-template-columns: repeat(auto-fit, minmax(72px, 1fr)); gap: 8px; }
.legend-item { display: flex; flex-direction: column; gap: 2px; }
.legend-name { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); }
.legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.legend-kg { font-size: 16px; font-weight: 700; }
.live-note { display: flex; align-items: center; gap: 8px; padding-top: 12px; border-top: 1px solid var(--border); font-size: 13px; color: var(--text-muted); }
.live-dot { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; flex-shrink: 0; }

/* ── Icon tiles ── */
.tile-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tile-lg { width: 52px; height: 52px; }
.tile-blue { background: rgba(78,110,242,0.12); color: #4e6ef2; }
.tile-teal { background: rgba(20,184,166,0.14); color: #0f9488; }
.tile-green { background: rgba(34,197,94,0.14); color: #16a34a; }
.tile-orange { background: rgba(245,158,11,0.14); color: #d97706; }
.tile-purple { background: rgba(168,85,247,0.12); color: #9333ea; }

/* ── Impact strip ── */
.impact-section { margin-top: -84px; position: relative; }
.impact { box-shadow: 0 6px 20px rgba(15,23,42,0.08); }
.impact-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.impact-tile { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px 14px; padding: 20px 12px; text-align: center; }
.impact-tile + .impact-tile { border-left: 1px solid var(--border); }
.impact-text { display: flex; flex-direction: column; min-width: 0; }
.impact-text strong { font-size: clamp(20px, 2.4vw, 26px); font-weight: 800; white-space: nowrap; }
.impact-text span { font-size: 14px; color: var(--text-muted); }
.impact-foot { display: flex; align-items: center; gap: 8px; padding: 10px 24px; border-top: 1px solid var(--border); font-size: 13px; color: var(--text-muted); }

/* ── Sections ── */
.section { padding-top: 28px; }
.how-inner { display: flex; flex-direction: column; gap: 20px; }
.section-head { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
.section-title { margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -0.01em; }
.section-sub { margin: 0; font-size: 16px; color: var(--text-muted); }
.steps { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
.step { padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.step-top { display: flex; align-items: center; justify-content: space-between; }
.step-num { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 36px; font-weight: 800; color: var(--border); }
.step-title { margin: 0; font-size: 18px; font-weight: 700; }
.step-text { margin: 0; font-size: 15px; line-height: 1.55; color: var(--text-secondary); }

.duo { display: flex; flex-wrap: wrap; gap: 16px; }
.info-card { flex: 1 1 420px; padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.card-title-row { display: flex; align-items: center; gap: 12px; }
.card-title { margin: 0; font-size: 24px; font-weight: 800; }
.card-text { margin: 0; font-size: 15px; line-height: 1.55; color: var(--text-secondary); }
.chips { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 10px; }
.chip-ok { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 14px; border-radius: 999px; background: rgba(34,197,94,0.12); color: #15803d; font-size: 15px; font-weight: 700; }
:global([data-theme="dark"]) .chip-ok { color: #4ade80; }
.not-accepted { margin-top: auto; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 14px; color: var(--text-muted); }
.chip-no { padding: 4px 10px; border-radius: 999px; background: var(--bg-hover); color: var(--text-secondary); }
.wallets { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 10px; }
.wallet { height: 56px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; display: flex; align-items: center; justify-content: center; }
.wallet-boost { background: #ea0029; border-color: #ea0029; }
.w-tng { height: 40px; width: auto; }
.w-grab { height: 24px; width: auto; }
.w-boost, .w-shopee { height: 26px; width: auto; }
.see-rewards { margin-top: auto; align-self: flex-start; min-height: 44px; display: inline-flex; align-items: center; gap: 6px; color: var(--accent-blue); font-size: 15px; font-weight: 700; text-decoration: none; }
.see-rewards:hover { text-decoration: underline; }

/* ── CTA ── */
.cta { border-radius: 10px; background: #1e293b; color: #fff; padding: 32px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; }
.cta-copy { flex: 1 1 300px; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.cta-title { margin: 0; font-size: 26px; font-weight: 800; }
.cta-text { margin: 0; font-size: 16px; color: #cbd5e1; }
.btn-cta { min-height: 52px; padding: 0 26px; border: 0; border-radius: 10px; background: #4e6ef2; color: #fff; display: inline-flex; align-items: center; gap: 10px; font: inherit; font-size: 16px; font-weight: 700; cursor: pointer; }
.btn-cta:hover { filter: brightness(1.08); }

/* ── Footer ── */
.foot { padding-top: 32px; padding-bottom: 28px; }
.foot-inner { padding-top: 32px; border-top: 1px solid var(--border); display: flex; flex-direction: column; align-items: center; gap: 14px; text-align: center; }
.foot-logos { display: flex; align-items: center; justify-content: center; gap: 28px; }
.logo-umpsa { height: 56px; width: auto; }
.logo-dsme { height: 44px; width: auto; }
.foot-divider { width: 1px; height: 40px; background: var(--border); }
/* Dark mode: the navy DSME logo disappears on a dark page, so both logos
   sit on a small white plate (the artwork itself stays unchanged). */
:global([data-theme="dark"]) .foot-logos { background: #fff; padding: 10px 20px; border-radius: 10px; }
:global([data-theme="dark"]) .foot-divider { background: #e2e8f0; }
.foot-tagline { font-size: 14px; color: var(--text-muted); }
.foot-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 0 24px; font-size: 14px; }
.foot-links a { min-height: 44px; display: inline-flex; align-items: center; color: var(--accent-blue); font-weight: 600; text-decoration: none; }
.foot-links a.muted { color: var(--text-muted); font-weight: 400; }

@media (max-width: 480px) {
  .hero { padding: 16px 16px 116px; }
  .wrap-pad { padding-left: 16px; padding-right: 16px; }
  .hero-inner { gap: 36px; }
  .hero-text { font-size: 16px; }
  .section-title { font-size: 26px; }
  .cta { padding: 24px; }
  .cta-title { font-size: 22px; }
  .btn-cta { width: 100%; justify-content: center; }
}
</style>
