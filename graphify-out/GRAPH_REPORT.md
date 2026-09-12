# Graph Report - RVM  (2026-09-12)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1254 nodes · 2392 edges · 92 communities (48 shown, 24 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 42 edges (avg confidence: 0.85)
- Token cost: 92,056 input · 6,702 output

## Graph Freshness
- Built from commit: `799ba405`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Admin Dashboard View
- Admin Notification Emails
- Composer Configuration
- RVM Session Camera View
- Frontend Dependencies
- Database Migrations
- User Settings View
- Admin Feature Tests
- Admin Controller Methods
- Carbon & Report Services
- Machine & Reward Controllers
- Dashboard & Auth Store
- QR Scan View
- Reward Redemption Models
- Vue Router Configuration
- Registration View
- Dashboard Stats Models
- Auth Controller
- User Model & Reports
- Admin Pagination Tests
- Admin Table Pagination Logic
- Hardware Control App
- Detection Log Model & Tests
- Recycling Session Controller
- Kiosk QR View
- Login View
- Session Summary View
- Auth & Locale Middleware
- Landing & Welcome Views
- Rewards Catalog View
- Admin UI Utility Helpers
- Admin CRUD Actions
- Reward Items CRUD Tests
- Transaction Hardware Controller
- Backend Build Config
- Core Backend Config
- QR Session Controller
- App & Broadcast Providers
- Activity Feed View
- AI Detection Service
- Eloquent Models
- i18n Setup & Tests
- Route Service Provider
- Reward Availability Tests
- App Root Component
- Machine Data Normalization
- YOLO Servo Test Script
- Event Service Provider
- App Navigation Bar
- Bin Collection Email Tests
- Console Kernel Scheduling
- Admin Caching Tests
- Points History Locale Tests
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- Authentication Middleware
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- Reward Config Helper
- Time Formatting Utilities
- HTTP Kernel
- Cookie Encryption Middleware
- Maintenance Mode Middleware
- Trim Strings Middleware
- Trusted Proxies Middleware
- Signed URL Middleware
- CSRF Verification Middleware
- Database Connection Helper
- Console Routes
- Review Eligibility Logic

## God Nodes (most connected - your core abstractions)
1. `User` - 81 edges
2. `TestCase` - 53 edges
3. `RvmMachine` - 50 edges
4. `RewardItem` - 44 edges
5. `RecyclingSession` - 40 edges
6. `AdminController` - 37 edges
7. `fetchTabData()` - 27 edges
8. `Transaction` - 26 edges
9. `AdminPaginationTest` - 24 edges
10. `DetectionLog` - 23 edges

## Surprising Connections (you probably didn't know these)
- `fetchTabData()` --indirect_call--> `normalizeMachine()`  [INFERRED]
  FrontEnd/src/views/AdminView.vue → FrontEnd/src/utils/admin/normalizeMachine.js
- `MachineController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/MachineController.php → BackEnd/app/Http/Controllers/Controller.php
- `RewardController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/RewardController.php → BackEnd/app/Http/Controllers/Controller.php
- `UserController` --inherits--> `Controller`  [EXTRACTED]
  BackEnd/app/Http/Controllers/UserController.php → BackEnd/app/Http/Controllers/Controller.php
- `AdminRedemptionsListTest` --inherits--> `TestCase`  [EXTRACTED]
  BackEnd/tests/Feature/AdminRedemptionsListTest.php → BackEnd/tests/TestCase.php

## Import Cycles
- None detected.

## Communities (92 total, 24 thin omitted)

### Community 0 - "Admin Dashboard View"
Cohesion: 0.02
Nodes (101): activeMachines, activeTab, adminMachines, adminRedemptions, auth, barChartData, barChartOptions, binTypes (+93 more)

### Community 1 - "Admin Notification Emails"
Cohesion: 0.06
Nodes (27): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, RewardConfigService, UserFactory (+19 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RVM Session Camera View"
Cohesion: 0.05
Nodes (42): aiConfidence, aiDetected, annotatedImageDataUrl, auth, autoStartFlow(), cameraCountdown, cameraMode, cameraStreamUrl (+34 more)

### Community 4 - "Frontend Dependencies"
Cohesion: 0.05
Nodes (41): dependencies, axios, chart.js, jsqr, leaflet, @phosphor-icons/vue, pinia, qrcode (+33 more)

### Community 5 - "Database Migrations"
Cohesion: 0.07
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 6 - "User Settings View"
Cohesion: 0.05
Nodes (32): activeTab, auth, cancelEditPassword(), changePassword(), confirmDelete, conversionRate, deletingAccount, editingPassword (+24 more)

### Community 7 - "Admin Feature Tests"
Cohesion: 0.10
Nodes (12): AdminRewardConfigTest, ExampleTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, RewardCatalogTest, SessionControllerLocaleTest, TestCase, Illuminate\Foundation\Testing\RefreshDatabase (+4 more)

### Community 8 - "Admin Controller Methods"
Cohesion: 0.16
Nodes (3): AdminController, Illuminate\Contracts\Http\Kernel, Illuminate\Http\Request

### Community 9 - "Carbon & Report Services"
Cohesion: 0.15
Nodes (8): CarbonService, FormalReportService, CarbonServiceTest, ExampleTest, FormalReportServiceTest, PhpOffice\PhpSpreadsheet\Worksheet\Worksheet, PHPUnit\Framework\TestCase, Spreadsheet

### Community 10 - "Machine & Reward Controllers"
Cohesion: 0.11
Nodes (5): MachineController, RewardController, UserController, PointsHistory, Illuminate\Http\JsonResponse

### Community 11 - "Dashboard & Auth Store"
Cohesion: 0.10
Nodes (18): DashboardView(), GoogleCallbackView(), api, registerClearAuth(), useAuthStore, resolveCachedPoints(), auth, binTypes (+10 more)

### Community 12 - "QR Scan View"
Cohesion: 0.09
Nodes (25): activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error, extractToken() (+17 more)

### Community 13 - "Reward Redemption Models"
Cohesion: 0.16
Nodes (5): RewardItem, RewardRedemption, AdminRedemptionsListTest, RewardHistoryTest, RewardRedeemTest

### Community 14 - "Vue Router Configuration"
Cohesion: 0.10
Nodes (20): AdminView(), GoodbyeView(), KioskLandingView(), KioskQrView(), NotFoundView(), routes, RvmSessionView(), ScanView() (+12 more)

### Community 15 - "Registration View"
Cohesion: 0.09
Nodes (21): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleVerifyOtp(), loading (+13 more)

### Community 16 - "Dashboard Stats Models"
Cohesion: 0.16
Nodes (5): RvmMachine, Transaction, AdminCarbonStatsTest, AdminExportExcelTest, PhpOffice\PhpSpreadsheet\IOFactory

### Community 17 - "Auth Controller"
Cohesion: 0.15
Nodes (3): AuthController, FonnteService, Illuminate\Http\RedirectResponse

### Community 18 - "User Model & Reports"
Cohesion: 0.14
Nodes (7): User, AdminEmailFormalReportTest, AuthControllerLocaleTest, TransactionControllerLocaleTest, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 20 - "Admin Table Pagination Logic"
Cohesion: 0.10
Nodes (21): changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage(), fetchTabData(), filterDetection() (+13 more)

### Community 21 - "Hardware Control App"
Cohesion: 0.20
Nodes (18): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+10 more)

### Community 23 - "Recycling Session Controller"
Cohesion: 0.16
Nodes (4): SessionController, RecyclingSession, DetectionLogTest, WeighAwardsConfiguredPointsTest

### Community 24 - "Kiosk QR View"
Cohesion: 0.15
Nodes (19): clearIntervals(), currentToken, expiresInSec, generateQr(), handleExpiry(), loadingQr, qrSvgSrc, route (+11 more)

### Community 25 - "Login View"
Cohesion: 0.11
Nodes (13): LoginView(), auth, error, form, loading, { locale, t }, loginMethod, otpSent (+5 more)

### Community 26 - "Session Summary View"
Cohesion: 0.12
Nodes (14): SessionSummaryView(), setKioskToken(), useRvmStore, auth, earnedPoints, finalPoints, goHome(), MATERIAL_ICON_PATHS (+6 more)

### Community 27 - "Auth & Locale Middleware"
Cohesion: 0.16
Nodes (9): AdminMiddleware, KioskAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, Closure, Illuminate\Support\Facades\App, Illuminate\Support\Facades\Auth, Laravel\Sanctum\TransientToken (+1 more)

### Community 28 - "Landing & Welcome Views"
Cohesion: 0.12
Nodes (13): LandingView(), WelcomeView(), { locale, t }, router, theme, toggleTheme, auth, route (+5 more)

### Community 29 - "Rewards Catalog View"
Cohesion: 0.14
Nodes (15): RewardsView(), activeCategory, auth, categories, confirmingItem, fetchItems(), filteredItems, items (+7 more)

### Community 30 - "Admin UI Utility Helpers"
Cohesion: 0.16
Nodes (8): paginationLabel(), isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, toDatetimeLocalValue(), openEditRewardItem(), switchTab(), vitest

### Community 31 - "Admin CRUD Actions"
Cohesion: 0.17
Nodes (16): addRewardItem(), askConfirm(), buildRewardItemFormData(), deleteMachine(), deleteRewardItem(), deleteUser(), exportDetectionLogsCsv(), exportExcel() (+8 more)

### Community 32 - "Reward Items CRUD Tests"
Cohesion: 0.24
Nodes (3): AdminLog, AdminRewardItemsCrudTest, Illuminate\Http\UploadedFile

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "Core Backend Config"
Cohesion: 0.20
Nodes (6): Carbon\Carbon, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Validator, Illuminate\Support\Str, Laravel\Socialite\Facades\Socialite, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 36 - "QR Session Controller"
Cohesion: 0.26
Nodes (6): Controller, QrController, QrSession, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "Activity Feed View"
Cohesion: 0.20
Nodes (8): ActivityView(), mergeActivityFeed(), feed, hasLoadError, loading, pointsHistoryFailed, redemptionsFailed, sessionsFailed

### Community 41 - "i18n Setup & Tests"
Cohesion: 0.24
Nodes (7): __dirname, messages, app, i18n, pinia, router, pinia

### Community 42 - "Route Service Provider"
Cohesion: 0.25
Nodes (5): RouteServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route

### Community 44 - "App Root Component"
Cohesion: 0.25
Nodes (7): auth, route, showAppNav, showToast(), theme, toastState, toggleTheme()

### Community 45 - "Machine Data Normalization"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 46 - "YOLO Servo Test Script"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "App Navigation Bar"
Cohesion: 0.46
Nodes (5): activeKey, route, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

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

### Community 61 - "Time Formatting Utilities"
Cohesion: 0.50
Nodes (4): fmtTime(), formatDate(), timeOnly(), updateClock()

## Knowledge Gaps
- **365 isolated node(s):** `activeMachines`, `activeTab`, `adminMachines`, `adminRedemptions`, `auth` (+360 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 553 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **24 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Model & Reports` to `Reward Items CRUD Tests`, `Admin Notification Emails`, `Core Backend Config`, `Admin Feature Tests`, `Admin Controller Methods`, `Carbon & Report Services`, `Reward Redemption Models`, `Dashboard Stats Models`, `Auth Controller`, `Bin Collection Email Tests`, `Admin Caching Tests`, `Admin Pagination Tests`, `Points History Locale Tests`, `Detection Log Model & Tests`, `Recycling Session Controller`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `vue` connect `Session Summary View` to `Admin Dashboard View`, `RVM Session Camera View`, `Frontend Dependencies`, `Activity Feed View`, `User Settings View`, `i18n Setup & Tests`, `Dashboard & Auth Store`, `App Root Component`, `QR Scan View`, `Vue Router Configuration`, `Registration View`, `App Navigation Bar`, `Kiosk QR View`, `Login View`, `Landing & Welcome Views`, `Rewards Catalog View`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Why does `vue-router` connect `Vue Router Configuration` to `Admin Dashboard View`, `RVM Session Camera View`, `Frontend Dependencies`, `User Settings View`, `Dashboard & Auth Store`, `App Root Component`, `QR Scan View`, `Registration View`, `App Navigation Bar`, `Kiosk QR View`, `Login View`, `Session Summary View`, `Landing & Welcome Views`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **What connects `activeMachines`, `activeTab`, `adminMachines` to the rest of the system?**
  _365 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin Dashboard View` be split into smaller, more focused modules?**
  _Cohesion score 0.017094017094017096 - nodes in this community are weakly interconnected._
- **Should `Admin Notification Emails` be split into smaller, more focused modules?**
  _Cohesion score 0.06382978723404255 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._