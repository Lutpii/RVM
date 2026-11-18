# Graph Report - RVM  (2026-11-18)

## Corpus Check
- 456 files · ~301,560 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3327 nodes · 7384 edges · 214 communities (115 shown, 54 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 134 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `820071a3`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- BinCollectionRequested
- Composer Configuration
- RvmSessionView.vue
- ai_service/app.py
- Illuminate\Database\Migrations\Migration
- UserSettingsView.vue
- TestCase
- Illuminate\Http\Request
- RewardItem
- RewardRedemption
- GpioDriver
- ScanView.vue
- FormalReportService
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- AdminCachingTest
- AuthController
- AdminBinCollectionEmailTest
- AdminPaginationTest
- fetchTabData
- SessionStatusAndGraceTest
- DetectionLog
- CarbonService
- vitest
- LoginView.vue
- ActivityView.vue
- Illuminate\Http\JsonResponse
- router/index.js
- RewardsView.vue
- App.vue
- showToast
- SessionSummaryView.vue
- transactionDisplay.js
- Backend Build Config
- DemoDataGenerator
- SessionFloat.vue
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- vue
- GpioDriverTest
- DemoKioskSessionView.vue
- machineAlerts.test.js
- Illuminate\Console\Command
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- ActiveSessionSheet.vue
- Closure
- Console Kernel Scheduling
- RvmMachine
- auth.js
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- AdminPagination.vue
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
- LocationCard.vue
- @phosphor-icons/vue
- tapGesture.test.js
- swipe.js
- SessionDetailModal.vue
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- AppSelect.vue
- AdminCompactorSettingsTest
- useLanguage.js
- MaintenanceHistoryModal.vue
- compactorFlow.js
- PlaceholderDriver
- brandLocales.test.js
- _UsbCameraWrapper
- MachineHeartbeatTest
- AdminLog
- CashRedeemTest
- RecordingDriver
- Carbon\Carbon
- formatUserId
- AiService
- RecyclingSession
- AccountSuspensionTest
- saveDetectionReview
- AdminController.php
- UserAvatar.vue
- AdminTransactionFiltersTest
- Transaction
- VoucherModal.vue
- intl
- test_yolo.py
- PlatformMachineDashboard.vue
- SessionController
- dataScope.test.js
- adminTabState.js
- DashboardView.vue
- normalize_material
- LanguageSwitch.vue
- vue-i18n
- Controller
- Handler
- rvm-kiosk-launch.sh
- extractQrToken
- redeemNow
- AdminPlatformTest
- UserController.php
- devDependencies
- vercel.json
- CompactorSettingsService
- Illuminate\Support\Facades\Schema
- MachineTypeIsolationTest
- simulateInsert
- Illuminate\Database\Schema\Blueprint
- PlatformCreditTest
- normalizeMachine
- AdminPlatformSettingsTest
- AdminKioskMaintenanceTest
- FonnteService
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- ExpireStaleSessionsTest
- heartbeat.js
- RewardConfigService
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- awardPoints
- autoStartFlow
- HardwareError
- QrSession
- HardwareCompactorProxyTest
- countMachinesInScope
- scrollReveal.js
- OneMachineRuleTest
- PlatformSimulation
- PlatformController
- User
- machineCodes.js
- scripts
- index.php
- 2026_11_15_000001_add_display_no_to_users_table.php
- PlatformCreditService
- startLimitEnd
- Authenticate.php
- voucher.js
- rewardCategory.test.js
- depositAndWait
- FrontEnd/vite.config.js
- platformNavGroups
- PlatformRequest.php
- cancelEditPassword
- num
- LandingView.vue
- localeChecks.test.js

## God Nodes (most connected - your core abstractions)
1. `User` - 233 edges
2. `RvmMachine` - 205 edges
3. `TestCase` - 157 edges
4. `RecyclingSession` - 112 edges
5. `Transaction` - 84 edges
6. `AdminController` - 71 edges
7. `vitest` - 71 edges
8. `RewardItem` - 59 edges
9. `vue` - 55 edges
10. `PointsHistory` - 54 edges

## Surprising Connections (you probably didn't know these)
- `GpioDriverTest` --uses--> `HardwareError`  [INFERRED]
  BackEnd/ai_service/tests/test_gpio_driver.py → BackEnd/ai_service/gpio_driver.py
- `GpioDriverTest` --uses--> `GpioDriver`  [INFERRED]
  BackEnd/ai_service/tests/test_gpio_driver.py → BackEnd/ai_service/gpio_driver.py
- `MachineTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_hardware.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `RecordingDriver`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/tests/test_hardware.py

## Import Cycles
- None detected.

## Communities (214 total, 54 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (176): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+168 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (40): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+32 more)

### Community 4 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (39): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+31 more)

### Community 7 - "TestCase"
Cohesion: 0.05
Nodes (25): AdminRedemptionsListTest, AdminRewardConfigTest, AuthControllerLocaleTest, ExampleTest, GoogleRedirectFrontendOriginTest, IndonesianLocaleTest, MachineOrderTest, PointsHistoryOrderTest (+17 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (3): AdminController, AdminTableExport, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.06
Nodes (9): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, AdminTableExportTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest (+1 more)

### Community 10 - "RewardRedemption"
Cohesion: 0.14
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.13
Nodes (11): GpioDriver, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown)., One relay on for `seconds`; all others off first (interlock)., Run the tilt back until the limit switch is pressed, at most TILT_HOME_TIMEOUT. (+3 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.07
Nodes (30): scanOutcome(), activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported, canvasRef (+22 more)

### Community 13 - "FormalReportService"
Cohesion: 0.06
Nodes (17): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, LastLoginTest, UserDisplayNumberTest, FormalReportServiceTest, Illuminate\Testing\TestResponse, PhpOffice\PhpSpreadsheet\Cell\Coordinate (+9 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (23): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp() (+15 more)

### Community 17 - "AuthController"
Cohesion: 0.17
Nodes (3): AuthController, SecurityLog, Illuminate\Http\RedirectResponse

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (35): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+27 more)

### Community 22 - "DetectionLog"
Cohesion: 0.10
Nodes (5): OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest

### Community 23 - "CarbonService"
Cohesion: 0.06
Nodes (13): PublicStatsController, CarbonService, GoogleMapsLinkService, UserFactory, ActiveSessionTest, CarbonSourcesTest, CarbonServiceTest, ExampleTest (+5 more)

### Community 24 - "vitest"
Cohesion: 0.16
Nodes (9): flattenKeys(), __dirname, leaves(), messages, platformMessages, COMPACTOR_KEYS, __dirname, KEYS (+1 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.06
Nodes (32): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, RECYCLING_KINDS, SIMULATED_SOURCES, history (+24 more)

### Community 27 - "Illuminate\Http\JsonResponse"
Cohesion: 0.14
Nodes (4): CarbonSourcesController, MachineController, TransactionController, Illuminate\Http\JsonResponse

### Community 28 - "router/index.js"
Cohesion: 0.05
Nodes (43): ActivityView(), AdminView(), DemoKioskSessionView(), demoMachineGuard(), GoodbyeView(), GoogleCallbackView(), KioskLandingView(), NotFoundView() (+35 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "App.vue"
Cohesion: 0.15
Nodes (15): auth, route, showAppNav, showKioskDemo, showToast(), theme, toastState, toggleTheme() (+7 more)

### Community 31 - "showToast"
Cohesion: 0.08
Nodes (28): cleanParams(), downloadExcel(), excelFilename(), kioskLink(), buildRewardUpdatePayload(), addRewardItem(), askConfirm(), buildRewardItemFormData() (+20 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.11
Nodes (21): shouldAutoReturn(), startCountdown(), SessionSummaryView(), isDemoKioskPath(), kioskBase(), pathOf(), transactionPoints(), confirmEndSession() (+13 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.17
Nodes (15): AI_CONFIDENCE_MIN, BIN_LETTERS, cap(), confidenceLevel(), isKnown(), itemLabel(), percentOf(), RESULT_CODES (+7 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DemoDataGenerator"
Cohesion: 0.19
Nodes (3): DemoDataGenerator, Carbon, Carbon\CarbonInterface

### Community 36 - "SessionFloat.vue"
Cohesion: 0.12
Nodes (17): summary, auth, isUserRoute, mode, route, shape, store, summary (+9 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.11
Nodes (28): onEnded(), KioskQrView(), setKioskToken(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, base, clearIntervals() (+20 more)

### Community 40 - "vue"
Cohesion: 0.06
Nodes (40): busy, error, found, { t }, url, error, saved, saving (+32 more)

### Community 41 - "GpioDriverTest"
Cohesion: 0.14
Nodes (7): duty(), GpioDriverTest, run(), PWM duty of a servo pulse of `ms` milliseconds at 50 Hz., Switch releases when the tilt drives out; presses again shortly after the…, [(on_time, off_time), ...] of a relay pin., skipIf

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.06
Nodes (42): progress, props, seconds, DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep() (+34 more)

### Community 43 - "machineAlerts.test.js"
Cohesion: 0.27
Nodes (9): buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS, now, machineAlerts (+1 more)

### Community 44 - "Illuminate\Console\Command"
Cohesion: 0.21
Nodes (5): DemoData, ExpireStaleSessions, MachineKey, PlatformTick, Illuminate\Console\Command

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.14
Nodes (22): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+14 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "ActiveSessionSheet.vue"
Cohesion: 0.10
Nodes (15): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+7 more)

### Community 49 - "Closure"
Cohesion: 0.08
Nodes (18): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, EnsurePlatformMode, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader (+10 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 51 - "RvmMachine"
Cohesion: 0.06
Nodes (12): CompactorBatch, ComposterDeposit, Location, RvmMachine, UserImpactService, PlatformPublicTest, PlatformTickTest, PlatformTelemetryTest (+4 more)

### Community 52 - "auth.js"
Cohesion: 0.16
Nodes (20): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+12 more)

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

### Community 60 - "AdminPagination.vue"
Cohesion: 0.22
Nodes (11): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+3 more)

### Community 61 - "ImageCropper.vue"
Cohesion: 0.11
Nodes (35): baseName(), exportFile(), frame, frameEl, framePoint(), imageStyle, imageUrl, load() (+27 more)

### Community 72 - "isReviewable"
Cohesion: 0.24
Nodes (12): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+4 more)

### Community 92 - "Machine"
Cohesion: 0.15
Nodes (5): chamber_capacity(), Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default.

### Community 93 - "LocationCard.vue"
Cohesion: 0.29
Nodes (7): icons, { t }, isRvmMachine(), pillTone(), statusPill(), tones, isRvmCard()

### Community 94 - "@phosphor-icons/vue"
Cohesion: 0.12
Nodes (14): activeKey, route, VoucherView(), NAV_ITEMS, resolveActiveNavKey(), auth, error, loading (+6 more)

### Community 95 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 97 - "SessionDetailModal.vue"
Cohesion: 0.14
Nodes (10): closeBtn, emit, items, loadError, loading, props, summary, { t, te } (+2 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.15
Nodes (12): axios, vite, name, private, version, autoprefixer, chart.js, jsqr (+4 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.11
Nodes (5): Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "AppSelect.vue"
Cohesion: 0.16
Nodes (19): button, choose(), close(), emit, enabled, focusItem(), hasValue, label (+11 more)

### Community 105 - "useLanguage.js"
Cohesion: 0.30
Nodes (9): applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, isLanguage(), LANGUAGES, resolveStoredLanguage() (+1 more)

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (18): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+10 more)

### Community 107 - "compactorFlow.js"
Cohesion: 0.24
Nodes (13): ACCEPTED_2BIN, AUTO_END_SECONDS, limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL, sessionLimitReached() (+5 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.21
Nodes (3): PlaceholderDriver, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "brandLocales.test.js"
Cohesion: 0.19
Nodes (5): adminUi, SOURCES, used, LANGS, content

### Community 110 - "_UsbCameraWrapper"
Cohesion: 0.23
Nodes (5): Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, RuntimeError

### Community 112 - "AdminLog"
Cohesion: 0.07
Nodes (6): AdminLog, MaintenanceRecord, AdminAuditLogTest, DemoDataGeneratorTest, Carbon, MaintenanceRecordsTest

### Community 118 - "RecyclingSession"
Cohesion: 0.10
Nodes (6): RecyclingSession, AdminExportExcelTest, DetectionLogTest, MachineDeleteGuardTest, PublicStatsTest, WeighAwardsConfiguredPointsTest

### Community 120 - "saveDetectionReview"
Cohesion: 0.16
Nodes (15): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+7 more)

### Community 121 - "AdminController.php"
Cohesion: 0.09
Nodes (12): GoogleCallbackAccountLinkTest, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Validator, Illuminate\Support\Str (+4 more)

### Community 122 - "UserAvatar.vue"
Cohesion: 0.18
Nodes (13): iconSize, initial, picked, props, style, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR (+5 more)

### Community 124 - "Transaction"
Cohesion: 0.08
Nodes (7): PointsHistory, Transaction, AccountSummaryTest, AdminUpdateUserTest, PointsHistoryLocaleTest, TransactionCompleteAtomicityTest, TransactionRejectNoDeductionTest

### Community 125 - "VoucherModal.vue"
Cohesion: 0.25
Nodes (8): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t }

### Community 126 - "intl"
Cohesion: 0.13
Nodes (18): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditLabel(), auditMessage() (+10 more)

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 128 - "PlatformMachineDashboard.vue"
Cohesion: 0.08
Nodes (28): fields, fields, across, avgOf(), barOptions, co2Week, color, donutData (+20 more)

### Community 129 - "SessionController"
Cohesion: 0.11
Nodes (5): QrController, SessionController, PlatformFlagTest, Illuminate\Support\Facades\Route, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 130 - "dataScope.test.js"
Cohesion: 0.17
Nodes (9): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), DAYS, weekdayLabel(), fetchChartData() (+1 more)

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "DashboardView.vue"
Cohesion: 0.08
Nodes (20): DashboardView(), demoDataLabel, googleLoginEnabled, platformMode, splitColumns(), auth, AVAILABILITY_LABEL, binTypes (+12 more)

### Community 134 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 135 - "LanguageSwitch.vue"
Cohesion: 0.20
Nodes (11): act(), button, choose(), { locale, languages, currentLanguage, setLanguage }, onKey, onPointer(), open, root (+3 more)

### Community 136 - "vue-i18n"
Cohesion: 0.08
Nodes (28): fmt(), activity, error, page, pending, props, { t }, barData (+20 more)

### Community 137 - "Controller"
Cohesion: 0.23
Nodes (5): PlatformAdminController, Controller, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

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

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 151 - "simulateInsert"
Cohesion: 0.20
Nodes (11): emit, onPaste(), resolve(), captureFromCamera(), captureFromHardware(), drawBoundingBoxes(), openCamera(), recordInvalidItem() (+3 more)

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 164 - "heartbeat.js"
Cohesion: 0.32
Nodes (5): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault(), machineLocked()

### Community 168 - "RewardConfigService"
Cohesion: 0.17
Nodes (3): PlatformSettingsService, RewardConfigService, Illuminate\Filesystem\Filesystem

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 170 - "awardPoints"
Cohesion: 0.43
Nodes (6): isNetworkError(), newIdempotencyKey(), retryOnce(), saveUserItem(), awardPoints(), randomPointsFallback()

### Community 173 - "autoStartFlow"
Cohesion: 0.47
Nodes (6): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), recycleAgain(), refreshChamber()

### Community 175 - "HardwareError"
Cohesion: 0.40
Nodes (4): HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, A move failed (e.g. the tilt never reached its limit switch). All relays are…, GpioDriver on gpiozero's mock pins (no Raspberry Pi needed). Skipped where…

### Community 176 - "QrSession"
Cohesion: 0.13
Nodes (5): QrSession, DemoSessionMachineTest, EnforceIdleTimeoutTest, QrLifetimeTest, Illuminate\Support\Carbon

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 188 - "User"
Cohesion: 0.07
Nodes (11): User, AdminCarbonStatsTest, AdminEmailFormalReportTest, AdminMapsLinkTest, SecurityLogTest, CollidingUser, UserPlatformImpactTest, Illuminate\Database\UniqueConstraintViolationException (+3 more)

### Community 189 - "machineCodes.js"
Cohesion: 0.50
Nodes (3): plannedCodes(), SUFFIX, plannedMachineCodes

### Community 190 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 198 - "startLimitEnd"
Cohesion: 0.50
Nodes (5): flushChamber(), confirmEndSession(), materialLimit(), { showOverlay: showIdleOverlay, msLeft: idleMsLeft, poke: pokeIdle, sessionEnded, endingSession }, startLimitEnd()

### Community 202 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 203 - "rewardCategory.test.js"
Cohesion: 0.53
Nodes (3): KNOWN, rewardCategoryKey(), rewardCategoryLabel()

### Community 205 - "depositAndWait"
Cohesion: 0.38
Nodes (7): depositItem(), claimSwitchDecision(), continueSwitch, depositAndWait(), openMaterialSwitch(), stopSwitchTimer(), takeBackSwitch()

### Community 206 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 207 - "platformNavGroups"
Cohesion: 0.50
Nodes (3): platformNavGroups(), t(), groupedNav

### Community 219 - "LandingView.vue"
Cohesion: 0.06
Nodes (31): el, props, reducedMotion(), run(), seen, shown, text, machines (+23 more)

## Knowledge Gaps
- **770 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+765 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1213 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **54 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `SessionController`, `TestCase`, `Illuminate\Http\Request`, `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AdminCachingTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `MachineTypeIsolationTest`, `PlatformCreditTest`, `AdminPlatformSettingsTest`, `AdminKioskMaintenanceTest`, `AdminCashRedeemSettingsTest`, `DemoDataGenerator`, `ExpireStaleSessionsTest`, `QrSession`, `Closure`, `RvmMachine`, `OneMachineRuleTest`, `PlatformSimulation`, `PlatformCreditService`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `MachineHeartbeatTest`, `AdminLog`, `CashRedeemTest`, `RecyclingSession`, `AccountSuspensionTest`, `AdminController.php`, `AdminTransactionFiltersTest`, `Transaction`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `SessionController`, `BinCollectionRequested`, `TestCase`, `Illuminate\Http\Request`, `Controller`, `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `MachineTypeIsolationTest`, `CarbonService`, `PlatformCreditTest`, `Illuminate\Http\JsonResponse`, `AdminKioskMaintenanceTest`, `DemoDataGenerator`, `ExpireStaleSessionsTest`, `Illuminate\Console\Command`, `QrSession`, `Closure`, `OneMachineRuleTest`, `PlatformSimulation`, `PlatformController`, `User`, `PlatformCreditService`, `DemoFlagTest`, `MachineHeartbeatTest`, `AdminLog`, `Carbon\Carbon`, `RecyclingSession`, `AdminController.php`, `AdminTransactionFiltersTest`, `Transaction`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `PlatformMachineDashboard.vue`, `AdminView.vue`, `RvmSessionView.vue`, `DashboardView.vue`, `UserSettingsView.vue`, `LanguageSwitch.vue`, `vue-i18n`, `ScanView.vue`, `RegisterView.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `App.vue`, `SessionSummaryView.vue`, `SessionFloat.vue`, `KioskQrView.vue`, `DemoKioskSessionView.vue`, `ActiveSessionSheet.vue`, `auth.js`, `AdminPagination.vue`, `ImageCropper.vue`, `LandingView.vue`, `@phosphor-icons/vue`, `SessionDetailModal.vue`, `FrontEnd/package.json`, `AppSelect.vue`, `useLanguage.js`, `MaintenanceHistoryModal.vue`, `UserAvatar.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _770 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.00999950741342791 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._