<template>
  <div class="landing">
    <!-- ═══ Hero (gradient, nav inside) ═══ -->
    <header ref="heroEl" class="hero">
      <div class="wrap hero-inner">
        <nav class="topnav" :aria-label="$t('landing.navLabel')">
          <RouterLink to="/" class="brand" aria-label="Ultra RVM, Reverse Vending Machine">
            <span class="brand-mark"><PhRecycle :size="22" aria-hidden="true" /></span>
            <span class="brand-name">Ultra RVM</span>
          </RouterLink>
          <div class="nav-actions">
            <button type="button" class="nav-link" @click="scrollToId('how')">{{ $t('landing.navHow') }}</button>
            <button type="button" class="nav-link" @click="scrollToId('rewards')">{{ $t('landing.navRewards') }}</button>
            <button type="button" class="nav-link" @click="scrollToId('faq')">{{ $t('landing.navFaq') }}</button>
            <button type="button" class="nav-round" @click="toggleTheme()"
              :aria-label="theme === 'dark' ? $t('landing.switchToLight') : $t('landing.switchToDark')">
              <PhSun v-if="theme === 'dark'" :size="19" aria-hidden="true" />
              <PhMoon v-else :size="19" aria-hidden="true" />
            </button>
            <LanguageSwitch variant="menu" />
            <RouterLink to="/login" class="nav-login">{{ $t('nav.login') }}</RouterLink>
          </div>
        </nav>

        <div class="hero-grid">
          <div class="hero-copy">
            <span class="hero-badge"><PhLeaf :size="15" weight="bold" aria-hidden="true" /> {{ $t('landing.heroBadge') }}</span>
            <template v-if="platformMode">
              <h1 class="hero-title">{{ $t('landing.platform.heroTitle1') }}<br>{{ $t('landing.platform.heroTitle2') }}</h1>
              <p class="hero-text">{{ $t('landing.platform.heroText') }}</p>
            </template>
            <template v-else>
              <h1 class="hero-title">{{ $t('landing.heroTitle1') }}<br>{{ $t('landing.heroTitle2') }}</h1>
              <p class="hero-text">{{ $t('landing.heroText') }}</p>
            </template>
            <div class="hero-buttons">
              <button type="button" class="btn-primary" @click="goToScan">
                <PhScan :size="20" weight="bold" aria-hidden="true" /> {{ $t('landing.startRecycling') }}
              </button>
              <RouterLink to="/register" class="btn-outline">{{ $t('landing.createAccount') }}</RouterLink>
            </div>
            <span class="hero-login">{{ $t('landing.haveAccount') }} <RouterLink to="/login">{{ $t('nav.login') }}</RouterLink></span>
          </div>

          <!-- CO2 saved this month, live from every machine -->
          <section v-if="platformMode" class="co2-card platform-co2" :aria-label="$t('landing.co2Month')">
            <div class="co2-head">
              <span class="tile-icon tile-teal"><PhLeaf :size="24" aria-hidden="true" /></span>
              <span class="co2-head-text">
                <strong>{{ $t('landing.co2Month') }}</strong>
                <span>{{ monthLabel }}</span>
              </span>
            </div>
            <div class="co2-value-row platform-co2-value">
              <span class="co2-value">{{ kg(stats.month.carbon_saved_kg) }}</span>
              <CarbonSourcesNote />
            </div>
            <div v-for="m in monthMachineTypes" :key="m.type" class="platform-carbon-row">
              <div class="platform-carbon-label">
                <span>{{ $t('platform.machine.' + m.type) }}</span>
                <strong>{{ kg(m.carbon) }}</strong>
              </div>
              <div class="platform-carbon-bar" aria-hidden="true"><span :class="'seg-type-' + m.type" :style="{ width: m.share + '%' }"></span></div>
            </div>
          </section>
          <section v-else class="co2-card" :aria-label="$t('landing.co2Month')">
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
                <span class="legend-kg">{{ itemsText(m.items) }}</span>
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
          <div v-if="!platformMode" class="impact-tile">
            <strong class="big-num"><CountUp :value="toNum(stats.items_recycled)" :format="num" /></strong>
            <span class="impact-label">{{ $t('landing.statItems') }}</span>
          </div>
          <div class="impact-tile">
            <strong class="big-num"><CountUp :value="toNum(stats.carbon_saved_kg)" :format="kg" /></strong>
            <span class="impact-label">{{ $t('landing.statCo2') }}</span>
          </div>
          <div class="impact-tile">
            <template v-if="platformMode">
              <strong class="big-num"><CountUp :value="platformMachineTotal" :format="num" /></strong>
              <span class="impact-label">{{ $t('landing.statMachines') }}</span>
              <span class="impact-sub">
                <span v-for="type in machineTypes" :key="type" class="platform-machine-count">
                  {{ $t('platform.machine.' + type) }} <b>{{ num(stats.machines?.[type]) }}</b>
                </span>
              </span>
            </template>
            <template v-else>
              <strong class="big-num"><CountUp :value="toNum(stats.machines)" :format="num" /></strong>
              <span class="impact-label">{{ $t('landing.statMachines') }}</span>
            </template>
          </div>
          <div v-if="platformMode" class="impact-tile">
            <strong class="big-num">1</strong>
            <span class="impact-label">{{ $t('landing.statAccounts') }}</span>
            <span class="impact-sub">{{ $t('landing.statAccountsSub') }}</span>
          </div>
        </div>
        <span v-if="!platformMode" class="impact-foot"><span class="live-dot"></span>{{ $t('landing.liveAll') }}</span>
      </div>
    </section>

    <PlatformMachinesSection v-if="platformMode" />

    <!-- ═══ How it works ═══ -->
    <section id="how" class="wrap-pad section how" aria-labelledby="how-title">
      <div class="wrap how-inner">
        <div class="section-head">
          <h2 id="how-title" class="section-title">{{ $t('landing.howTitle') }}</h2>
          <p class="section-sub">{{ $t('landing.howSub') }}</p>
        </div>
        <ol class="steps">
          <li v-for="(s, i) in steps" :key="i" class="card step">
            <!-- Real screens: kiosk QR + phone scanning it, kiosk "Success", Rewards page -->
            <div class="stage" role="img" :aria-label="$t('landing.stepAlt.s' + (i + 1))">
              <div v-if="s.visual === 'scan'" class="scan-duo" aria-hidden="true">
                <div class="device-tablet"><img :src="imgKioskQr" alt="" loading="lazy" /></div>
                <div class="device-phone phone-scan">
                  <div class="scan-feed" :style="{ backgroundImage: `url(${imgKioskQr})` }"></div>
                  <span class="scan-top">{{ $t('scan.title') }}</span>
                  <div class="scan-vf">
                    <img :src="imgQrCode" alt="" loading="lazy" />
                    <i class="vf-c tl"></i><i class="vf-c tr"></i><i class="vf-c bl"></i><i class="vf-c br"></i>
                    <span class="vf-laser"></span>
                  </div>
                </div>
              </div>
              <div v-else-if="s.visual === 'tablet'" class="device-tablet tablet-up" aria-hidden="true"><img :src="imgKioskSuccess" alt="" loading="lazy" /></div>
              <div v-else class="device-phone" aria-hidden="true"><img :src="imgPhoneRewards" alt="" loading="lazy" /></div>
            </div>
            <span class="step-num" aria-hidden="true">{{ i + 1 }}</span>
            <h3 class="step-title">{{ $t(s.title) }}</h3>
            <p class="step-text">{{ $t(s.text) }}</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- ═══ Rewards + what goes in, then the scrolling partner strip ═══ -->
    <section id="rewards" class="wrap-pad section" aria-labelledby="rewards-title">
      <div class="wrap card rewards-card">
        <div class="rewards-grid">
          <div class="rewards-copy">
            <h2 id="rewards-title" class="section-title">{{ $t('landing.rewardsTitle') }}</h2>
            <p class="rewards-text">{{ $t('landing.rewardsText') }}</p>
            <!-- /rewards needs a login; the router sends guests to /login first. -->
            <RouterLink to="/rewards" class="see-rewards">{{ $t('landing.seeRewards') }} <PhCaretRight :size="16" weight="bold" aria-hidden="true" /></RouterLink>
          </div>
          <div class="goes-in">
            <h3 class="card-title">{{ $t('landing.goesInTitle') }}</h3>
            <ul class="chips" :aria-label="$t('landing.goesInTitle')">
              <li v-for="c in acceptedChips" :key="c" class="chip-ok"><PhCheck :size="18" weight="bold" aria-hidden="true" />{{ $t(c) }}</li>
              <li v-if="platformMode" class="chip-ok"><PhCheck :size="18" weight="bold" aria-hidden="true" />{{ $t('landing.platform.foodChip') }}</li>
            </ul>
            <p class="card-text">{{ $t(platformMode ? 'landing.platform.goesInText' : 'landing.goesInText') }}</p>
          </div>
        </div>
        <!-- Official artwork from each brand, unmodified; white logos sit on the brand's own colour. -->
        <div class="marquee">
          <ul class="marquee-track" :aria-label="$t('landing.partnersLabel')">
            <li v-for="p in partners" :key="p.name" :class="['partner', p.cls]"><img :src="p.src" :alt="p.name" /></li>
            <li v-for="p in partners" :key="p.name + '-2'" :class="['partner', 'partner-copy', p.cls]" aria-hidden="true"><img :src="p.src" alt="" /></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- ═══ FAQ ═══ -->
    <section id="faq" class="wrap-pad section" aria-labelledby="faq-title">
      <div class="wrap card faq-card">
        <h2 id="faq-title" class="section-title">{{ $t('landing.faqTitle') }}</h2>
        <div class="faq-grid">
          <details v-for="n in 4" :key="n" class="faq-item">
            <summary>{{ $t('landing.faq.q' + n) }}</summary>
            <p>{{ $t('landing.faq.a' + n) }}</p>
          </details>
        </div>
      </div>
    </section>

    <!-- ═══ Final call to action ═══ -->
    <section class="wrap-pad section">
      <div class="wrap cta">
        <div class="cta-copy">
          <h2 class="cta-title">{{ $t('landing.ctaTitle') }}</h2>
          <p class="cta-text">{{ $t('landing.ctaText') }}</p>
          <button type="button" class="btn-cta" @click="goToScan">
            <PhScan :size="20" weight="bold" aria-hidden="true" /> {{ $t('landing.startRecycling') }}
          </button>
        </div>
        <div class="cta-phone device-phone" aria-hidden="true"><img :src="imgPhoneRewards" alt="" loading="lazy" /></div>
      </div>
    </section>

    <!-- ═══ Footer: centred stack on every width ═══ -->
    <footer class="wrap-pad foot">
      <div class="wrap foot-inner">
        <div class="foot-logos">
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
import { computed, inject, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import LanguageSwitch from '@/components/LanguageSwitch.vue'
import CountUp from '@/components/landing/CountUp.vue'
import CarbonSourcesNote from '@/components/platform/CarbonSourcesNote.vue'
import PlatformMachinesSection from '@/components/platform/PlatformMachinesSection.vue'
import { platformMode } from '@/utils/featureFlags'
import { intlLocale as toIntlLocale } from '@/composables/useLanguage'
import api from '@/services/api'
import { PhSun, PhMoon, PhRecycle, PhLeaf, PhScan, PhCheck, PhCaretRight } from '@phosphor-icons/vue'
import logoDsme from '@/assets/dsme-logo.png'
import logoTng from '@/assets/ewallet/tng.png'
import logoGrab from '@/assets/ewallet/grabpay.png'
import logoBoost from '@/assets/ewallet/boost.svg'
import logoShopee from '@/assets/ewallet/shopeepay.png'
import logoGrabFood from '@/assets/landing/grabfood-white.png'
import logoTealive from '@/assets/landing/tealive.svg'
import logoJaya from '@/assets/landing/jayagrocer.jpg'
import logo99 from '@/assets/landing/99speedmart.jpg'
import imgKioskQr from '@/assets/landing/kiosk-qr.webp'
import imgQrCode from '@/assets/landing/kiosk-qr-code.png'
import imgKioskSuccess from '@/assets/landing/kiosk-success.webp'
import imgPhoneRewards from '@/assets/landing/phone-rewards.webp'

const router = useRouter()
const theme = inject('theme')
const toggleTheme = inject('toggleTheme')
const { locale, t } = useI18n()

function goToScan() {
  router.push(localStorage.getItem('rvm_token') ? '/scan' : '/login')
}

function scrollToId(id) {
  const smooth = !window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
  document.getElementById(id)?.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' })
}

const steps = [
  { visual: 'scan', title: 'landing.step1Title', text: 'landing.step1Text' },
  { visual: 'tablet', title: 'landing.step2Title', text: 'landing.step2Text' },
  { visual: 'phone', title: 'landing.step3Title', text: 'landing.step3Text' },
]

// Reward partners and cash-out e-wallets (vouchers: GrabFood, Zus / Tealive,
// Jaya Grocer / 99 Speedmart). No ZUS artwork yet, Tealive stands for that voucher.
const partners = [
  { name: "Touch 'n Go eWallet", src: logoTng, cls: 'p-tng' },
  { name: 'GrabFood', src: logoGrabFood, cls: 'p-grabfood' },
  { name: 'Tealive', src: logoTealive, cls: '' },
  { name: 'Jaya Grocer', src: logoJaya, cls: '' },
  { name: '99 Speedmart', src: logo99, cls: '' },
  { name: 'GrabPay', src: logoGrab, cls: 'p-grab' },
  { name: 'Boost', src: logoBoost, cls: 'p-boost' },
  { name: 'ShopeePay', src: logoShopee, cls: '' },
]
const acceptedChips = ['landing.chipPlastic', 'landing.chipCans', 'landing.chipPaper']

// The window scrollbar track sits outside the page, so it showed as a white
// (or dark) strip beside the purple hero. While this page is open the track
// is painted purple for the part of the screen the hero still covers.
// A plain ::-webkit-scrollbar-track rule is the only one Chromium applies to
// the window scrollbar, so it lives in a <style> added here and removed on leave.
const heroEl = ref(null)
let trackStyle = null
let trackFrame = 0
function updateTrack() {
  trackFrame = 0
  const visible = Math.max(0, (heroEl.value?.offsetHeight || 0) - window.scrollY)
  document.documentElement.style.setProperty('--landing-track-h', `${visible}px`)
}
function queueTrack() { if (!trackFrame) trackFrame = requestAnimationFrame(updateTrack) }
onMounted(() => {
  trackStyle = document.createElement('style')
  trackStyle.textContent = '::-webkit-scrollbar-track { background: linear-gradient(180deg, #9568f6 0, #a855f7 var(--landing-track-h, 0px), var(--bg-primary) var(--landing-track-h, 0px)); }'
  document.head.appendChild(trackStyle)
  updateTrack()
  window.addEventListener('scroll', queueTrack, { passive: true })
  window.addEventListener('resize', queueTrack)
})
onBeforeUnmount(() => {
  window.removeEventListener('scroll', queueTrack)
  window.removeEventListener('resize', queueTrack)
  cancelAnimationFrame(trackFrame)
  trackStyle?.remove()
  document.documentElement.style.removeProperty('--landing-track-h')
})

// ── Live numbers (GET /api/stats/public) ──
const stats = ref({
  items_recycled: null, carbon_saved_kg: null, machines: null,
  month: { year: null, month: null, items_recycled: null, carbon_saved_kg: null, carbon_by_material_kg: null, items_by_material: null },
})

onMounted(async () => {
  try {
    const res = await api.get(platformMode ? '/platform/stats' : '/stats/public')
    if (res.data?.stats) stats.value = res.data.stats
  } catch { /* numbers stay as "—" */ }
})

const intlLocale = computed(() => toIntlLocale(locale.value))
function num(n) { return n == null ? '—' : Math.round(Number(n)).toLocaleString('en-MY') }
function toNum(n) { return n == null || Number.isNaN(Number(n)) ? null : Number(n) }
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

const machineTypes = ['rvm', 'compactor', 'composter']
const platformMachineTotal = computed(() => stats.value.machines == null
  ? null
  : machineTypes.reduce((sum, type) => sum + (Number(stats.value.machines[type]) || 0), 0))
const monthMachineTypes = computed(() => {
  const breakdown = stats.value.month.carbon_by_machine_kg
  const total = Number(stats.value.month.carbon_saved_kg) || 0
  return machineTypes.map(type => ({
    type,
    carbon: breakdown?.[type] ?? null,
    share: total ? (Number(breakdown?.[type]) || 0) * 100 / total : 0,
  }))
})

const MONTH_MATERIALS = [
  { key: 'plastic', label: 'landing.matPlastic' },
  { key: 'aluminum', label: 'landing.matAluminium' },
  { key: 'paper', label: 'landing.matPaper' },
]
// The bar splits this month's ITEMS by material. A CO2 split would always be
// mostly aluminium (a can saves ~6x the CO2 of a bottle or paper), which
// hides how evenly people recycle; the big number above stays total CO2.
const monthMaterials = computed(() => {
  const loaded = !!stats.value.month.items_by_material
  const by = stats.value.month.items_by_material || {}
  const total = MONTH_MATERIALS.reduce((sum, m) => sum + (Number(by[m.key]) || 0), 0)
  return MONTH_MATERIALS.map(m => {
    const n = Number(by[m.key]) || 0
    return { key: m.key, label: t(m.label), items: loaded ? n : null, share: total ? (n / total) * 100 : 0 }
  })
})
function itemsText(n) { return n == null ? '—' : t('landing.itemsCount', { n: num(n) }) }
const barLabel = computed(() => monthMaterials.value.map(m => `${m.label} ${itemsText(m.items)}`).join(', '))
</script>

<style scoped>
.platform-co2-value { align-items: center; }
.platform-co2-value :deep(summary) { color: inherit; }
.platform-carbon-row + .platform-carbon-row { margin-top: 9px; }
.platform-carbon-label { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; font-size: .82rem; }
.platform-carbon-bar { margin-top: 4px; height: 6px; overflow: hidden; border-radius: 99px; background: var(--bg-hover); }
.platform-carbon-bar > span { display: block; height: 100%; border-radius: inherit; }
.seg-type-rvm { background: #16a34a; }
.seg-type-compactor { background: #d97706; }
.seg-type-composter { background: #0891b2; }
.platform-machine-count { display: inline-flex; gap: 4px; align-items: center; flex-wrap: wrap; justify-content: center; }
/* 10px corners for cards and buttons, pills fully round (same as the Account page). */
.landing { min-height: 100vh; min-height: 100dvh; background: var(--bg-primary); color: var(--text-primary); }
.wrap { max-width: 1160px; margin: 0 auto; box-sizing: border-box; }
.wrap-pad { padding-left: 24px; padding-right: 24px; }
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; box-sizing: border-box; min-width: 0; }
h1, h2, h3 { font-family: 'Plus Jakarta Sans', 'DM Sans', sans-serif; }

/* ── Hero ── */
.hero { background: var(--grad-header); color: #fff; padding: 20px 24px 124px; position: relative; overflow: hidden; }
/* Soft translucent circles on the purple gradient (decoration only) */
.hero::before, .hero::after { content: ''; position: absolute; border-radius: 50%; pointer-events: none; }
.hero::before { right: -160px; top: -160px; width: 520px; height: 520px; background: rgba(255,255,255,0.07); }
.hero::after { left: -120px; bottom: -220px; width: 420px; height: 420px; background: rgba(255,255,255,0.05); }
.hero-inner { position: relative; z-index: 1; }
.hero-inner { display: flex; flex-direction: column; gap: 48px; }
.topnav { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.brand { display: flex; align-items: center; gap: 10px; min-height: 44px; color: #fff; text-decoration: none; }
.brand-mark { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.brand-name { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 800; white-space: nowrap; }
.nav-actions { display: flex; align-items: center; gap: 8px; }
.nav-link { min-height: 44px; padding: 0 12px; border: 0; border-radius: 10px; background: transparent; color: #fff; font: inherit; font-size: 15px; font-weight: 600; cursor: pointer; opacity: 0.95; white-space: nowrap; }
.nav-link:hover { background: rgba(255,255,255,0.14); }
@media (max-width: 900px) { .nav-link { display: none; } }
.nav-round { min-width: 44px; height: 44px; padding: 0; border-radius: 999px; border: 0; background: rgba(255,255,255,0.18); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font: inherit; }
.nav-round:hover { background: rgba(255,255,255,0.28); }
.nav-login { height: 44px; padding: 0 18px; box-sizing: border-box; white-space: nowrap; display: inline-flex; align-items: center; border: 1.5px solid rgba(255,255,255,0.85); border-radius: 999px; color: #fff; font-size: 14px; font-weight: 700; text-decoration: none; }
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
.co2-value { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 48px; font-weight: 800; line-height: 1; color: var(--carbon-accent); }
.co2-total { color: var(--carbon-accent); }
.co2-items { font-size: 14px; color: var(--text-muted); }
.co2-bar { display: flex; height: 12px; border-radius: 999px; overflow: hidden; background: var(--border); }
.seg-plastic { background: #4e6ef2; }
.seg-aluminum { background: #d97706; }
.seg-paper { background: #0f9488; }
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
.impact-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); padding: 14px 0 6px; }
.impact-tile { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 18px 16px; text-align: center; min-width: 0; }
.impact-tile + .impact-tile { border-left: 1px solid var(--border); }
/* Big gradient numbers; line-height + padding keep the "g" of "kg" from being clipped. */
.big-num {
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: clamp(34px, 4vw, 46px); font-weight: 800;
  line-height: 1.2; padding-bottom: 0.06em; letter-spacing: -0.015em; white-space: nowrap;
  background: var(--grad-header); -webkit-background-clip: text; background-clip: text; color: transparent;
  font-variant-numeric: tabular-nums;
}
.impact-label { font-size: 15px; font-weight: 600; color: var(--text-primary); }
.impact-sub { display: flex; flex-wrap: wrap; justify-content: center; gap: 2px 10px; font-size: 13px; color: var(--text-muted); }
.impact-foot { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 24px; border-top: 1px dashed var(--border); font-size: 13px; color: var(--text-muted); }
@media (max-width: 700px) {
  .impact-grid { grid-template-columns: 1fr; }
  .impact-tile + .impact-tile { border-left: 0; border-top: 1px solid var(--border); }
}

/* ── Sections ── */
/* One spacing for every block: 32px between sections, 16px between cards in a row */
.section { padding-top: 32px; }
.how-inner { display: flex; flex-direction: column; gap: 18px; }
.section-head { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
.section-title { margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -0.01em; }
.section-sub { margin: 0; font-size: 16px; color: var(--text-muted); }
.steps { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 16px; }
.step { padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
.stage { position: relative; width: 100%; height: 320px; margin-bottom: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: radial-gradient(circle at 50% 55%, rgba(78,110,242,0.10) 0%, var(--bg-hover) 75%); }
.device-phone { width: 150px; height: 300px; border-radius: 24px; border: 7px solid #16182b; overflow: hidden; background: #fff; box-shadow: 0 18px 34px rgba(22,24,43,0.18); box-sizing: border-box; flex-shrink: 0; }
.device-phone img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; }
.device-tablet { width: 86%; aspect-ratio: 16 / 10; border-radius: 12px; border: 7px solid #16182b; overflow: hidden; background: #fff; box-shadow: 0 18px 34px rgba(22,24,43,0.18); box-sizing: border-box; }
.device-tablet img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; }
.tablet-up { align-self: flex-start; margin-top: 30px; }
[data-theme="dark"] .device-phone, [data-theme="dark"] .device-tablet { border-color: #2c3150; }
.scan-duo { position: absolute; inset: 0; }
.scan-duo .device-tablet { position: absolute; left: 4%; top: 30px; width: 82%; }
.phone-scan { position: absolute; right: 4%; bottom: 14px; width: 116px; height: 232px; border-width: 6px; border-radius: 22px; transform: rotate(5deg); background: #0e0f1a; }
.scan-feed { position: absolute; inset: 0; background-position: 50% 30%; background-size: 300% auto; background-repeat: no-repeat; filter: blur(1.5px) brightness(0.55); }
.scan-top { position: absolute; top: 0; left: 0; right: 0; padding: 12px 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 9px; font-weight: 700; color: #fff; text-align: center; background: linear-gradient(rgba(0,0,0,0.55), transparent); }
.scan-vf { position: absolute; left: 50%; top: 48%; width: 76px; height: 76px; transform: translate(-50%, -50%); border-radius: 6px; overflow: hidden; }
.scan-vf img { width: 100%; height: 100%; display: block; }
.vf-c { position: absolute; width: 16px; height: 16px; border: 3px solid #4ade80; }
.vf-c.tl { left: 0; top: 0; border-right: 0; border-bottom: 0; }
.vf-c.tr { right: 0; top: 0; border-left: 0; border-bottom: 0; }
.vf-c.bl { left: 0; bottom: 0; border-right: 0; border-top: 0; }
.vf-c.br { right: 0; bottom: 0; border-left: 0; border-top: 0; }
.vf-laser { position: absolute; left: 6px; right: 6px; top: 10%; height: 2px; background: #4ade80; box-shadow: 0 0 8px #4ade80; animation: vf-laser 2s ease-in-out infinite; }
@keyframes vf-laser { 0%, 100% { top: 10%; } 50% { top: 88%; } }
.step-num { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: var(--grad-header); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 15px; }
.step-title { margin: 4px 0 0; font-size: 18px; font-weight: 700; }
.step-text { margin: 0; font-size: 15px; line-height: 1.55; color: var(--text-secondary); }

/* Rewards card: same width as the sections above and below; copy + "What goes in", then the partner strip */
.rewards-card { padding: 40px; }
.rewards-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start; }
.rewards-copy { display: flex; flex-direction: column; gap: 10px; }
.rewards-copy .section-title { font-size: 34px; }
.rewards-text { margin: 0; font-size: 17px; line-height: 1.55; color: var(--text-muted); }
.goes-in { background: var(--bg-primary); border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 14px; }
.marquee { margin-top: 36px; overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
.marquee-track { list-style: none; margin: 0; padding: 0; display: flex; gap: 12px; width: max-content; animation: marquee 32s linear infinite; }
.marquee:hover .marquee-track { animation-play-state: paused; }
@keyframes marquee { to { transform: translateX(calc(-50% - 6px)); } }
.partner { width: 140px; height: 56px; flex: 0 0 auto; box-sizing: border-box; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; display: flex; align-items: center; justify-content: center; }
.partner img { max-height: 30px; max-width: 100%; object-fit: contain; }
.p-tng img { max-height: 40px; }
.p-grab img { max-height: 22px; }
.p-boost { background: #ea0029; border-color: #ea0029; }
.p-boost img { max-height: 24px; }
.p-grabfood { background: #00b14f; border-color: #00b14f; }
@media (prefers-reduced-motion: reduce) {
  .marquee { -webkit-mask-image: none; mask-image: none; }
  .marquee-track { animation: none; width: auto; flex-wrap: wrap; justify-content: center; padding: 0 16px; }
  .partner-copy { display: none; }
  .vf-laser { animation: none; top: 48%; }
}

/* FAQ */
.faq-card { padding: 40px; display: flex; flex-direction: column; gap: 24px; }
.faq-card .section-title { font-size: 34px; }
/* Two columns of questions; each opens on its own without stretching its neighbour */
.faq-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 16px; align-items: start; }
@media (max-width: 760px) { .faq-grid { grid-template-columns: 1fr; } .faq-card { padding: 24px 20px; } }
.faq-item { background: var(--bg-primary); border: 1px solid var(--border); border-radius: 10px; padding: 0 20px; }
.faq-item[open] { box-shadow: 0 10px 24px rgba(22,24,43,0.06); }
.faq-item summary { min-height: 60px; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-weight: 700; font-size: 16px; cursor: pointer; list-style: none; }
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item summary::after { content: '+'; font-size: 22px; line-height: 1; color: var(--accent-blue); }
.faq-item[open] summary::after { content: '−'; }
.faq-item p { margin: 0 0 18px; font-size: 15px; line-height: 1.55; color: var(--text-muted); }

.card-title { margin: 0; font-size: 22px; font-weight: 800; }
.card-text { margin: 0; font-size: 15px; line-height: 1.55; color: var(--text-secondary); }
.chips { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 10px; }
.chip-ok { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 14px; border-radius: 999px; background: rgba(34,197,94,0.12); color: #15803d; font-size: 15px; font-weight: 700; }
[data-theme="dark"] .chip-ok { color: #4ade80; }
.not-accepted { margin-top: auto; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 14px; color: var(--text-muted); }
.chip-no { padding: 4px 10px; border-radius: 999px; background: var(--bg-hover); color: var(--text-secondary); }
.see-rewards { margin-top: 8px; align-self: flex-start; min-height: 44px; display: inline-flex; align-items: center; gap: 6px; color: var(--accent-blue); font-size: 15px; font-weight: 700; text-decoration: none; }
.see-rewards:hover { text-decoration: underline; }

/* ── CTA ── */
.cta { border-radius: 24px; background: var(--grad-header); color: #fff; display: grid; grid-template-columns: 1.3fr 0.7fr; align-items: center; overflow: hidden; }
.cta-copy { min-width: 0; padding: 48px; display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
.cta-title { margin: 0; font-size: 34px; font-weight: 800; line-height: 1.15; }
.cta-text { margin: 0 0 14px; font-size: 17px; opacity: 0.95; }
.btn-cta { min-height: 52px; padding: 0 24px; border: 0; border-radius: 10px; background: #fff; color: #3b5bdb; display: inline-flex; align-items: center; gap: 10px; font: inherit; font-size: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 24px rgba(15,23,42,0.18); }
.btn-cta:hover { filter: brightness(0.97); }
.cta-phone { width: 200px; height: 400px; border-width: 9px; border-radius: 30px; margin: 28px auto -120px; transform: rotate(-6deg); }
@media (max-width: 900px) {
  .rewards-grid, .cta { grid-template-columns: 1fr; }
  .cta-phone { display: none; }
  .cta-copy { padding: 28px; }
}

/* ── Footer ── */
.foot { padding-top: 32px; padding-bottom: 28px; }
.foot-inner { padding-top: 32px; border-top: 1px solid var(--border); display: flex; flex-direction: column; align-items: center; gap: 14px; text-align: center; }
.foot-logos { display: flex; align-items: center; justify-content: center; gap: 28px; }
.logo-dsme { height: 44px; width: auto; }
/* Dark mode: the navy DSME logo disappears on a dark page, so it
   sits on a small white plate (the artwork itself stays unchanged). */
[data-theme="dark"] .foot-logos { background: #fff; padding: 10px 20px; border-radius: 10px; }
.foot-tagline { font-size: 14px; color: var(--text-muted); }
.foot-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 0 24px; font-size: 14px; }
.foot-links a { min-height: 44px; display: inline-flex; align-items: center; color: var(--accent-blue); font-weight: 600; text-decoration: none; }
.foot-links a.muted { color: var(--text-muted); font-weight: 400; }

@media (max-width: 480px) {
  .hero { padding: 16px 16px 116px; }
  .wrap-pad { padding-left: 16px; padding-right: 16px; }
  .hero-inner { gap: 36px; }
  .hero-text { font-size: 16px; }
  .section-title, .rewards-copy .section-title { font-size: 26px; }
  .cta-copy { padding: 24px; }
  .cta-title { font-size: 24px; }
  .btn-cta { width: 100%; justify-content: center; }
  .stage { height: 300px; }
  .partner { width: 120px; height: 50px; }
  .rewards-card { padding: 24px 20px; }
}

/* "Ultra RVM" is longer than the old "RVM": keep the logo on one line
   next to the language and Login buttons on narrow phones. */
@media (max-width: 400px) {
  .brand-name { font-size: 16px; }
  .brand { gap: 6px; }
  .brand-mark { width: 34px; height: 34px; }
  .nav-actions { gap: 6px; }
  .nav-login { padding: 0 12px; font-size: 13px; }
}
@media (max-width: 340px) {
  .brand-name { font-size: 15px; }
  .brand-mark { display: none; }
  .nav-actions { gap: 4px; }
  .nav-round { min-width: 40px; height: 40px; }
  .nav-login { height: 40px; padding: 0 10px; }
}
</style>
