import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

// Views
import LandingView        from '@/views/LandingView.vue'
import LoginView          from '@/views/LoginView.vue'
import RegisterView       from '@/views/RegisterView.vue'
import DashboardView      from '@/views/DashboardView.vue'
import ScanView           from '@/views/ScanView.vue'
import RvmSessionView     from '@/views/RvmSessionView.vue'
import SessionSummaryView from '@/views/SessionSummaryView.vue'
import AdminView          from '@/views/AdminView.vue'
import UserSettingsView   from '@/views/UserSettingsView.vue'
import NotFoundView       from '@/views/NotFoundView.vue'
import KioskLandingView      from '@/views/KioskLandingView.vue'
import KioskQrView           from '@/views/KioskQrView.vue'
import GoogleCallbackView    from '@/views/GoogleCallbackView.vue'
import WelcomeView           from '@/views/WelcomeView.vue'
import GoodbyeView           from '@/views/GoodbyeView.vue'

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
