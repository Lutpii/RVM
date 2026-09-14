# Graph Report - RVM  (2026-09-14)

## Corpus Check
- 191 files · ~103,182 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1364 nodes · 2600 edges · 97 communities (52 shown, 25 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 47 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d77f019e`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- BinCollectionRequested
- Composer Configuration
- RvmSessionView.vue
- FrontEnd/package.json
- Illuminate\Database\Migrations\Migration
- User Settings View
- TestCase
- Illuminate\Http\Request
- AdminController.php
- Controller
- auth.js
- ScanView.vue
- RewardItem
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- RvmMachine
- AuthController
- User Model & Reports
- Admin Pagination Tests
- Admin Table Pagination Logic
- Hardware Control App
- DetectionLog
- RecyclingSession
- KioskQrView.vue
- LoginView.vue
- api.js
- Closure
- router/index.js
- Rewards Catalog View
- vitest
- showToast
- SessionSummaryView.vue
- TransactionController
- Backend Build Config
- DashboardView.vue
- QrSession
- App & Broadcast Providers
- AdminRewardConfigTest
- AuthController.php
- useIdleLogout
- i18n Setup & Tests
- Authenticate.php
- Reward Availability Tests
- App.vue
- Machine Data Normalization
- YOLO Servo Test Script
- Event Service Provider
- vue
- AdminBinCollectionEmailTest
- Console Kernel Scheduling
- Admin Caching Tests
- Points History Locale Tests
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- WeighAwardsConfiguredPointsTest.php
- Time Formatting Utilities
- HTTP Kernel
- Cookie Encryption Middleware
- Maintenance Mode Middleware
- Trim Strings Middleware
- Trusted Proxies Middleware
- Signed URL Middleware
- CSRF Verification Middleware
- Database Connection Helper
- EnforceIdleTimeoutTest.php
- Review Eligibility Logic
- ExpireStaleSessionsTest.php
- scrollReveal.js
- isFresh
- toDatetimeLocalValue
- setRewardImageFile

## God Nodes (most connected - your core abstractions)
1. `User` - 85 edges
2. `TestCase` - 59 edges
3. `RvmMachine` - 55 edges
4. `RecyclingSession` - 44 edges
5. `RewardItem` - 44 edges
6. `AdminController` - 37 edges
7. `fetchTabData()` - 27 edges
8. `Transaction` - 26 edges
9. `AdminPaginationTest` - 24 edges
10. `vue` - 24 edges

## Surprising Connections (you probably didn't know these)
- `fetchTabData()` --indirect_call--> `normalizeMachine()`  [INFERRED]
  FrontEnd/src/views/AdminView.vue → FrontEnd/src/utils/admin/normalizeMachine.js
- `AdminController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AdminController.php → BackEnd/app/Http/Controllers/Controller.php
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AuthController.php → BackEnd/app/Http/Controllers/Controller.php
- `AuthController` --references--> `FonnteService`  [EXTRACTED]
  BackEnd/app/Http/Controllers/AuthController.php → BackEnd/app/Services/FonnteService.php
- `QrController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/QrController.php → BackEnd/app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (97 total, 25 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.02
Nodes (104): activeMachines, activeTab, adminMachines, adminRedemptions, auth, barChartData, barChartOptions, binTypes (+96 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.14
Nodes (14): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue (+6 more)

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
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 6 - "User Settings View"
Cohesion: 0.05
Nodes (32): activeTab, auth, cancelEditPassword(), changePassword(), confirmDelete, conversionRate, deletingAccount, editingPassword (+24 more)

### Community 7 - "TestCase"
Cohesion: 0.10
Nodes (14): AdminRedemptionsListTest, ExampleTest, GoogleRedirectFrontendOriginTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, RewardHistoryTest, SessionControllerLocaleTest, TestCase (+6 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.17
Nodes (3): AdminController, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 9 - "AdminController.php"
Cohesion: 0.11
Nodes (13): CarbonService, FormalReportService, CarbonServiceTest, ExampleTest, FormalReportServiceTest, Illuminate\Database\Eloquent\Builder, PhpOffice\PhpSpreadsheet\Spreadsheet, PhpOffice\PhpSpreadsheet\Style\Alignment (+5 more)

### Community 10 - "Controller"
Cohesion: 0.13
Nodes (7): Controller, MachineController, UserController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller, Illuminate\Support\Facades\Route

### Community 11 - "auth.js"
Cohesion: 0.11
Nodes (19): ACTIVITY_EVENTS, readLastActivity(), GoodbyeView(), GoogleCallbackView(), registerClearAuth(), useAuthStore, resolveCachedPoints(), auth (+11 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.09
Nodes (25): activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error, extractToken() (+17 more)

### Community 13 - "RewardItem"
Cohesion: 0.07
Nodes (9): RewardController, AdminLog, PointsHistory, RewardItem, RewardRedemption, AdminRewardItemsCrudTest, RewardCatalogTest, RewardRedeemTest (+1 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (36): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+28 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (18): auth, clearDraft(), error, form, handleRegister(), handleVerifyOtp(), loading, otpCode (+10 more)

### Community 16 - "RvmMachine"
Cohesion: 0.15
Nodes (5): RvmMachine, Transaction, AdminCarbonStatsTest, AdminExportExcelTest, PhpOffice\PhpSpreadsheet\IOFactory

### Community 18 - "User Model & Reports"
Cohesion: 0.14
Nodes (7): User, AdminEmailFormalReportTest, AuthControllerLocaleTest, TransactionControllerLocaleTest, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 20 - "Admin Table Pagination Logic"
Cohesion: 0.10
Nodes (21): changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage(), fetchTabData(), filterDetection() (+13 more)

### Community 21 - "Hardware Control App"
Cohesion: 0.20
Nodes (18): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+10 more)

### Community 23 - "RecyclingSession"
Cohesion: 0.20
Nodes (3): SessionController, RecyclingSession, DetectionLogTest

### Community 24 - "KioskQrView.vue"
Cohesion: 0.16
Nodes (20): clearIntervals(), currentToken, expiresInSec, generateQr(), handleExpiry(), loadingQr, qrSvgSrc, returnToKiosk() (+12 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.12
Nodes (11): LoginView(), auth, error, form, loading, loginMethod, otpSent, route (+3 more)

### Community 26 - "api.js"
Cohesion: 0.15
Nodes (11): ActivityView(), api, storedKioskToken, readKioskState(), useRvmStore, feed, hasLoadError, loading (+3 more)

### Community 27 - "Closure"
Cohesion: 0.13
Nodes (11): AdminMiddleware, EnforceIdleTimeout, KioskAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, Closure, Illuminate\Support\Facades\App, Illuminate\Support\Facades\Auth (+3 more)

### Community 28 - "router/index.js"
Cohesion: 0.09
Nodes (21): AdminView(), KioskQrView(), LandingView(), NotFoundView(), RegisterView(), routes, RvmSessionView(), ScanView() (+13 more)

### Community 29 - "Rewards Catalog View"
Cohesion: 0.14
Nodes (15): RewardsView(), activeCategory, auth, categories, confirmingItem, fetchItems(), filteredItems, items (+7 more)

### Community 30 - "vitest"
Cohesion: 0.17
Nodes (7): mergeActivityFeed(), paginationLabel(), buildRewardUpdatePayload(), resolveLoadingFlag(), TAB_LOADING_FLAG, updateReward(), vitest

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

### Community 36 - "QrSession"
Cohesion: 0.33
Nodes (3): QrController, QrSession, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 39 - "AuthController.php"
Cohesion: 0.06
Nodes (19): RouteServiceProvider, AiService, FonnteService, UserFactory, GuestCarbonTest, Carbon\Carbon, Illuminate\Cache\RateLimiting\Limit, Illuminate\Database\Eloquent\Factories\Factory (+11 more)

### Community 40 - "useIdleLogout"
Cohesion: 0.42
Nodes (9): useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire(), onActivity() (+1 more)

### Community 41 - "i18n Setup & Tests"
Cohesion: 0.24
Nodes (7): __dirname, messages, app, i18n, pinia, router, pinia

### Community 42 - "Authenticate.php"
Cohesion: 0.33
Nodes (3): Authenticate, Illuminate\Auth\Middleware\Authenticate, Illuminate\Contracts\Http\Kernel

### Community 44 - "App.vue"
Cohesion: 0.17
Nodes (13): auth, route, showAppNav, showToast(), theme, toastState, toggleTheme(), NO_LENIS_ROUTES (+5 more)

### Community 45 - "Machine Data Normalization"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

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

### Community 61 - "Time Formatting Utilities"
Cohesion: 0.50
Nodes (4): fmtTime(), formatDate(), timeOnly(), updateClock()

### Community 70 - "EnforceIdleTimeoutTest.php"
Cohesion: 0.24
Nodes (4): ExpireStaleSessions, EnforceIdleTimeoutTest, Illuminate\Console\Command, Illuminate\Support\Carbon

### Community 95 - "ExpireStaleSessionsTest.php"
Cohesion: 0.31
Nodes (3): ExpireStaleSessionsTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan

### Community 102 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

## Knowledge Gaps
- **404 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+399 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 597 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **25 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Model & Reports` to `AdminRewardConfigTest`, `AuthController.php`, `Illuminate\Http\Request`, `AdminController.php`, `TestCase`, `EnforceIdleTimeoutTest.php`, `RewardItem`, `RvmMachine`, `AuthController`, `AdminBinCollectionEmailTest`, `Admin Caching Tests`, `Admin Pagination Tests`, `Points History Locale Tests`, `DetectionLog`, `RecyclingSession`, `WeighAwardsConfiguredPointsTest.php`, `ExpireStaleSessionsTest.php`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `AdminRewardConfigTest`, `EnforceIdleTimeoutTest.php`, `AuthController.php`, `AdminController.php`, `Reward Availability Tests`, `RewardItem`, `RvmMachine`, `AdminBinCollectionEmailTest`, `User Model & Reports`, `Admin Caching Tests`, `Admin Pagination Tests`, `Points History Locale Tests`, `DetectionLog`, `RecyclingSession`, `Test Application Bootstrap`, `WeighAwardsConfiguredPointsTest.php`, `ExpireStaleSessionsTest.php`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `QrSession`, `EnforceIdleTimeoutTest.php`, `TestCase`, `Illuminate\Http\Request`, `AdminController.php`, `Controller`, `RewardItem`, `AdminBinCollectionEmailTest`, `User Model & Reports`, `Admin Pagination Tests`, `Points History Locale Tests`, `RecyclingSession`, `WeighAwardsConfiguredPointsTest.php`, `ExpireStaleSessionsTest.php`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _404 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.016666666666666666 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.14130434782608695 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._