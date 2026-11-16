# Graph Report - RVM  (2026-11-16)

## Corpus Check
- 448 files · ~289,038 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3250 nodes · 7246 edges · 219 communities (117 shown, 57 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 130 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `52358311`
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
- User
- Illuminate\Http\Request
- RewardItem
- RewardController
- GpioDriver
- ScanView.vue
- FormalReportService
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- AdminEmailFormalReportTest
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
- LandingView.vue
- showToast
- SessionSummaryView.vue
- transactionDisplay.js
- Backend Build Config
- Carbon\Carbon
- api.js
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- vue
- GpioDriverTest
- DemoKioskSessionView.vue
- ActiveSessionSheet.vue
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- PointsHistory
- Closure
- Console Kernel Scheduling
- AdminCachingTest
- useIdleLogout.js
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
- tapGesture.test.js
- Transaction
- machineAlerts.test.js
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- AppSelect.vue
- AdminCompactorSettingsTest
- GoogleCallbackAccountLinkTest
- MaintenanceHistoryModal.vue
- LanguageSwitch.vue
- PlaceholderDriver
- compactorFlow.js
- activityTimeline.test.js
- MachineHeartbeatTest
- AccountSuspensionTest
- CashRedeemTest
- RecordingDriver
- QrSession
- Illuminate\Support\Str
- AiService
- RvmMachine
- api.php
- saveDetectionReview
- AdminController.php
- UserAvatar.vue
- AdminTransactionFiltersTest
- AccountSummaryTest
- VoucherModal.vue
- intl
- test_yolo.py
- MonitorPanel.vue
- Controller
- dataScope.test.js
- adminTabState.js
- DashboardView.vue
- normalize_material
- SessionDetailModal.vue
- intlLocale
- Illuminate\Database\Eloquent\Builder
- Handler
- rvm-kiosk-launch.sh
- scanMaintFrame
- redeemNow
- AdminPlatformTest
- awardPoints
- devDependencies
- vercel.json
- CompactorSettingsService
- Illuminate\Support\Facades\Schema
- Authenticate.php
- brandLocales.test.js
- Illuminate\Database\Schema\Blueprint
- _UsbCameraWrapper
- normalizeMachine
- AdminPlatformSettingsTest
- AdminTableExportTest
- @phosphor-icons/vue
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- qrToken.js
- useLanguage.js
- AdminRewardConfigTest
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- GoogleRedirectFrontendOriginTest
- LocationCard.vue
- RecyclingSession
- DemoSessionMachineTest
- AdminAuditLogTest
- countMachinesInScope
- MaintenanceRecordsTest
- OneMachineRuleTest
- rvm.js
- downloadExcel
- HardwareError
- machineCodes.js
- scripts
- index.php
- 2026_11_15_000001_add_display_no_to_users_table.php
- IdleOverlay.vue
- scanOutcome
- VoucherView.vue
- voucher.js
- AdminCarbonStatsTest
- rewardCategory.test.js
- FrontEnd/vite.config.js
- platformNavGroups
- localeChecks.test.js
- cancelEditPassword
- num
- AvatarIcon
- AdminKioskMaintenanceTest
- vue-i18n
- EnforceIdleTimeoutTest
- AdminUpdateUserTest
- main.js
- PlatformRequest.php

## God Nodes (most connected - your core abstractions)
1. `User` - 230 edges
2. `RvmMachine` - 203 edges
3. `TestCase` - 155 edges
4. `RecyclingSession` - 112 edges
5. `Transaction` - 84 edges
6. `AdminController` - 70 edges
7. `vitest` - 70 edges
8. `RewardItem` - 59 edges
9. `PointsHistory` - 54 edges
10. `vue` - 53 edges

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

## Communities (219 total, 57 thin omitted)

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
Nodes (54): flushChamber(), isFlapEmpty(), aiConfidence, aiDetected, annotatedImageDataUrl, auth, autoStartFlow(), cameraCountdown (+46 more)

### Community 4 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (39): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+31 more)

### Community 7 - "User"
Cohesion: 0.05
Nodes (33): RewardRedemption, User, AdminRedemptionsListTest, AuthControllerLocaleTest, ExampleTest, MachineOrderTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest (+25 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (3): AdminController, AdminTableExport, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.07
Nodes (8): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardController"
Cohesion: 0.12
Nodes (3): RewardController, CashRedeemSettingsService, self

### Community 11 - "GpioDriver"
Cohesion: 0.13
Nodes (11): GpioDriver, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown)., One relay on for `seconds`; all others off first (interlock)., Run the tilt back until the limit switch is pressed, at most TILT_HOME_TIMEOUT. (+3 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.08
Nodes (26): activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported, canvasRef, elsewhere (+18 more)

### Community 13 - "FormalReportService"
Cohesion: 0.07
Nodes (15): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, LastLoginTest, UserDisplayNumberTest, FormalReportServiceTest, Illuminate\Testing\TestResponse, PhpOffice\PhpSpreadsheet\Cell\Coordinate (+7 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.11
Nodes (22): auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp(), loading (+14 more)

### Community 17 - "AuthController"
Cohesion: 0.13
Nodes (6): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (35): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+27 more)

### Community 22 - "DetectionLog"
Cohesion: 0.07
Nodes (10): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, PlatformTick, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest (+2 more)

### Community 23 - "CarbonService"
Cohesion: 0.12
Nodes (7): PublicStatsController, CarbonService, ActiveSessionTest, CarbonSourcesTest, CarbonServiceTest, ExampleTest, PHPUnit\Framework\TestCase

### Community 24 - "vitest"
Cohesion: 0.16
Nodes (9): flattenKeys(), __dirname, leaves(), messages, platformMessages, COMPACTOR_KEYS, __dirname, KEYS (+1 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.08
Nodes (19): ActivityView(), clock(), filters, hasLoadError, loading, materialList(), MATERIALS, openSessionCode (+11 more)

### Community 27 - "Illuminate\Http\JsonResponse"
Cohesion: 0.11
Nodes (4): MachineController, TransactionController, UserController, Illuminate\Http\JsonResponse

### Community 28 - "router/index.js"
Cohesion: 0.06
Nodes (35): AdminView(), DemoKioskSessionView(), GoodbyeView(), KioskLandingView(), NotFoundView(), PrivacyView(), RegisterView(), routes (+27 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "LandingView.vue"
Cohesion: 0.10
Nodes (18): LandingView(), acceptedChips, barLabel, intlLocale, itemsText(), { locale, t }, machineTypes, MONTH_MATERIALS (+10 more)

### Community 31 - "showToast"
Cohesion: 0.10
Nodes (23): kioskLink(), buildRewardUpdatePayload(), addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize(), copyKioskLink(), deleteMachine() (+15 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.11
Nodes (21): shouldAutoReturn(), startCountdown(), SessionSummaryView(), isDemoKioskPath(), kioskBase(), pathOf(), transactionPoints(), confirmEndSession() (+13 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.17
Nodes (15): AI_CONFIDENCE_MIN, BIN_LETTERS, cap(), confidenceLevel(), isKnown(), itemLabel(), percentOf(), RESULT_CODES (+7 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "Carbon\Carbon"
Cohesion: 0.16
Nodes (5): DemoDataGenerator, Carbon, Carbon, PlatformSimulation, Carbon\Carbon

### Community 36 - "api.js"
Cohesion: 0.15
Nodes (16): demoMachineGuard(), GoogleCallbackView(), api, registerClearAuth(), storedKioskToken, useActiveSessionStore, useAuthStore, isDemoMachine() (+8 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.13
Nodes (23): KioskQrView(), base, clearIntervals(), currentToken, elsewhereMsg, expiresInSec, generateQr(), handleExpiry() (+15 more)

### Community 40 - "vue"
Cohesion: 0.18
Nodes (14): IDLE_COUNTDOWN_MS, IDLE_MS, mount(), useIdleTimer(), SESSION_ENDED_MS, useKioskSessionGuard(), onEnded(), mount() (+6 more)

### Community 41 - "GpioDriverTest"
Cohesion: 0.14
Nodes (7): duty(), GpioDriverTest, run(), PWM duty of a servo pulse of `ms` milliseconds at 50 Hz., Switch releases when the tilt drives out; presses again shortly after the…, [(on_time, off_time), ...] of a relay pin., skipIf

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (39): DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence(), aiConfidence (+31 more)

### Community 43 - "ActiveSessionSheet.vue"
Cohesion: 0.06
Nodes (32): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+24 more)

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

### Community 48 - "PointsHistory"
Cohesion: 0.05
Nodes (15): PlatformController, CompactorBatch, ComposterDeposit, Location, PointsHistory, UserImpactService, PlatformPublicTest, PlatformTickTest (+7 more)

### Community 49 - "Closure"
Cohesion: 0.10
Nodes (14): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, EnsurePlatformMode, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader (+6 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "useIdleLogout.js"
Cohesion: 0.20
Nodes (16): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+8 more)

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
Cohesion: 0.27
Nodes (11): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 92 - "Machine"
Cohesion: 0.15
Nodes (5): chamber_capacity(), Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default.

### Community 93 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 94 - "Transaction"
Cohesion: 0.09
Nodes (6): AdminLog, MaintenanceRecord, Transaction, DemoDataGeneratorTest, Carbon, TransactionCompleteAtomicityTest

### Community 95 - "machineAlerts.test.js"
Cohesion: 0.27
Nodes (9): buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS, now, machineAlerts (+1 more)

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

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

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (19): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), fmt() (+11 more)

### Community 107 - "LanguageSwitch.vue"
Cohesion: 0.20
Nodes (11): act(), button, choose(), { locale, languages, currentLanguage, setLanguage }, onKey, onPointer(), open, root (+3 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.21
Nodes (3): PlaceholderDriver, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.12
Nodes (25): depositItem(), HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), ACCEPTED_2BIN, AUTO_END_SECONDS, isMachineFault(), limitEndsSession() (+17 more)

### Community 110 - "activityTimeline.test.js"
Cohesion: 0.16
Nodes (14): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, RECYCLING_KINDS, SIMULATED_SOURCES, history (+6 more)

### Community 115 - "QrSession"
Cohesion: 0.29
Nodes (3): QrController, QrSession, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 116 - "Illuminate\Support\Str"
Cohesion: 0.19
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, static

### Community 117 - "AiService"
Cohesion: 0.08
Nodes (4): AiService, GuestCarbonTest, HardwareCompactorProxyTest, IndonesianLocaleTest

### Community 118 - "RvmMachine"
Cohesion: 0.09
Nodes (7): RvmMachine, PlatformTelemetry, ExpireStaleSessionsTest, MachineDeleteGuardTest, MachineTypeIsolationTest, PublicStatsTest, PlatformTelemetryTest

### Community 119 - "api.php"
Cohesion: 0.15
Nodes (6): RouteServiceProvider, PlatformFlagTest, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route

### Community 120 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 121 - "AdminController.php"
Cohesion: 0.07
Nodes (13): PlatformCreditService, PlatformSettingsService, RewardConfigService, PlatformCreditTest, SecurityLogTest, Illuminate\Filesystem\Filesystem, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Http (+5 more)

### Community 122 - "UserAvatar.vue"
Cohesion: 0.18
Nodes (13): iconSize, initial, picked, props, style, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR (+5 more)

### Community 125 - "VoucherModal.vue"
Cohesion: 0.25
Nodes (8): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t }

### Community 126 - "intl"
Cohesion: 0.13
Nodes (18): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditLabel(), auditMessage() (+10 more)

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 128 - "MonitorPanel.vue"
Cohesion: 0.15
Nodes (10): fields, fields, detail, detailError, listError, machines, props, selected (+2 more)

### Community 129 - "Controller"
Cohesion: 0.19
Nodes (6): CarbonSourcesController, Controller, SessionController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

### Community 130 - "dataScope.test.js"
Cohesion: 0.17
Nodes (9): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), DAYS, weekdayLabel(), fetchChartData() (+1 more)

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "DashboardView.vue"
Cohesion: 0.10
Nodes (16): DashboardView(), demoDataLabel, googleLoginEnabled, platformMode, auth, AVAILABILITY_LABEL, binTypes, loadingMachines (+8 more)

### Community 134 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 135 - "SessionDetailModal.vue"
Cohesion: 0.14
Nodes (10): closeBtn, emit, items, loadError, loading, props, summary, { t, te } (+2 more)

### Community 136 - "intlLocale"
Cohesion: 0.13
Nodes (18): chart, colors, error, format(), kpis, options, overview, props (+10 more)

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

### Community 144 - "awardPoints"
Cohesion: 0.23
Nodes (11): isNetworkError(), newIdempotencyKey(), retryOnce(), saveUserItem(), awardPoints(), claimSwitchDecision(), continueSwitch, openMaterialSwitch() (+3 more)

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 151 - "brandLocales.test.js"
Cohesion: 0.19
Nodes (5): adminUi, SOURCES, used, LANGS, content

### Community 153 - "_UsbCameraWrapper"
Cohesion: 0.23
Nodes (5): Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, RuntimeError

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 159 - "@phosphor-icons/vue"
Cohesion: 0.46
Nodes (5): activeKey, route, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

### Community 163 - "qrToken.js"
Cohesion: 0.43
Nodes (6): afterQrExpiry(), extractQrToken(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, handleScan(), onQrDetected()

### Community 164 - "useLanguage.js"
Cohesion: 0.30
Nodes (9): applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, isLanguage(), LANGUAGES, resolveStoredLanguage() (+1 more)

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 173 - "LocationCard.vue"
Cohesion: 0.29
Nodes (7): icons, { t }, isRvmMachine(), pillTone(), statusPill(), tones, isRvmCard()

### Community 175 - "RecyclingSession"
Cohesion: 0.09
Nodes (6): RecyclingSession, AdminExportExcelTest, DetectionLogTest, PointsHistoryLocaleTest, SessionSummaryReceiptTest, WeighAwardsConfiguredPointsTest

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 186 - "rvm.js"
Cohesion: 0.62
Nodes (4): getHardwareState(), readKioskState(), readStoredProfile(), useRvmStore

### Community 187 - "downloadExcel"
Cohesion: 0.52
Nodes (5): cleanParams(), downloadExcel(), excelFilename(), exportDetectionLogsExcel(), exportTable()

### Community 188 - "HardwareError"
Cohesion: 0.40
Nodes (4): HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, A move failed (e.g. the tilt never reached its limit switch). All relays are…, GpioDriver on gpiozero's mock pins (no Raspberry Pi needed). Skipped where…

### Community 189 - "machineCodes.js"
Cohesion: 0.50
Nodes (3): plannedCodes(), SUFFIX, plannedMachineCodes

### Community 190 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 195 - "IdleOverlay.vue"
Cohesion: 0.50
Nodes (3): progress, props, seconds

### Community 201 - "VoucherView.vue"
Cohesion: 0.15
Nodes (9): VoucherView(), auth, error, loading, markError, marking, route, { t } (+1 more)

### Community 202 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 205 - "rewardCategory.test.js"
Cohesion: 0.53
Nodes (3): KNOWN, rewardCategoryKey(), rewardCategoryLabel()

### Community 206 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 207 - "platformNavGroups"
Cohesion: 0.50
Nodes (3): platformNavGroups(), t(), groupedNav

### Community 219 - "vue-i18n"
Cohesion: 0.07
Nodes (21): activity, error, page, pending, props, { t }, error, saved (+13 more)

### Community 224 - "main.js"
Cohesion: 0.20
Nodes (8): mounted(), prefersReducedMotion(), app, i18n, pinia, startLanguage, router, hasMachineKey

## Knowledge Gaps
- **746 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+741 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1178 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **57 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Controller`, `Illuminate\Http\Request`, `RewardItem`, `RewardController`, `FormalReportService`, `AdminPlatformTest`, `AdminEmailFormalReportTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `Illuminate\Http\JsonResponse`, `AdminPlatformSettingsTest`, `AdminTableExportTest`, `AdminCashRedeemSettingsTest`, `Carbon\Carbon`, `AdminRewardConfigTest`, `RecyclingSession`, `PointsHistory`, `AdminAuditLogTest`, `DemoSessionMachineTest`, `AdminCachingTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `AdminCarbonStatsTest`, `AdminKioskMaintenanceTest`, `Transaction`, `AdminUpdateUserTest`, `EnforceIdleTimeoutTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `Illuminate\Support\Str`, `AiService`, `RvmMachine`, `AdminController.php`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `User`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Builder`, `FormalReportService`, `AdminPlatformTest`, `AdminEmailFormalReportTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `Illuminate\Http\JsonResponse`, `AdminTableExportTest`, `Carbon\Carbon`, `RecyclingSession`, `PointsHistory`, `Closure`, `DemoSessionMachineTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `AdminCarbonStatsTest`, `AdminKioskMaintenanceTest`, `Transaction`, `EnforceIdleTimeoutTest`, `DemoFlagTest`, `MachineHeartbeatTest`, `QrSession`, `AdminController.php`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `TestCase` connect `User` to `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AdminEmailFormalReportTest`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `AdminPlatformSettingsTest`, `AdminTableExportTest`, `AdminCashRedeemSettingsTest`, `AdminRewardConfigTest`, `GoogleRedirectFrontendOriginTest`, `RecyclingSession`, `PointsHistory`, `AdminAuditLogTest`, `DemoSessionMachineTest`, `AdminCachingTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `Test Application Bootstrap`, `AdminCarbonStatsTest`, `AdminKioskMaintenanceTest`, `Transaction`, `AdminUpdateUserTest`, `EnforceIdleTimeoutTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `AiService`, `RvmMachine`, `api.php`, `AdminController.php`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _746 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.010049751243781095 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._