# Graph Report - RVM  (2026-11-13)

## Corpus Check
- 342 files · ~250,953 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2706 nodes · 5787 edges · 185 communities (99 shown, 46 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 110 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `669336fc`
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
- RewardController
- GpioDriver
- ScanView.vue
- FormalReportService
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- Controller
- AuthController
- AdminBinCollectionEmailTest
- AdminPaginationTest
- fetchTabData
- ai_service/app.py
- DetectionLog
- ActiveSessionSheet.vue
- vitest
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
- PointsHistory
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- vue
- api.js
- DemoKioskSessionView.vue
- AdminRewardConfigTest
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- CarbonService
- UserController
- Console Kernel Scheduling
- AdminCachingTest
- RewardItemAvailabilityTest
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- RewardRedemption
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
- autoStartFlow
- Transaction
- AdminKioskMaintenanceTest
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- QrSession
- AdminCompactorSettingsTest
- GoogleCallbackAccountLinkTest.php
- MaintenanceHistoryModal.vue
- saveDetectionReview
- PlaceholderDriver
- compactorFlow.js
- static
- MachineHeartbeatTest
- AccountSuspensionTest
- CashRedeemTest
- RecordingDriver
- heartbeat.js
- depositAndWait
- DemoDataGenerator
- normalize_material
- KioskLandingView.vue
- Illuminate\Support\Facades\Schema
- avatarIcons.js
- AdminTransactionFiltersTest
- GoogleRedirectFrontendOriginTest
- VoucherModal.vue
- formatAuditLog
- test_yolo.py
- startCameraMode
- _UsbCameraWrapper
- dataScope.test.js
- adminTabState.js
- CompactorSettingsService
- startLimitEnd
- MaintenanceRecordsTest
- AdminAuditLogTest
- AdminCashRedeemSettingsTest
- Handler
- rvm-kiosk-launch.sh
- scanMaintFrame
- redeemNow
- AdminController.php
- index.php
- awardPoints
- vercel.json
- VoucherView.vue
- Illuminate\Database\Schema\Blueprint
- ExpireStaleSessionsTest.php
- normalizeMachine
- DemoSessionMachineTest
- cancelEditPassword
- ai_service_4bin/README.md
- start.sh
- num
- AdminEmailFormalReportTest
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- UserAvatar.vue
- RvmMachine
- .makeFixture
- useIdleLogout
- setRewardImageFile
- @phosphor-icons/vue
- isDemoKioskPath
- kioskDemo.js
- Authenticate.php
- tabLoading.js
- fmtTime

## God Nodes (most connected - your core abstractions)
1. `User` - 181 edges
2. `RvmMachine` - 129 edges
3. `TestCase` - 121 edges
4. `RecyclingSession` - 92 edges
5. `Transaction` - 75 edges
6. `AdminController` - 62 edges
7. `RewardItem` - 59 edges
8. `RewardRedemption` - 47 edges
9. `PointsHistory` - 45 edges
10. `vitest` - 43 edges

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

## Communities (185 total, 46 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (159): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+151 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (40): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+32 more)

### Community 4 - "Illuminate\Http\JsonResponse"
Cohesion: 0.14
Nodes (4): MachineController, TransactionController, Illuminate\Http\JsonResponse, self

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (40): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+32 more)

### Community 7 - "User"
Cohesion: 0.07
Nodes (21): User, AdminRedemptionsListTest, AuthControllerLocaleTest, ExampleTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, RewardHistoryTest (+13 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (4): AdminController, AdminTableExport, Illuminate\Database\Eloquent\Builder, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.11
Nodes (6): RewardItem, AdminRewardItemsCrudTest, RewardCatalogTest, RewardRedeemTest, Illuminate\Http\UploadedFile, UploadedFile

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.09
Nodes (27): extractQrToken(), activeMachines, auth, cameraActive, cameraError, cameraSupported, canvasRef, error (+19 more)

### Community 13 - "FormalReportService"
Cohesion: 0.07
Nodes (16): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, AdminTableExportTest, FormalReportServiceTest, Illuminate\Testing\TestResponse, PhpOffice\PhpSpreadsheet\Cell\Coordinate, PhpOffice\PhpSpreadsheet\IOFactory (+8 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (23): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp() (+15 more)

### Community 16 - "Controller"
Cohesion: 0.29
Nodes (5): Controller, SessionController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

### Community 17 - "AuthController"
Cohesion: 0.16
Nodes (3): AuthController, SecurityLog, Illuminate\Http\RedirectResponse

### Community 20 - "fetchTabData"
Cohesion: 0.07
Nodes (32): isFresh(), changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage() (+24 more)

### Community 21 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (9): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest (+1 more)

### Community 23 - "ActiveSessionSheet.vue"
Cohesion: 0.11
Nodes (17): { session }, sheetOpen, showLocation, summary, closeBtn, emit, items, props (+9 more)

### Community 24 - "vitest"
Cohesion: 0.06
Nodes (37): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+29 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.06
Nodes (32): ActivityView(), activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, history, redemptions (+24 more)

### Community 27 - "Closure"
Cohesion: 0.10
Nodes (13): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, Closure (+5 more)

### Community 28 - "router/index.js"
Cohesion: 0.07
Nodes (35): AdminView(), DemoKioskSessionView(), demoMachineGuard(), GoodbyeView(), GoogleCallbackView(), NotFoundView(), PrivacyView(), routes (+27 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "LandingView.vue"
Cohesion: 0.12
Nodes (15): LandingView(), acceptedChips, barLabel, intlLocale, itemsText(), { locale, t }, MONTH_MATERIALS, monthLabel (+7 more)

### Community 31 - "showToast"
Cohesion: 0.10
Nodes (25): cleanParams(), downloadExcel(), excelFilename(), buildRewardUpdatePayload(), addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize() (+17 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.13
Nodes (16): SessionSummaryView(), setKioskToken(), auth, clearCountdown(), countdown, earnedPoints, finalPoints, goHome() (+8 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.24
Nodes (10): AI_CONFIDENCE_MIN, BIN_LETTERS, confidenceLevel(), itemLabel(), MATERIAL_NAMES, percentOf(), RESULT_CODES, resultReason() (+2 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.11
Nodes (14): DashboardView(), demoDataLabel, googleLoginEnabled, MATERIAL_ICON_PATHS, materialIconSvg(), auth, AVAILABILITY_LABEL, binTypes (+6 more)

### Community 36 - "PointsHistory"
Cohesion: 0.16
Nodes (3): PointsHistory, AdminUpdateUserTest, TransactionCompleteAtomicityTest

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.12
Nodes (25): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, base, clearIntervals(), currentToken, expiresInSec (+17 more)

### Community 40 - "vue"
Cohesion: 0.24
Nodes (10): useActiveSession(), ACTIVITY_EVENTS, registerClearAuth(), useAuthStore, clearIdleActivity(), IDLE_ACTIVITY_STORAGE_KEY, readIdleActivity(), resetIdleActivity() (+2 more)

### Community 41 - "api.js"
Cohesion: 0.15
Nodes (14): mounted(), prefersReducedMotion(), app, i18n, pinia, router, api, hasMachineKey (+6 more)

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (33): aiConfidence, auth, autoStartFlow(), awardPoints(), cameraCountdown, cameraFlash, chooseItem(), chosenId (+25 more)

### Community 44 - "App.vue"
Cohesion: 0.15
Nodes (14): auth, route, showAppNav, showKioskDemo, showToast(), theme, toastState, toggleTheme() (+6 more)

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.14
Nodes (22): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+14 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "CarbonService"
Cohesion: 0.15
Nodes (6): PublicStatsController, CarbonService, ActiveSessionTest, CarbonServiceTest, ExampleTest, PHPUnit\Framework\TestCase

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

### Community 60 - "RewardRedemption"
Cohesion: 0.15
Nodes (3): RewardRedemption, AccountSummaryTest, RewardVoucherTest

### Community 61 - "ImageCropper.vue"
Cohesion: 0.11
Nodes (35): baseName(), exportFile(), frame, frameEl, framePoint(), imageStyle, imageUrl, load() (+27 more)

### Community 72 - "isReviewable"
Cohesion: 0.27
Nodes (11): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 92 - "Machine"
Cohesion: 0.17
Nodes (3): Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…

### Community 93 - "autoStartFlow"
Cohesion: 0.32
Nodes (8): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), machineLocked(), recycleAgain(), refreshChamber(), takeBackSwitch()

### Community 94 - "Transaction"
Cohesion: 0.08
Nodes (7): AdminLog, MaintenanceRecord, RewardItemImage, Transaction, DemoDataGeneratorTest, Carbon, Illuminate\Database\Eloquent\Model

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.06
Nodes (29): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest (+21 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.13
Nodes (4): create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "QrSession"
Cohesion: 0.14
Nodes (8): QrController, QrSession, RouteServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route, SimpleSoftwareIO\QrCode\Facades\QrCode

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
Cohesion: 0.24
Nodes (13): ACCEPTED_2BIN, AUTO_END_SECONDS, limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL, sessionLimitReached() (+5 more)

### Community 110 - "static"
Cohesion: 0.38
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 115 - "heartbeat.js"
Cohesion: 0.38
Nodes (4): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault()

### Community 116 - "depositAndWait"
Cohesion: 0.31
Nodes (9): depositItem(), claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), recordInvalidItem(), simulateInsert() (+1 more)

### Community 117 - "DemoDataGenerator"
Cohesion: 0.06
Nodes (9): AiService, DemoDataGenerator, Carbon, FonnteService, RewardConfigService, GuestCarbonTest, HardwareCompactorProxyTest, Carbon\Carbon (+1 more)

### Community 118 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 120 - "KioskLandingView.vue"
Cohesion: 0.18
Nodes (9): KioskLandingView(), base, checked, isDemoRoute, machineLocation, machineName, notDemo, route (+1 more)

### Community 122 - "avatarIcons.js"
Cohesion: 0.39
Nodes (6): AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR, formatAvatar(), maskEmail(), draftValue

### Community 125 - "VoucherModal.vue"
Cohesion: 0.18
Nodes (14): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t } (+6 more)

### Community 126 - "formatAuditLog"
Cohesion: 0.33
Nodes (7): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditRows

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 128 - "startCameraMode"
Cohesion: 0.40
Nodes (5): captureFromCamera(), captureFromHardware(), openCamera(), startCameraMode(), stopCamera()

### Community 129 - "_UsbCameraWrapper"
Cohesion: 0.23
Nodes (5): Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, RuntimeError

### Community 130 - "dataScope.test.js"
Cohesion: 0.23
Nodes (7): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), fetchChartData(), setDataScope()

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 134 - "startLimitEnd"
Cohesion: 0.50
Nodes (4): flushChamber(), confirmEndSession(), materialLimit(), startLimitEnd()

### Community 138 - "Handler"
Cohesion: 0.33
Nodes (3): BaseHTTPRequestHandler, Handler, Local-only companion for the RVM kiosk's admin "Maintenance" action. Runs as…

### Community 139 - "rvm-kiosk-launch.sh"
Cohesion: 0.60
Nodes (5): new_bytes(), rvm-kiosk-launch.sh script, size_of(), start_ai(), uptime_s()

### Community 140 - "scanMaintFrame"
Cohesion: 0.47
Nodes (6): closeMaintenance(), scanMaintFrame(), startMaintCamera(), stopMaintCamera(), submitMaintenance(), toggleMaintCamera()

### Community 141 - "redeemNow"
Cohesion: 0.33
Nodes (6): deleteAccount(), loadRedemptionHistory(), loadSummary(), redeemNow(), saveAvatar(), showToast

### Community 142 - "AdminController.php"
Cohesion: 0.08
Nodes (11): SecurityLogTest, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Storage, Illuminate\Support\Facades\Validator (+3 more)

### Community 144 - "awardPoints"
Cohesion: 0.48
Nodes (5): isNetworkError(), newIdempotencyKey(), retryOnce(), awardPoints(), randomPointsFallback()

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 148 - "VoucherView.vue"
Cohesion: 0.15
Nodes (9): VoucherView(), auth, error, loading, markError, marking, route, { t } (+1 more)

### Community 153 - "ExpireStaleSessionsTest.php"
Cohesion: 0.13
Nodes (6): EnforceIdleTimeoutTest, ExpireStaleSessionsTest, QrLifetimeTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Carbon, Illuminate\Support\Facades\Artisan

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 170 - "UserAvatar.vue"
Cohesion: 0.29
Nodes (7): iconSize, initial, picked, props, style, parseAvatar(), openAvatarSheet()

### Community 173 - "RvmMachine"
Cohesion: 0.09
Nodes (8): RecyclingSession, RvmMachine, AdminCarbonStatsTest, AdminExportExcelTest, DetectionLogTest, PublicStatsTest, SessionSummaryReceiptTest, WeighAwardsConfiguredPointsTest

### Community 178 - "useIdleLogout"
Cohesion: 0.42
Nodes (9): useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire(), onActivity() (+1 more)

### Community 184 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

### Community 186 - "@phosphor-icons/vue"
Cohesion: 0.46
Nodes (5): activeKey, route, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

### Community 187 - "isDemoKioskPath"
Cohesion: 0.38
Nodes (6): shouldAutoReturn(), startCountdown(), isDemoKioskPath(), kioskBase(), pathOf(), confirmEndSession()

### Community 188 - "kioskDemo.js"
Cohesion: 0.48
Nodes (5): DEMO_ITEMS, RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence()

### Community 194 - "fmtTime"
Cohesion: 0.50
Nodes (4): fmtTime(), formatDate(), timeOnly(), updateClock()

## Knowledge Gaps
- **647 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+642 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1036 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **46 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `MaintenanceRecordsTest`, `Illuminate\Http\Request`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `FormalReportService`, `AdminController.php`, `Controller`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `DetectionLog`, `ExpireStaleSessionsTest.php`, `Closure`, `DemoSessionMachineTest`, `AdminEmailFormalReportTest`, `PointsHistory`, `AdminRewardConfigTest`, `RvmMachine`, `CarbonService`, `.makeFixture`, `AdminCachingTest`, `RewardRedemption`, `Transaction`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest.php`, `static`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `DemoDataGenerator`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `AdminView.vue`, `RvmSessionView.vue`, `UserSettingsView.vue`, `ScanView.vue`, `RegisterView.vue`, `VoucherView.vue`, `ActiveSessionSheet.vue`, `vitest`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `LandingView.vue`, `SessionSummaryView.vue`, `DashboardView.vue`, `KioskQrView.vue`, `api.js`, `UserAvatar.vue`, `DemoKioskSessionView.vue`, `App.vue`, `@phosphor-icons/vue`, `ImageCropper.vue`, `FrontEnd/package.json`, `MaintenanceHistoryModal.vue`, `KioskLandingView.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `Illuminate\Http\JsonResponse`, `User`, `Illuminate\Http\Request`, `MaintenanceRecordsTest`, `FormalReportService`, `AdminController.php`, `Controller`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `DetectionLog`, `ExpireStaleSessionsTest.php`, `Closure`, `DemoSessionMachineTest`, `AdminEmailFormalReportTest`, `PointsHistory`, `CarbonService`, `.makeFixture`, `RewardRedemption`, `Transaction`, `AdminKioskMaintenanceTest`, `DemoFlagTest`, `QrSession`, `MachineHeartbeatTest`, `DemoDataGenerator`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _647 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.01098901098901099 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._