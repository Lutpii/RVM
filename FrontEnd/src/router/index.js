import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

// Views (lazy-loaded — each becomes its own chunk, fetched only when visited)
const LandingView        = () => import('@/views/LandingView.vue')
const LoginView          = () => import('@/views/LoginView.vue')
const RegisterView       = () => import('@/views/RegisterView.vue')
const DashboardView      = () => import('@/views/DashboardView.vue')
const ScanView           = () => import('@/views/ScanView.vue')
const RvmSessionView     = () => import('@/views/RvmSessionView.vue')
const SessionSummaryView = () => import('@/views/SessionSummaryView.vue')
const AdminView          = () => import('@/views/AdminView.vue')
const UserSettingsView   = () => import('@/views/UserSettingsView.vue')
const NotFoundView       = () => import('@/views/NotFoundView.vue')
const KioskLandingView      = () => import('@/views/KioskLandingView.vue')
const KioskQrView           = () => import('@/views/KioskQrView.vue')
const GoogleCallbackView    = () => import('@/views/GoogleCallbackView.vue')
const WelcomeView           = () => import('@/views/WelcomeView.vue')
const GoodbyeView           = () => import('@/views/GoodbyeView.vue')

const routes = [
  // ── Kiosk routes (RVM machine side — no auth required) ──────────────────
  {
    path: '/kiosk/:machineCode',
    name: 'kiosk-landing',
    component: KioskLandingView,
    meta: { title: 'RVM Kiosk' },
  },
  {
    path: '/kiosk/:machineCode/qr',
    name: 'kiosk-qr',
    component: KioskQrView,
    meta: { title: 'Scan QR - RVM Kiosk' },
  },
  {
    path: '/kiosk/:machineCode/session',
    name: 'kiosk-session',
    component: RvmSessionView,
    meta: { title: 'Guest Recycling - RVM Kiosk' },
  },
  {
    path: '/kiosk/:machineCode/summary',
    name: 'kiosk-summary',
    component: SessionSummaryView,
    meta: { title: 'Session Summary - RVM Kiosk' },
  },

  // ── User (phone) routes ──────────────────────────────────────────────────
  {
    path: '/',
    name: 'landing',
    component: LandingView,
    meta: { title: 'RVM - Smart Recycling' },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { title: 'Login - RVM' },
  },
  {
    path: '/auth/callback',
    name: 'google-callback',
    component: GoogleCallbackView,
    meta: { title: 'Signing in...' },
  },
  {
    // No requiresAuth: also used by the kiosk after a phone-scanned login,
    // where the kiosk's own browser session only holds a kiosk_token, not a
    // Sanctum session (see KioskQrView.vue's scanned-QR handler).
    path: '/welcome',
    name: 'welcome',
    component: WelcomeView,
    meta: { title: 'Welcome - RVM' },
  },
  {
    // No requiresAuth — same reasoning as /welcome above: reached from the
    // kiosk's End Session button for both guests and phone-logged-in users.
    path: '/thank-you',
    name: 'thank-you',
    component: GoodbyeView,
    meta: { title: 'Thank You - RVM' },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { title: 'Register - RVM' },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { title: 'Dashboard - RVM', requiresAuth: true },
  },
  {
    path: '/scan',
    name: 'scan',
    component: ScanView,
    meta: { title: 'Scan QR - RVM', requiresAuth: true },
  },
  {
    path: '/session',
    name: 'session',
    component: RvmSessionView,
    meta: { title: 'Recycling Session - RVM', requiresAuth: true },
  },
  {
    path: '/session/summary',
    name: 'session-summary',
    component: SessionSummaryView,
    meta: { title: 'Session Summary - RVM', requiresAuth: true },
  },
  {
    path: '/settings',
    name: 'settings',
    component: UserSettingsView,
    meta: { title: 'Settings - RVM', requiresAuth: true },
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminView,
    meta: { title: 'Admin Panel - RVM', requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// Navigation guards
router.beforeEach((to, from, next) => {
  document.title = to.meta.title || 'RVM'

  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guestOnly && authStore.isLoggedIn) {
    return next({ name: 'dashboard' })
  }

  if (to.meta.requiresAdmin && authStore.user?.role !== 'admin') {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
