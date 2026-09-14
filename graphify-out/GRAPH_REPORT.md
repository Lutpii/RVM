# Graph Report - RVM  (2026-09-14)

## Corpus Check
- 197 files · ~106,720 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1424 nodes · 2724 edges · 100 communities (53 shown, 27 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 48 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2af60ced`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- AdminController.php
- Composer Configuration
- RvmSessionView.vue
- FrontEnd/package.json
- Illuminate\Database\Migrations\Migration
- UserSettingsView.vue
- TestCase
- Illuminate\Http\Request
- AdminExportExcelTest
- Illuminate\Http\JsonResponse
- auth.js
- ScanView.vue
- RewardItem
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- AdminCarbonStatsTest
- AuthController
- User
- AdminPaginationTest
- fetchTabData
- Hardware Control App
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
- TransactionController
- Backend Build Config
- DashboardView.vue
- AdminPagination.vue
- App & Broadcast Providers
- AdminRewardConfigTest
- TransactionController.php
- vue-i18n
- main.js
- Authenticate.php
- RewardItemAvailabilityTest
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
- AdminEmailFormalReportTest
- WeighAwardsConfiguredPointsTest.php
- HTTP Kernel
- Cookie Encryption Middleware
- Maintenance Mode Middleware
- Trim Strings Middleware
- Trusted Proxies Middleware
- Signed URL Middleware
- CSRF Verification Middleware
- Database Connection Helper
- Controller
- saveDetectionReview
- isFresh
- vitest
- Transaction
- ExpireStaleSessionsTest.php
- scrollReveal.js
- toDatetimeLocalValue
- fmtTime
- setRewardImageFile

## God Nodes (most connected - your core abstractions)
1. `User` - 85 edges
2. `TestCase` - 59 edges
3. `RvmMachine` - 55 edges
4. `RecyclingSession` - 44 edges
5. `RewardItem` - 44 edges
6. `AdminController` - 39 edges
7. `DetectionLog` - 33 edges
8. `fetchTabData()` - 32 edges
9. `Transaction` - 26 edges
10. `vue` - 25 edges

## Surprising Connections (you probably didn't know these)
- `fetchTabData()` --indirect_call--> `normalizeMachine()`  [INFERRED]
  FrontEnd/src/views/AdminView.vue → FrontEnd/src/utils/admin/normalizeMachine.js
- `AdminController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AdminController.php → BackEnd/app/Http/Controllers/Controller.php
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AuthController.php → BackEnd/app/Http/Controllers/Controller.php
- `MachineController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/MachineController.php → BackEnd/app/Http/Controllers/Controller.php
- `QrController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/QrController.php → BackEnd/app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (100 total, 27 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (117): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, auth, barChartData, barChartOptions (+109 more)

### Community 1 - "AdminController.php"
Cohesion: 0.06
Nodes (28): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, RewardConfigService, UserFactory (+20 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.06
Nodes (39): aiConfidence, aiDetected, annotatedImageDataUrl, auth, autoStartFlow(), cameraCountdown, cameraMode, cameraStreamUrl (+31 more)

### Community 4 - "FrontEnd/package.json"
Cohesion: 0.05
Nodes (42): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+34 more)

### Community 5 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.06
Nodes (4): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Schema

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.05
Nodes (33): UserSettingsView(), activeTab, auth, cancelEditPassword(), changePassword(), confirmDelete, conversionRate, deletingAccount (+25 more)

### Community 7 - "TestCase"
Cohesion: 0.09
Nodes (11): AdminRedemptionsListTest, DetectionLogTest, ExampleTest, GoogleRedirectFrontendOriginTest, GuestCarbonTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, SessionControllerLocaleTest (+3 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.15
Nodes (3): AdminController, Illuminate\Contracts\Http\Kernel, Illuminate\Http\Request

### Community 10 - "Illuminate\Http\JsonResponse"
Cohesion: 0.12
Nodes (4): RewardController, UserController, PointsHistory, Illuminate\Http\JsonResponse

### Community 11 - "auth.js"
Cohesion: 0.16
Nodes (13): GoogleCallbackView(), api, registerClearAuth(), storedKioskToken, useAuthStore, readKioskState(), useRvmStore, resolveCachedPoints() (+5 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.09
Nodes (25): activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error, extractToken() (+17 more)

### Community 13 - "RewardItem"
Cohesion: 0.05
Nodes (17): AdminLog, RewardItem, RewardRedemption, CarbonService, FormalReportService, AdminRewardItemsCrudTest, RewardCatalogTest, RewardHistoryTest (+9 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (36): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+28 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (19): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleVerifyOtp(), loading (+11 more)

### Community 17 - "AuthController"
Cohesion: 0.15
Nodes (6): AuthController, FonnteService, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Validator, Laravel\Socialite\Facades\Socialite

### Community 18 - "User"
Cohesion: 0.18
Nodes (6): User, AuthControllerLocaleTest, TransactionControllerLocaleTest, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 20 - "fetchTabData"
Cohesion: 0.09
Nodes (23): changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage(), fetchTabData(), filterTransactions() (+15 more)

### Community 21 - "Hardware Control App"
Cohesion: 0.20
Nodes (18): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+10 more)

### Community 23 - "RvmMachine"
Cohesion: 0.15
Nodes (5): MachineController, QrController, QrSession, RvmMachine, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 24 - "KioskQrView.vue"
Cohesion: 0.16
Nodes (20): clearIntervals(), currentToken, expiresInSec, generateQr(), handleExpiry(), loadingQr, qrSvgSrc, returnToKiosk() (+12 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.12
Nodes (11): LoginView(), auth, error, form, loading, loginMethod, otpSent, route (+3 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.20
Nodes (8): ActivityView(), mergeActivityFeed(), feed, hasLoadError, loading, pointsHistoryFailed, redemptionsFailed, sessionsFailed

### Community 27 - "Closure"
Cohesion: 0.07
Nodes (20): ExpireStaleSessions, AdminMiddleware, EnforceIdleTimeout, KioskAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, RouteServiceProvider, EnforceIdleTimeoutTest (+12 more)

### Community 28 - "router/index.js"
Cohesion: 0.08
Nodes (26): AdminView(), GoodbyeView(), KioskQrView(), LandingView(), NotFoundView(), routes, RvmSessionView(), ScanView() (+18 more)

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

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.13
Nodes (10): DashboardView(), MATERIAL_ICON_PATHS, materialIconSvg(), auth, binTypes, loadingMachines, machines, mapContainer (+2 more)

### Community 36 - "AdminPagination.vue"
Cohesion: 0.22
Nodes (11): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+3 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 40 - "vue-i18n"
Cohesion: 0.26
Nodes (12): ACTIVITY_EVENTS, readLastActivity(), useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm() (+4 more)

### Community 41 - "main.js"
Cohesion: 0.33
Nodes (5): app, i18n, pinia, router, pinia

### Community 44 - "App.vue"
Cohesion: 0.17
Nodes (13): auth, route, showAppNav, showToast(), theme, toastState, toggleTheme(), NO_LENIS_ROUTES (+5 more)

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
Cohesion: 0.26
Nodes (3): SessionController, RecyclingSession, Carbon\Carbon

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

### Community 70 - "Controller"
Cohesion: 0.70
Nodes (4): Controller, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

### Community 72 - "saveDetectionReview"
Cohesion: 0.22
Nodes (11): closeIncorrectReview(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 93 - "vitest"
Cohesion: 0.19
Nodes (8): flattenKeys(), __dirname, messages, buildRewardUpdatePayload(), resolveLoadingFlag(), TAB_LOADING_FLAG, updateReward(), vitest

### Community 94 - "Transaction"
Cohesion: 0.15
Nodes (5): Transaction, PointsHistoryLocaleTest, Illuminate\Support\Facades\Mail, Laravel\Sanctum\Sanctum, PhpOffice\PhpSpreadsheet\IOFactory

### Community 95 - "ExpireStaleSessionsTest.php"
Cohesion: 0.31
Nodes (3): ExpireStaleSessionsTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan

### Community 99 - "fmtTime"
Cohesion: 0.50
Nodes (4): fmtTime(), formatDate(), timeOnly(), updateClock()

### Community 102 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

## Knowledge Gaps
- **420 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+415 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 616 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **27 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AdminController.php`, `Illuminate\Database\Migrations\Migration`, `TestCase`, `Illuminate\Http\Request`, `AdminExportExcelTest`, `Illuminate\Http\JsonResponse`, `RewardItem`, `AdminCarbonStatsTest`, `AuthController`, `AdminPaginationTest`, `DetectionLog`, `Closure`, `AdminRewardConfigTest`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `RecyclingSession`, `AdminEmailFormalReportTest`, `WeighAwardsConfiguredPointsTest.php`, `Transaction`, `ExpireStaleSessionsTest.php`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `Illuminate\Database\Migrations\Migration`, `AdminRewardConfigTest`, `AdminExportExcelTest`, `RewardItemAvailabilityTest`, `RewardItem`, `AdminCarbonStatsTest`, `AdminBinCollectionEmailTest`, `User`, `AdminCachingTest`, `AdminPaginationTest`, `DetectionLog`, `Test Application Bootstrap`, `Closure`, `AdminEmailFormalReportTest`, `WeighAwardsConfiguredPointsTest.php`, `Transaction`, `ExpireStaleSessionsTest.php`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `AdminController.php`, `TransactionController.php`, `Illuminate\Http\Request`, `AdminExportExcelTest`, `Illuminate\Http\JsonResponse`, `TestCase`, `RewardItem`, `AdminCarbonStatsTest`, `AdminBinCollectionEmailTest`, `User`, `AdminPaginationTest`, `RecyclingSession`, `Closure`, `AdminEmailFormalReportTest`, `WeighAwardsConfiguredPointsTest.php`, `Transaction`, `ExpireStaleSessionsTest.php`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _420 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.014925373134328358 - nodes in this community are weakly interconnected._
- **Should `AdminController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.058069381598793365 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._