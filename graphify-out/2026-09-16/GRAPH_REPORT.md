# Graph Report - RVM  (2026-09-16)

## Corpus Check
- 197 files · ~110,497 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1444 nodes · 2764 edges · 105 communities (60 shown, 25 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 51 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f4af2d3c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- BinCollectionRequested
- Composer Configuration
- RvmSessionView.vue
- FrontEnd/package.json
- Illuminate\Database\Migrations\Migration
- UserSettingsView.vue
- TestCase
- Illuminate\Http\Request
- RewardItem
- PointsHistory
- auth.js
- ScanView.vue
- AdminController.php
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- Transaction
- AuthController
- User
- AdminPaginationTest
- fetchTabData
- app.py
- DetectionLog
- RvmMachine
- KioskQrView.vue
- LoginView.vue
- ActivityView.vue
- Closure
- router/index.js
- Rewards Catalog View
- normalizeMachine
- showToast
- SessionSummaryView.vue
- Illuminate\Http\JsonResponse
- Backend Build Config
- DashboardView.vue
- AdminPagination.vue
- App & Broadcast Providers
- dependencies
- TransactionController.php
- useIdleLogout
- scrollReveal.js
- Authenticate.php
- Laravel\Sanctum\Sanctum
- App.vue
- detectionDateRange.js
- YOLO Servo Test Script
- Event Service Provider
- vue
- AdminBinCollectionEmailTest
- Console Kernel Scheduling
- AdminCachingTest
- RecyclingSession
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- devDependencies
- useLenisScroll.js
- HTTP Kernel
- Cookie Encryption Middleware
- Maintenance Mode Middleware
- Trim Strings Middleware
- Trusted Proxies Middleware
- Signed URL Middleware
- CSRF Verification Middleware
- Database Connection Helper
- RewardCatalogTest
- saveDetectionReview
- isFresh
- vitest
- PointsHistoryLocaleTest.php
- ExpireStaleSessionsTest.php
- RewardConfigService
- AdminEmailFormalReportTest
- toDatetimeLocalValue
- fmtTime
- DetectionLogTest.php
- FrontEnd/vite.config.js
- setRewardImageFile
- WeighAwardsConfiguredPointsTest.php
- scripts

## God Nodes (most connected - your core abstractions)
1. `User` - 86 edges
2. `TestCase` - 59 edges
3. `RvmMachine` - 56 edges
4. `RecyclingSession` - 47 edges
5. `RewardItem` - 44 edges
6. `AdminController` - 39 edges
7. `DetectionLog` - 33 edges
8. `fetchTabData()` - 32 edges
9. `Transaction` - 27 edges
10. `vue` - 25 edges

## Surprising Connections (you probably didn't know these)
- `fetchTabData()` --indirect_call--> `normalizeMachine()`  [INFERRED]
  FrontEnd/src/views/AdminView.vue → FrontEnd/src/utils/admin/normalizeMachine.js
- `AdminController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AdminController.php → BackEnd/app/Http/Controllers/Controller.php
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AuthController.php → BackEnd/app/Http/Controllers/Controller.php
- `RewardController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/RewardController.php → BackEnd/app/Http/Controllers/Controller.php
- `SessionController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/SessionController.php → BackEnd/app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (105 total, 25 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (117): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, auth, barChartData, barChartOptions (+109 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.14
Nodes (14): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue (+6 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (41): MATERIAL_ICON_PATHS, materialIconSvg(), aiConfidence, aiDetected, annotatedImageDataUrl, auth, autoStartFlow(), cameraCountdown (+33 more)

### Community 4 - "FrontEnd/package.json"
Cohesion: 0.14
Nodes (13): axios, vite, name, private, version, autoprefixer, chart.js, jsqr (+5 more)

### Community 5 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.06
Nodes (4): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Schema

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.05
Nodes (32): activeTab, auth, cancelEditPassword(), changePassword(), confirmDelete, conversionRate, deletingAccount, editingPassword (+24 more)

### Community 7 - "TestCase"
Cohesion: 0.12
Nodes (10): AdminRedemptionsListTest, ExampleTest, GoogleRedirectFrontendOriginTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, RewardHistoryTest, SessionControllerLocaleTest, TestCase (+2 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (3): AdminController, Illuminate\Contracts\Http\Kernel, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.07
Nodes (9): AdminLog, QrSession, RewardItem, RewardRedemption, AdminRewardItemsCrudTest, RewardItemAvailabilityTest, RewardRedeemTest, Illuminate\Database\Eloquent\Model (+1 more)

### Community 10 - "PointsHistory"
Cohesion: 0.13
Nodes (4): RewardController, UserController, PointsHistory, Illuminate\Support\Facades\Route

### Community 11 - "auth.js"
Cohesion: 0.11
Nodes (19): ACTIVITY_EVENTS, readLastActivity(), GoodbyeView(), GoogleCallbackView(), registerClearAuth(), useAuthStore, resolveCachedPoints(), auth (+11 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.09
Nodes (25): activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error, extractToken() (+17 more)

### Community 13 - "AdminController.php"
Cohesion: 0.08
Nodes (20): CarbonService, FormalReportService, UserFactory, CarbonServiceTest, ExampleTest, FormalReportServiceTest, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Factories\Factory (+12 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (36): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+28 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (18): auth, clearDraft(), error, form, handleRegister(), handleVerifyOtp(), loading, otpCode (+10 more)

### Community 16 - "Transaction"
Cohesion: 0.24
Nodes (3): Transaction, AdminExportExcelTest, PhpOffice\PhpSpreadsheet\IOFactory

### Community 17 - "AuthController"
Cohesion: 0.15
Nodes (3): AuthController, FonnteService, Illuminate\Http\RedirectResponse

### Community 18 - "User"
Cohesion: 0.15
Nodes (7): User, AdminCarbonStatsTest, AuthControllerLocaleTest, TransactionControllerLocaleTest, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 20 - "fetchTabData"
Cohesion: 0.09
Nodes (23): changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage(), fetchTabData(), filterTransactions() (+15 more)

### Community 21 - "app.py"
Cohesion: 0.10
Nodes (24): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+16 more)

### Community 23 - "RvmMachine"
Cohesion: 0.16
Nodes (7): Controller, MachineController, QrController, RvmMachine, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

### Community 24 - "KioskQrView.vue"
Cohesion: 0.15
Nodes (21): KioskQrView(), clearIntervals(), currentToken, expiresInSec, generateQr(), handleExpiry(), loadingQr, qrSvgSrc (+13 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.12
Nodes (11): LoginView(), auth, error, form, loading, loginMethod, otpSent, route (+3 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.20
Nodes (8): ActivityView(), mergeActivityFeed(), feed, hasLoadError, loading, pointsHistoryFailed, redemptionsFailed, sessionsFailed

### Community 27 - "Closure"
Cohesion: 0.07
Nodes (19): ExpireStaleSessions, AdminMiddleware, EnforceIdleTimeout, KioskAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, RouteServiceProvider, EnforceIdleTimeoutTest (+11 more)

### Community 28 - "router/index.js"
Cohesion: 0.10
Nodes (20): AdminView(), LandingView(), NotFoundView(), RegisterView(), routes, RvmSessionView(), ScanView(), UserSettingsView() (+12 more)

### Community 29 - "Rewards Catalog View"
Cohesion: 0.14
Nodes (15): RewardsView(), activeCategory, auth, categories, confirmingItem, fetchItems(), filteredItems, items (+7 more)

### Community 30 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 31 - "showToast"
Cohesion: 0.15
Nodes (17): addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize(), deleteMachine(), deleteRewardItem(), deleteUser(), exportDetectionLogsCsv() (+9 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.14
Nodes (14): SessionSummaryView(), setKioskToken(), auth, earnedPoints, finalPoints, goHome(), isUnknownTransaction(), route (+6 more)

### Community 33 - "Illuminate\Http\JsonResponse"
Cohesion: 0.25
Nodes (3): TransactionController, Illuminate\Http\JsonResponse, self

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.12
Nodes (12): DashboardView(), api, storedKioskToken, readKioskState(), useRvmStore, auth, binTypes, loadingMachines (+4 more)

### Community 36 - "AdminPagination.vue"
Cohesion: 0.22
Nodes (11): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+3 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "TransactionController.php"
Cohesion: 0.17
Nodes (3): AiService, GuestCarbonTest, Illuminate\Support\Facades\Cache

### Community 40 - "useIdleLogout"
Cohesion: 0.42
Nodes (9): useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire(), onActivity() (+1 more)

### Community 43 - "Laravel\Sanctum\Sanctum"
Cohesion: 0.21
Nodes (3): AdminRewardConfigTest, Illuminate\Support\Facades\Mail, Laravel\Sanctum\Sanctum

### Community 44 - "App.vue"
Cohesion: 0.14
Nodes (12): auth, route, showAppNav, showToast(), theme, toastState, toggleTheme(), app (+4 more)

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "YOLO Servo Test Script"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "vue"
Cohesion: 0.18
Nodes (11): activeKey, route, KioskLandingView(), NAV_ITEMS, resolveActiveNavKey(), machineLocation, machineName, route (+3 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "RecyclingSession"
Cohesion: 0.15
Nodes (7): SessionController, RecyclingSession, Carbon\Carbon, Illuminate\Support\Facades\Validator, Illuminate\Support\Str, Laravel\Socialite\Facades\Socialite, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 53 - "Exception Handler"
Cohesion: 0.50
Nodes (3): Handler, Illuminate\Foundation\Exceptions\Handler, Throwable

### Community 54 - "Logging Configuration"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 55 - "Test Application Bootstrap"
Cohesion: 0.50
Nodes (3): CreatesApplication, Illuminate\Contracts\Console\Kernel, Illuminate\Foundation\Application

### Community 56 - "BackEnd/README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 60 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 61 - "useLenisScroll.js"
Cohesion: 0.48
Nodes (6): NO_LENIS_ROUTES, start(), stop(), sync(), useLenisScroll(), lenis

### Community 72 - "saveDetectionReview"
Cohesion: 0.22
Nodes (11): closeIncorrectReview(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 93 - "vitest"
Cohesion: 0.19
Nodes (8): flattenKeys(), __dirname, messages, buildRewardUpdatePayload(), resolveLoadingFlag(), TAB_LOADING_FLAG, updateReward(), vitest

### Community 95 - "ExpireStaleSessionsTest.php"
Cohesion: 0.31
Nodes (3): ExpireStaleSessionsTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan

### Community 99 - "fmtTime"
Cohesion: 0.50
Nodes (4): fmtTime(), formatDate(), timeOnly(), updateClock()

### Community 101 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 102 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

### Community 104 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

## Knowledge Gaps
- **421 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+416 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 625 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **25 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Migrations\Migration`, `TestCase`, `Illuminate\Http\Request`, `RewardItem`, `AdminController.php`, `Transaction`, `AuthController`, `AdminPaginationTest`, `DetectionLog`, `Closure`, `Laravel\Sanctum\Sanctum`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `RecyclingSession`, `RewardCatalogTest`, `PointsHistoryLocaleTest.php`, `ExpireStaleSessionsTest.php`, `AdminEmailFormalReportTest`, `DetectionLogTest.php`, `WeighAwardsConfiguredPointsTest.php`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `Illuminate\Database\Migrations\Migration`, `RewardItem`, `AdminController.php`, `Transaction`, `User`, `AdminPaginationTest`, `DetectionLog`, `Closure`, `TransactionController.php`, `Laravel\Sanctum\Sanctum`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `Test Application Bootstrap`, `RewardCatalogTest`, `PointsHistoryLocaleTest.php`, `ExpireStaleSessionsTest.php`, `AdminEmailFormalReportTest`, `DetectionLogTest.php`, `WeighAwardsConfiguredPointsTest.php`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `AdminView.vue`, `SessionSummaryView.vue`, `DashboardView.vue`, `FrontEnd/package.json`, `AdminPagination.vue`, `RvmSessionView.vue`, `UserSettingsView.vue`, `auth.js`, `App.vue`, `ScanView.vue`, `RegisterView.vue`, `Rewards Catalog View`, `KioskQrView.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `useLenisScroll.js`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _421 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.014925373134328358 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.14130434782608695 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._