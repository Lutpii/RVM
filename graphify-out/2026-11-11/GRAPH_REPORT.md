# Graph Report - RVM  (2026-11-10)

## Corpus Check
- 303 files · ~228,060 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2418 nodes · 4999 edges · 165 communities (87 shown, 41 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 101 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `8465a89e`
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
- AdminCarbonStatsTest
- AdminPaginationTest
- fetchTabData
- ai_service/app.py
- DetectionLog
- AdminController.php
- KioskQrView.vue
- LoginView.vue
- ActivityView.vue
- Closure
- router/index.js
- RewardsView.vue
- normalizeMachine
- showToast
- SessionSummaryView.vue
- transactionDisplay.js
- Backend Build Config
- DashboardView.vue
- AdminPagination.vue
- App & Broadcast Providers
- dependencies
- HardwareCompactorProxyTest
- auth.js
- api.js
- Authenticate.php
- AdminRewardConfigTest
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- VoucherView.vue
- AdminBinCollectionEmailTest
- Console Kernel Scheduling
- AdminCachingTest
- QrSession
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- devDependencies
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
- DemoDataGeneratorTest
- AdminKioskMaintenanceTest
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- fmtTime
- MachineApiTest
- FrontEnd/vite.config.js
- setRewardImageFile
- EnforceIdleTimeoutTest
- scripts
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
- MaintenanceRecordsTest
- depositAndWait
- DemoDataGenerator
- normalize_material
- Illuminate\Support\Facades\Schema
- AdminCompactorSettingsTest
- AdminTransactionFiltersTest
- UserFactory.php
- VoucherModal.vue
- formatAuditLog
- test_yolo.py
- EnforceIdleTimeout.php
- heartbeat.js
- adminTabState.js
- voucher.js
- autoStartFlow
- AdminAuditLogTest
- AdminCashRedeemSettingsTest
- Handler
- rvm-kiosk-launch.sh
- scanMaintFrame
- RvmMachine
- startCameraMode
- awardPoints
- redeemNow
- vercel.json
- Illuminate\Database\Schema\Blueprint
- @phosphor-icons/vue
- ai_service_4bin/README.md
- start.sh
- cancelEditPassword
- setTheme
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register

## God Nodes (most connected - your core abstractions)
1. `User` - 154 edges
2. `TestCase` - 107 edges
3. `RvmMachine` - 102 edges
4. `RecyclingSession` - 69 edges
5. `RewardItem` - 55 edges
6. `Transaction` - 54 edges
7. `AdminController` - 48 edges
8. `PointsHistory` - 40 edges
9. `RewardRedemption` - 40 edges
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
- `auditRows` --indirect_call--> `formatAuditLog()`  [INFERRED]
  FrontEnd/src/views/AdminView.vue → FrontEnd/src/utils/admin/auditLog.js

## Import Cycles
- None detected.

## Communities (165 total, 41 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (156): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+148 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.10
Nodes (18): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+10 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (40): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+32 more)

### Community 4 - "FrontEnd/package.json"
Cohesion: 0.14
Nodes (13): axios, vite, name, private, version, autoprefixer, chart.js, jsqr (+5 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.05
Nodes (30): activeTab, auth, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, editingPassword, editingProfile (+22 more)

### Community 7 - "User"
Cohesion: 0.05
Nodes (30): User, AdminRedemptionsListTest, AuthControllerLocaleTest, ExampleTest, GoogleRedirectFrontendOriginTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest (+22 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.06
Nodes (8): AdminController, MachineController, TransactionController, UserController, Illuminate\Contracts\Http\Kernel, Illuminate\Http\JsonResponse, Illuminate\Http\Request, self

### Community 9 - "RewardItem"
Cohesion: 0.07
Nodes (8): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.16
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.09
Nodes (26): extractQrToken(), activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error (+18 more)

### Community 13 - "FormalReportService"
Cohesion: 0.12
Nodes (11): CarbonService, FormalReportService, CarbonServiceTest, ExampleTest, FormalReportServiceTest, PhpOffice\PhpSpreadsheet\Spreadsheet, PhpOffice\PhpSpreadsheet\Style\Alignment, PhpOffice\PhpSpreadsheet\Style\Border (+3 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (23): googleLoginEnabled, auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp() (+15 more)

### Community 16 - "Transaction"
Cohesion: 0.06
Nodes (9): AdminLog, MaintenanceRecord, PointsHistory, Transaction, AdminEmailFormalReportTest, AdminUpdateUserTest, PointsHistoryLocaleTest, TransactionCompleteAtomicityTest (+1 more)

### Community 20 - "fetchTabData"
Cohesion: 0.07
Nodes (32): isFresh(), changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage() (+24 more)

### Community 21 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (9): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest (+1 more)

### Community 23 - "AdminController.php"
Cohesion: 0.08
Nodes (13): MachineAuthMiddleware, CompactorSettingsService, SecurityLog, SecurityLogTest, Illuminate\Database\Eloquent\Builder, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Http (+5 more)

### Community 24 - "KioskQrView.vue"
Cohesion: 0.13
Nodes (24): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, clearIntervals(), currentToken, expiresInSec, generateQr() (+16 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.07
Nodes (30): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, history, redemptions, sessions (+22 more)

### Community 27 - "Closure"
Cohesion: 0.09
Nodes (16): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, RouteServiceProvider, Closure (+8 more)

### Community 28 - "router/index.js"
Cohesion: 0.05
Nodes (46): ActivityView(), AdminView(), GoodbyeView(), GoogleCallbackView(), KioskLandingView(), LandingView(), NotFoundView(), PrivacyView() (+38 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 31 - "showToast"
Cohesion: 0.14
Nodes (19): addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize(), deleteMachine(), deleteRewardItem(), deleteUser(), exportDetectionLogsCsv() (+11 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.14
Nodes (14): SessionSummaryView(), setKioskToken(), auth, earnedPoints, finalPoints, goHome(), isUnknownTransaction(), route (+6 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.26
Nodes (10): AI_CONFIDENCE_MIN, BIN_LETTERS, confidenceLevel(), formatWeight(), itemLabel(), MATERIAL_NAMES, percentOf(), resultReason() (+2 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.09
Nodes (22): DashboardView(), buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS, now (+14 more)

### Community 36 - "AdminPagination.vue"
Cohesion: 0.22
Nodes (11): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+3 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 40 - "auth.js"
Cohesion: 0.19
Nodes (17): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+9 more)

### Community 41 - "api.js"
Cohesion: 0.17
Nodes (14): mounted(), prefersReducedMotion(), app, i18n, pinia, api, hasMachineKey, storedKioskToken (+6 more)

### Community 44 - "App.vue"
Cohesion: 0.17
Nodes (13): auth, route, showAppNav, showToast(), theme, toastState, toggleTheme(), NO_LENIS_ROUTES (+5 more)

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.09
Nodes (27): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+19 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "VoucherView.vue"
Cohesion: 0.15
Nodes (9): VoucherView(), auth, error, loading, markError, marking, route, { t } (+1 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "QrSession"
Cohesion: 0.22
Nodes (6): Controller, QrController, QrSession, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

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

### Community 61 - "ImageCropper.vue"
Cohesion: 0.11
Nodes (35): baseName(), exportFile(), frame, frameEl, framePoint(), imageStyle, imageUrl, load() (+27 more)

### Community 72 - "isReviewable"
Cohesion: 0.27
Nodes (11): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 92 - "Machine"
Cohesion: 0.15
Nodes (5): chamber_capacity(), Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default.

### Community 93 - "vitest"
Cohesion: 0.16
Nodes (10): flattenKeys(), __dirname, messages, COMPACTOR_KEYS, __dirname, buildRewardUpdatePayload(), resolveLoadingFlag(), TAB_LOADING_FLAG (+2 more)

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "fmtTime"
Cohesion: 0.50
Nodes (4): fmtTime(), formatDate(), timeOnly(), updateClock()

### Community 100 - "MachineApiTest"
Cohesion: 0.13
Nodes (4): create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 101 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 102 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

### Community 104 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 105 - "GoogleCallbackAccountLinkTest.php"
Cohesion: 0.36
Nodes (4): GoogleCallbackAccountLinkTest, Laravel\Socialite\Facades\Socialite, Laravel\Socialite\Two\User, Mockery

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.07
Nodes (27): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+19 more)

### Community 107 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.18
Nodes (4): PlaceholderDriver, Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.24
Nodes (13): ACCEPTED_2BIN, AUTO_END_SECONDS, limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL, sessionLimitReached() (+5 more)

### Community 110 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 116 - "depositAndWait"
Cohesion: 0.31
Nodes (9): claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), recordInvalidItem(), simulateInsert(), stopSwitchTimer() (+1 more)

### Community 117 - "DemoDataGenerator"
Cohesion: 0.09
Nodes (7): AiService, DemoDataGenerator, Carbon, RewardConfigService, GuestCarbonTest, Carbon\Carbon, Carbon\CarbonInterface

### Community 118 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 124 - "UserFactory.php"
Cohesion: 0.47
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 125 - "VoucherModal.vue"
Cohesion: 0.25
Nodes (8): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t }

### Community 126 - "formatAuditLog"
Cohesion: 0.33
Nodes (7): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditRows

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 130 - "heartbeat.js"
Cohesion: 0.38
Nodes (4): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault()

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 135 - "autoStartFlow"
Cohesion: 0.38
Nodes (7): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), machineLocked(), recycleAgain(), refreshChamber()

### Community 138 - "Handler"
Cohesion: 0.33
Nodes (3): BaseHTTPRequestHandler, Handler, Local-only companion for the RVM kiosk's admin "Maintenance" action. Runs as…

### Community 139 - "rvm-kiosk-launch.sh"
Cohesion: 0.60
Nodes (5): new_bytes(), rvm-kiosk-launch.sh script, size_of(), start_ai(), uptime_s()

### Community 140 - "scanMaintFrame"
Cohesion: 0.47
Nodes (6): closeMaintenance(), scanMaintFrame(), startMaintCamera(), stopMaintCamera(), submitMaintenance(), toggleMaintCamera()

### Community 142 - "RvmMachine"
Cohesion: 0.08
Nodes (9): SessionController, RecyclingSession, RvmMachine, AdminExportExcelTest, DetectionLogTest, ExpireStaleSessionsTest, WeighAwardsConfiguredPointsTest, Illuminate\Support\Str (+1 more)

### Community 143 - "startCameraMode"
Cohesion: 0.40
Nodes (5): captureFromCamera(), captureFromHardware(), openCamera(), startCameraMode(), stopCamera()

### Community 144 - "awardPoints"
Cohesion: 0.25
Nodes (9): flushChamber(), isNetworkError(), newIdempotencyKey(), retryOnce(), awardPoints(), confirmEndSession(), materialLimit(), randomPointsFallback() (+1 more)

### Community 145 - "redeemNow"
Cohesion: 0.50
Nodes (4): deleteAccount(), loadRedemptionHistory(), redeemNow(), showToast

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 148 - "@phosphor-icons/vue"
Cohesion: 0.46
Nodes (5): activeKey, route, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

## Knowledge Gaps
- **577 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+572 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 943 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **41 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Http\Request`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `FormalReportService`, `RvmMachine`, `Transaction`, `AuthController`, `AdminCarbonStatsTest`, `AdminPaginationTest`, `DetectionLog`, `AdminController.php`, `AdminRewardConfigTest`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `DemoDataGeneratorTest`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `EnforceIdleTimeoutTest`, `GoogleCallbackAccountLinkTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `MaintenanceRecordsTest`, `DemoDataGenerator`, `AdminCompactorSettingsTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.058) - this node is a cross-community bridge._
- **Why does `TestCase` connect `User` to `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `FormalReportService`, `RvmMachine`, `Transaction`, `AdminCarbonStatsTest`, `AdminPaginationTest`, `DetectionLog`, `AdminController.php`, `HardwareCompactorProxyTest`, `AdminRewardConfigTest`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `Test Application Bootstrap`, `DemoDataGeneratorTest`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `EnforceIdleTimeoutTest`, `GoogleCallbackAccountLinkTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `MaintenanceRecordsTest`, `DemoDataGenerator`, `AdminCompactorSettingsTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `User`, `Illuminate\Http\Request`, `EnforceIdleTimeoutTest`, `FormalReportService`, `MachineHeartbeatTest`, `Transaction`, `AdminBinCollectionEmailTest`, `AdminCarbonStatsTest`, `AdminPaginationTest`, `QrSession`, `DemoDataGenerator`, `DetectionLog`, `AdminController.php`, `MaintenanceRecordsTest`, `AdminTransactionFiltersTest`, `DemoDataGeneratorTest`, `AdminKioskMaintenanceTest`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _577 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.011111111111111112 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.09841269841269841 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._