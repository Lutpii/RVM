# Graph Report - RVM  (2026-11-11)

## Corpus Check
- 316 files · ~238,794 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2526 nodes · 5299 edges · 178 communities (90 shown, 50 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 102 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `57b5e0b9`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- BinCollectionRequested
- Composer Configuration
- RvmSessionView.vue
- Illuminate\Http\JsonResponse
- Illuminate\Database\Migrations\Migration
- UserSettingsView.vue
- User
- Illuminate\Http\Request
- RewardItem
- RewardRedemption
- GpioDriver
- ScanView.vue
- FormalReportService
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- Transaction
- AuthController
- AdminBinCollectionEmailTest
- AdminPaginationTest
- fetchTabData
- ai_service/app.py
- DetectionLog
- QrSession
- machineAlerts.test.js
- LoginView.vue
- ActivityView.vue
- Closure
- router/index.js
- RewardsView.vue
- LandingView.vue
- showToast
- SessionSummaryView.vue
- transactionDisplay.js
- Backend Build Config
- DashboardView.vue
- AdminPagination.vue
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- auth.js
- api.js
- Authenticate.php
- AdminRewardConfigTest
- vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- _UsbCameraWrapper
- UserController
- Console Kernel Scheduling
- AdminCachingTest
- Controller
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- RewardVoucherTest
- ImageCropper.vue
- HTTP Kernel
- Cookie Encryption Middleware
- Maintenance Mode Middleware
- Trim Strings Middleware
- TrustProxies.php
- Signed URL Middleware
- CSRF Verification Middleware
- Database Connection Helper
- MachineTest
- isReviewable
- Machine
- vitest
- AdminLog
- AdminKioskMaintenanceTest
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- scrollReveal.js
- MaintenanceRecordsTest
- AdminCompactorSettingsTest
- GoogleCallbackAccountLinkTest.php
- MaintenanceHistoryModal.vue
- saveDetectionReview
- PlaceholderDriver
- compactorFlow.js
- tapGesture.test.js
- MachineHeartbeatTest
- AccountSuspensionTest
- CashRedeemTest
- RecordingDriver
- devDependencies
- depositAndWait
- DemoDataGenerator
- normalize_material
- Illuminate\Support\Facades\Schema
- AdminTableExportTest
- Illuminate\Database\Schema\Blueprint
- avatarIcons.js
- AdminTransactionFiltersTest
- Illuminate\Support\Str
- VoucherModal.vue
- formatAuditLog
- test_yolo.py
- voucher.js
- CompactorSettingsService
- heartbeat.js
- adminTabState.js
- AccountSummaryTest
- EnforceIdleTimeoutTest
- autoStartFlow
- AdminAuditLogTest
- AdminCashRedeemSettingsTest
- Handler
- rvm-kiosk-launch.sh
- extractQrToken
- redeemNow
- RvmMachine
- FrontEnd/vite.config.js
- awardPoints
- scripts
- vercel.json
- @phosphor-icons/vue
- index.php
- cancelEditPassword
- ai_service_4bin/README.md
- start.sh
- num
- SecurityLogTest
- AdminCarbonStatsTest
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- AdminEmailFormalReportTest
- GoogleRedirectFrontendOriginTest
- AdminExportExcelTest
- AdminUpdateUserTest
- WeighAwardsConfiguredPointsTest
- ExampleTest

## God Nodes (most connected - your core abstractions)
1. `User` - 166 edges
2. `RvmMachine` - 114 edges
3. `TestCase` - 113 edges
4. `RecyclingSession` - 75 edges
5. `Transaction` - 64 edges
6. `AdminController` - 59 edges
7. `RewardItem` - 59 edges
8. `RewardRedemption` - 47 edges
9. `PointsHistory` - 43 edges
10. `DetectionLog` - 38 edges

## Surprising Connections (you probably didn't know these)
- `MachineTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_hardware.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `RecordingDriver`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/tests/test_hardware.py
- `depositAndWait()` --indirect_call--> `getHardwareState()`  [INFERRED]
  FrontEnd/src/views/RvmSessionView.vue → FrontEnd/src/services/compactor.js
- `tick()` --calls--> `isMachineFault()`  [EXTRACTED]
  FrontEnd/src/services/heartbeat.js → FrontEnd/src/utils/compactorFlow.js

## Import Cycles
- None detected.

## Communities (178 total, 50 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (161): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+153 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (45): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+37 more)

### Community 4 - "Illuminate\Http\JsonResponse"
Cohesion: 0.14
Nodes (4): MachineController, TransactionController, Illuminate\Http\JsonResponse, self

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (40): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+32 more)

### Community 7 - "User"
Cohesion: 0.07
Nodes (21): User, AdminRedemptionsListTest, AuthControllerLocaleTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, RewardHistoryTest, RewardRedemptionCashColumnsTest (+13 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (4): AdminController, AdminTableExport, Illuminate\Database\Eloquent\Builder, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.09
Nodes (7): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.14
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.09
Nodes (25): activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error, handleScan() (+17 more)

### Community 13 - "FormalReportService"
Cohesion: 0.08
Nodes (19): PublicStatsController, CarbonService, FormalReportService, ReadsExcelDownloads, CarbonServiceTest, ExampleTest, FormalReportServiceTest, Illuminate\Testing\TestResponse (+11 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.11
Nodes (22): auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp(), loading (+14 more)

### Community 16 - "Transaction"
Cohesion: 0.10
Nodes (6): PointsHistory, Transaction, PointsHistoryLocaleTest, SessionSummaryReceiptTest, TransactionCompleteAtomicityTest, Illuminate\Database\Eloquent\Model

### Community 17 - "AuthController"
Cohesion: 0.12
Nodes (7): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule, Laravel\Socialite\Facades\Socialite

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (36): isFresh(), changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage() (+28 more)

### Community 21 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (9): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest (+1 more)

### Community 23 - "QrSession"
Cohesion: 0.14
Nodes (8): QrController, QrSession, RouteServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 24 - "machineAlerts.test.js"
Cohesion: 0.27
Nodes (9): buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS, now, machineAlerts (+1 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): googleLoginEnabled, auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.07
Nodes (30): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, history, redemptions, sessions (+22 more)

### Community 27 - "Closure"
Cohesion: 0.10
Nodes (13): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, Closure (+5 more)

### Community 28 - "router/index.js"
Cohesion: 0.06
Nodes (37): ActivityView(), AdminView(), GoodbyeView(), GoogleCallbackView(), LoginView(), NotFoundView(), PrivacyView(), RegisterView() (+29 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "LandingView.vue"
Cohesion: 0.12
Nodes (14): LandingView(), acceptedChips, barLabel, intlLocale, kg(), { locale, t }, MONTH_MATERIALS, monthLabel (+6 more)

### Community 31 - "showToast"
Cohesion: 0.12
Nodes (23): cleanParams(), downloadExcel(), excelFilename(), addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize(), deleteMachine() (+15 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.14
Nodes (14): SessionSummaryView(), setKioskToken(), auth, earnedPoints, finalPoints, goHome(), isUnknownTransaction(), route (+6 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.27
Nodes (9): AI_CONFIDENCE_MIN, BIN_LETTERS, confidenceLevel(), itemLabel(), MATERIAL_NAMES, percentOf(), resultReason(), txConfClass() (+1 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.11
Nodes (13): DashboardView(), MATERIAL_ICON_PATHS, materialIconSvg(), auth, AVAILABILITY_LABEL, binTypes, loadingMachines, machineDotClass() (+5 more)

### Community 36 - "AdminPagination.vue"
Cohesion: 0.22
Nodes (11): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+3 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.13
Nodes (24): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, clearIntervals(), currentToken, expiresInSec, generateQr() (+16 more)

### Community 40 - "auth.js"
Cohesion: 0.19
Nodes (17): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+9 more)

### Community 41 - "api.js"
Cohesion: 0.23
Nodes (12): app, i18n, pinia, router, api, hasMachineKey, storedKioskToken, getHardwareState() (+4 more)

### Community 44 - "vue"
Cohesion: 0.12
Nodes (18): auth, route, showAppNav, showToast(), theme, toastState, toggleTheme(), iconSize (+10 more)

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.14
Nodes (22): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+14 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "_UsbCameraWrapper"
Cohesion: 0.23
Nodes (5): Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, RuntimeError

### Community 49 - "UserController"
Cohesion: 0.18
Nodes (3): UserController, AvatarIcon, Illuminate\Support\Facades\Hash

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "Controller"
Cohesion: 0.29
Nodes (5): Controller, SessionController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

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

### Community 61 - "ImageCropper.vue"
Cohesion: 0.11
Nodes (35): baseName(), exportFile(), frame, frameEl, framePoint(), imageStyle, imageUrl, load() (+27 more)

### Community 72 - "isReviewable"
Cohesion: 0.27
Nodes (11): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 92 - "Machine"
Cohesion: 0.17
Nodes (3): Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…

### Community 93 - "vitest"
Cohesion: 0.11
Nodes (15): flattenKeys(), __dirname, messages, COMPACTOR_KEYS, __dirname, BIN_LEVEL_KEYS, normalizeMachine(), buildRewardUpdatePayload() (+7 more)

### Community 94 - "AdminLog"
Cohesion: 0.11
Nodes (4): AdminLog, MaintenanceRecord, DemoDataGeneratorTest, Carbon

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.14
Nodes (13): axios, vite, name, private, version, autoprefixer, chart.js, jsqr (+5 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.13
Nodes (4): create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 105 - "GoogleCallbackAccountLinkTest.php"
Cohesion: 0.42
Nodes (3): GoogleCallbackAccountLinkTest, Laravel\Socialite\Two\User, Mockery

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.07
Nodes (27): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+19 more)

### Community 107 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.15
Nodes (6): chamber_capacity(), PlaceholderDriver, Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default., Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.23
Nodes (14): ACCEPTED_2BIN, AUTO_END_SECONDS, isMachineFault(), limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL (+6 more)

### Community 110 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 115 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 116 - "depositAndWait"
Cohesion: 0.27
Nodes (10): depositItem(), claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), recordInvalidItem(), simulateInsert() (+2 more)

### Community 117 - "DemoDataGenerator"
Cohesion: 0.07
Nodes (8): AiService, DemoDataGenerator, Carbon, RewardConfigService, GuestCarbonTest, HardwareCompactorProxyTest, Carbon\Carbon, Carbon\CarbonInterface

### Community 118 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 122 - "avatarIcons.js"
Cohesion: 0.27
Nodes (9): picked, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR, formatAvatar(), maskEmail(), parseAvatar(), draftValue (+1 more)

### Community 124 - "Illuminate\Support\Str"
Cohesion: 0.22
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, static

### Community 125 - "VoucherModal.vue"
Cohesion: 0.25
Nodes (8): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t }

### Community 126 - "formatAuditLog"
Cohesion: 0.33
Nodes (7): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditRows

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 128 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 130 - "heartbeat.js"
Cohesion: 0.40
Nodes (3): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick()

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 135 - "autoStartFlow"
Cohesion: 0.38
Nodes (7): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), machineLocked(), recycleAgain(), refreshChamber()

### Community 138 - "Handler"
Cohesion: 0.33
Nodes (3): BaseHTTPRequestHandler, Handler, Local-only companion for the RVM kiosk's admin "Maintenance" action. Runs as…

### Community 139 - "rvm-kiosk-launch.sh"
Cohesion: 0.60
Nodes (5): new_bytes(), rvm-kiosk-launch.sh script, size_of(), start_ai(), uptime_s()

### Community 140 - "extractQrToken"
Cohesion: 0.43
Nodes (7): extractQrToken(), closeMaintenance(), scanMaintFrame(), startMaintCamera(), stopMaintCamera(), submitMaintenance(), toggleMaintCamera()

### Community 141 - "redeemNow"
Cohesion: 0.33
Nodes (6): deleteAccount(), loadRedemptionHistory(), loadSummary(), redeemNow(), saveAvatar(), showToast

### Community 142 - "RvmMachine"
Cohesion: 0.07
Nodes (14): RecyclingSession, RvmMachine, DetectionLogTest, ExpireStaleSessionsTest, PublicStatsTest, QrLifetimeTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Carbon (+6 more)

### Community 143 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 144 - "awardPoints"
Cohesion: 0.25
Nodes (9): flushChamber(), isNetworkError(), newIdempotencyKey(), retryOnce(), awardPoints(), confirmEndSession(), materialLimit(), randomPointsFallback() (+1 more)

### Community 145 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 148 - "@phosphor-icons/vue"
Cohesion: 0.11
Nodes (17): activeKey, route, KioskLandingView(), WelcomeView(), NAV_ITEMS, resolveActiveNavKey(), machineLocation, machineName (+9 more)

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

## Knowledge Gaps
- **599 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+594 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 972 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **50 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AccountSummaryTest`, `EnforceIdleTimeoutTest`, `Illuminate\Http\Request`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `FormalReportService`, `RvmMachine`, `Transaction`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `DetectionLog`, `Closure`, `SecurityLogTest`, `AdminCarbonStatsTest`, `AdminEmailFormalReportTest`, `AdminRewardConfigTest`, `AdminExportExcelTest`, `AdminUpdateUserTest`, `WeighAwardsConfiguredPointsTest`, `AdminCachingTest`, `Controller`, `RewardVoucherTest`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `MaintenanceRecordsTest`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `DemoDataGenerator`, `AdminTableExportTest`, `AdminTransactionFiltersTest`, `Illuminate\Support\Str`?**
  _High betweenness centrality (0.058) - this node is a cross-community bridge._
- **Why does `TestCase` connect `User` to `AccountSummaryTest`, `EnforceIdleTimeoutTest`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `FormalReportService`, `RvmMachine`, `Transaction`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `DetectionLog`, `SecurityLogTest`, `AdminCarbonStatsTest`, `AdminEmailFormalReportTest`, `AdminRewardConfigTest`, `GoogleRedirectFrontendOriginTest`, `AdminExportExcelTest`, `AdminUpdateUserTest`, `WeighAwardsConfiguredPointsTest`, `ExampleTest`, `AdminCachingTest`, `Test Application Bootstrap`, `RewardVoucherTest`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `MaintenanceRecordsTest`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `DemoDataGenerator`, `AdminTableExportTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `Illuminate\Http\JsonResponse`, `AccountSummaryTest`, `EnforceIdleTimeoutTest`, `User`, `Illuminate\Http\Request`, `FormalReportService`, `Transaction`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `DetectionLog`, `QrSession`, `Closure`, `SecurityLogTest`, `AdminCarbonStatsTest`, `AdminEmailFormalReportTest`, `AdminExportExcelTest`, `WeighAwardsConfiguredPointsTest`, `Controller`, `AdminLog`, `AdminKioskMaintenanceTest`, `MaintenanceRecordsTest`, `MachineHeartbeatTest`, `DemoDataGenerator`, `AdminTableExportTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _599 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.010927056088346411 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._