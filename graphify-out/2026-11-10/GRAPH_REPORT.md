# Graph Report - RVM  (2026-11-10)

## Corpus Check
- 288 files · ~159,338 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2317 nodes · 4765 edges · 168 communities (88 shown, 44 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 97 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c7a880e4`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- AuthController.php
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
- RecyclingSession
- AuthController
- AdminCarbonStatsTest
- AdminPaginationTest
- fetchTabData
- ai_service/app.py
- DetectionLog
- Illuminate\Http\JsonResponse
- KioskQrView.vue
- LoginView.vue
- ActivityView.vue
- Closure
- router/index.js
- RewardsView.vue
- normalizeMachine
- showToast
- SessionSummaryView.vue
- TransactionController
- Backend Build Config
- DashboardView.vue
- AdminPagination.vue
- App & Broadcast Providers
- dependencies
- DemoDataGenerator
- auth.js
- api.js
- Authenticate.php
- AdminRewardConfigTest
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- vue
- AdminBinCollectionEmailTest
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
- AdminLog
- RvmMachine
- swipe.js
- AdminEmailFormalReportTest
- toDatetimeLocalValue
- fmtTime
- MachineApiTest
- FrontEnd/vite.config.js
- setRewardImageFile
- QrSession
- scripts
- AdminController.php
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
- machineAlerts.test.js
- normalize_material
- Illuminate\Database\Schema\Blueprint
- AdminKioskMaintenanceTest
- api.php
- AdminCompactorSettingsTest
- AdminTransactionFiltersTest
- SessionDetailModal.vue
- VoucherModal.vue
- formatAuditLog
- test_yolo.py
- CompactorSettingsService
- RewardVoucherTest
- heartbeat.js
- adminTabState.js
- voucher.js
- Illuminate\Support\Facades\Schema
- AuthVerificationGateTest
- autoStartFlow
- AdminAuditLogTest
- AdminCashRedeemSettingsTest
- Handler
- rvm-kiosk-launch.sh
- scanMaintFrame
- AdminUpdateUserTest
- GoogleRedirectFrontendOriginTest
- startCameraMode
- startLimitEnd
- redeemNow
- vercel.json
- ExampleTest
- ai_service_4bin/README.md
- start.sh
- cancelEditPassword
- setTheme

## God Nodes (most connected - your core abstractions)
1. `User` - 142 edges
2. `TestCase` - 99 edges
3. `RvmMachine` - 94 edges
4. `RecyclingSession` - 64 edges
5. `RewardItem` - 55 edges
6. `AdminController` - 48 edges
7. `Transaction` - 47 edges
8. `RewardRedemption` - 40 edges
9. `DetectionLog` - 38 edges
10. `PointsHistory` - 36 edges

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

## Communities (168 total, 44 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (152): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+144 more)

### Community 1 - "AuthController.php"
Cohesion: 0.06
Nodes (26): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+18 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (40): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+32 more)

### Community 4 - "FrontEnd/package.json"
Cohesion: 0.15
Nodes (12): axios, vite, name, private, version, autoprefixer, chart.js, jsqr (+4 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.05
Nodes (30): activeTab, auth, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, editingPassword, editingProfile (+22 more)

### Community 7 - "User"
Cohesion: 0.07
Nodes (20): User, AdminRedemptionsListTest, AuthControllerLocaleTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, RewardHistoryTest, RewardRedemptionCashColumnsTest, SessionControllerLocaleTest (+12 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (4): AdminController, Illuminate\Contracts\Http\Kernel, Illuminate\Database\Eloquent\Builder, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.09
Nodes (6): RewardItem, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.13
Nodes (4): RewardController, RewardRedemption, CashRedeemSettingsService, self

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
Cohesion: 0.11
Nodes (22): auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp(), loading (+14 more)

### Community 16 - "RecyclingSession"
Cohesion: 0.07
Nodes (9): MaintenanceRecord, PointsHistory, RecyclingSession, RewardItemImage, Transaction, PointsHistoryLocaleTest, TransactionCompleteAtomicityTest, Illuminate\Database\Eloquent\Model (+1 more)

### Community 20 - "fetchTabData"
Cohesion: 0.07
Nodes (30): isFresh(), changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage() (+22 more)

### Community 21 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (8): DemoData, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest, Illuminate\Console\Command

### Community 23 - "Illuminate\Http\JsonResponse"
Cohesion: 0.13
Nodes (4): MachineController, QrController, UserController, Illuminate\Http\JsonResponse

### Community 24 - "KioskQrView.vue"
Cohesion: 0.13
Nodes (24): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, clearIntervals(), currentToken, expiresInSec, generateQr() (+16 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.09
Nodes (18): LoginView(), googleLoginEnabled, auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading (+10 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.12
Nodes (16): ActivityView(), filterActivityFeed(), KIND_FOR_FILTER, mergeActivityFeed(), RANGE_DAYS, feed, filters, hasLoadError (+8 more)

### Community 27 - "Closure"
Cohesion: 0.10
Nodes (13): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, Closure (+5 more)

### Community 28 - "router/index.js"
Cohesion: 0.05
Nodes (43): AdminView(), GoodbyeView(), GoogleCallbackView(), KioskLandingView(), LandingView(), NotFoundView(), PrivacyView(), RegisterView() (+35 more)

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

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "DemoDataGenerator"
Cohesion: 0.06
Nodes (9): AiService, DemoDataGenerator, Carbon, RewardConfigService, GuestCarbonTest, HardwareCompactorProxyTest, Carbon\Carbon, Carbon\CarbonInterface (+1 more)

### Community 40 - "auth.js"
Cohesion: 0.19
Nodes (17): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+9 more)

### Community 41 - "api.js"
Cohesion: 0.17
Nodes (14): mounted(), prefersReducedMotion(), app, i18n, pinia, router, api, hasMachineKey (+6 more)

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

### Community 48 - "vue"
Cohesion: 0.12
Nodes (15): activeKey, route, VoucherView(), NAV_ITEMS, resolveActiveNavKey(), auth, error, loading (+7 more)

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

### Community 94 - "AdminLog"
Cohesion: 0.19
Nodes (3): AdminLog, DemoDataGeneratorTest, Carbon

### Community 95 - "RvmMachine"
Cohesion: 0.11
Nodes (7): RvmMachine, AdminExportExcelTest, DetectionLogTest, ExpireStaleSessionsTest, WeighAwardsConfiguredPointsTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "fmtTime"
Cohesion: 0.40
Nodes (5): fmtTime(), formatDate(), timeOnly(), txDateTime(), updateClock()

### Community 100 - "MachineApiTest"
Cohesion: 0.13
Nodes (4): create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 101 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 102 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

### Community 103 - "QrSession"
Cohesion: 0.16
Nodes (5): ExpireStaleSessions, QrSession, EnforceIdleTimeoutTest, QrLifetimeTest, Illuminate\Support\Carbon

### Community 104 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 105 - "AdminController.php"
Cohesion: 0.18
Nodes (8): GoogleCallbackAccountLinkTest, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Storage, Laravel\Socialite\Two\User, Mockery, PhpOffice\PhpSpreadsheet\Writer\Xlsx

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.14
Nodes (14): actionTaken, closeError, closeRecord(), closingId, errorMessage(), form, formError, load() (+6 more)

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
Cohesion: 0.25
Nodes (11): depositItem(), awardPoints(), claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), randomPointsFallback() (+3 more)

### Community 117 - "machineAlerts.test.js"
Cohesion: 0.31
Nodes (8): buildMachineAlerts(), connectivitySummary(), machineConnectivity(), ONLINE_WINDOW_MS, now, toMs(), machineAlerts, machineSummary

### Community 118 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 121 - "api.php"
Cohesion: 0.25
Nodes (5): RouteServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route

### Community 124 - "SessionDetailModal.vue"
Cohesion: 0.22
Nodes (6): items, loadError, loading, props, summary, { t, te }

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
Cohesion: 0.32
Nodes (5): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault(), machineLocked()

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 135 - "autoStartFlow"
Cohesion: 0.38
Nodes (7): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), recycleAgain(), refreshChamber(), takeBackSwitch()

### Community 138 - "Handler"
Cohesion: 0.33
Nodes (3): BaseHTTPRequestHandler, Handler, Local-only companion for the RVM kiosk's admin "Maintenance" action. Runs as…

### Community 139 - "rvm-kiosk-launch.sh"
Cohesion: 0.60
Nodes (5): new_bytes(), rvm-kiosk-launch.sh script, size_of(), start_ai(), uptime_s()

### Community 140 - "scanMaintFrame"
Cohesion: 0.47
Nodes (6): closeMaintenance(), scanMaintFrame(), startMaintCamera(), stopMaintCamera(), submitMaintenance(), toggleMaintCamera()

### Community 143 - "startCameraMode"
Cohesion: 0.40
Nodes (5): captureFromCamera(), captureFromHardware(), openCamera(), startCameraMode(), stopCamera()

### Community 144 - "startLimitEnd"
Cohesion: 0.50
Nodes (4): flushChamber(), confirmEndSession(), materialLimit(), startLimitEnd()

### Community 145 - "redeemNow"
Cohesion: 0.50
Nodes (4): deleteAccount(), loadRedemptionHistory(), redeemNow(), showToast

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

## Knowledge Gaps
- **552 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+547 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 902 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **44 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AuthController.php`, `RewardVoucherTest`, `AuthVerificationGateTest`, `Illuminate\Http\Request`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `AdminUpdateUserTest`, `FormalReportService`, `RecyclingSession`, `AuthController`, `AdminCarbonStatsTest`, `AdminPaginationTest`, `DetectionLog`, `DemoDataGenerator`, `AdminRewardConfigTest`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `Controller`, `AdminLog`, `RvmMachine`, `AdminEmailFormalReportTest`, `QrSession`, `AdminController.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `MaintenanceRecordsTest`, `AdminKioskMaintenanceTest`, `AdminCompactorSettingsTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Why does `TestCase` connect `User` to `RewardVoucherTest`, `AuthVerificationGateTest`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `AdminUpdateUserTest`, `GoogleRedirectFrontendOriginTest`, `FormalReportService`, `RecyclingSession`, `AdminCarbonStatsTest`, `AdminPaginationTest`, `DetectionLog`, `ExampleTest`, `DemoDataGenerator`, `AdminRewardConfigTest`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `Test Application Bootstrap`, `AdminLog`, `RvmMachine`, `AdminEmailFormalReportTest`, `QrSession`, `AdminController.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `MaintenanceRecordsTest`, `AdminKioskMaintenanceTest`, `AdminCompactorSettingsTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `AdminView.vue`, `RvmSessionView.vue`, `FrontEnd/package.json`, `UserSettingsView.vue`, `ScanView.vue`, `RegisterView.vue`, `KioskQrView.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `SessionSummaryView.vue`, `DashboardView.vue`, `AdminPagination.vue`, `auth.js`, `api.js`, `App.vue`, `ImageCropper.vue`, `MaintenanceHistoryModal.vue`, `SessionDetailModal.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _552 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.011428571428571429 - nodes in this community are weakly interconnected._
- **Should `AuthController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06184012066365008 - nodes in this community are weakly interconnected._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._