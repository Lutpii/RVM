# Graph Report - RVM  (2026-11-16)

## Corpus Check
- 442 files · ~286,586 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3210 nodes · 7172 edges · 216 communities (116 shown, 55 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 131 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f5f6dd85`
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
- RewardRedemption
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
- PlatformSimulation
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- api.js
- PlatformTelemetry
- DemoKioskSessionView.vue
- ActiveSessionSheet.vue
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- CompactorBatch
- Closure
- Console Kernel Scheduling
- AdminCachingTest
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
- tapGesture.test.js
- Transaction
- machineAlerts.test.js
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- vue
- AdminCompactorSettingsTest
- LastLoginTest.php
- MaintenanceHistoryModal.vue
- LanguageSwitch.vue
- PlaceholderDriver
- compactorFlow.js
- activityTimeline.test.js
- MachineHeartbeatTest
- AccountSuspensionTest
- CashRedeemTest
- RecordingDriver
- Controller
- Illuminate\Support\Str
- AiService
- RvmMachine
- UserController.php
- detectionOptimistic.test.js
- PlatformCreditService
- UserAvatar.vue
- AdminTransactionFiltersTest
- AccountSummaryTest
- VoucherModal.vue
- intl
- test_yolo.py
- MonitorPanel.vue
- autoStartFlow
- dataScope.test.js
- adminTabState.js
- DashboardView.vue
- normalize_material
- RewardConfigService
- PlatformOverview.vue
- PlatformAdminController
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
- ExpireStaleSessionsTest.php
- normalizeMachine
- AdminPlatformSettingsTest
- startCameraMode
- heartbeat.js
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- User.php
- useLanguage.js
- AdminRewardConfigTest
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- GoogleRedirectFrontendOriginTest
- LocationCard.vue
- RecyclingSession
- QrSession
- AdminAuditLogTest
- countMachinesInScope
- MaintenanceRecordsTest
- OneMachineRuleTest
- depositAndWait
- .makeFixture
- startLimitEnd
- Location
- scripts
- index.php
- 2026_11_15_000001_add_display_no_to_users_table.php
- intlLocale
- ExampleTest
- PrivacyView.vue
- voucher.js
- AdminCarbonStatsTest
- PlatformCreditTest
- rewardCategory.test.js
- FrontEnd/vite.config.js
- platformNavGroups
- WeighAwardsConfiguredPointsTest
- kioskLink
- localeChecks.test.js
- setRewardImageFile
- cancelEditPassword
- num

## God Nodes (most connected - your core abstractions)
1. `User` - 227 edges
2. `RvmMachine` - 200 edges
3. `TestCase` - 155 edges
4. `RecyclingSession` - 112 edges
5. `Transaction` - 84 edges
6. `AdminController` - 70 edges
7. `vitest` - 68 edges
8. `RewardItem` - 59 edges
9. `PointsHistory` - 54 edges
10. `vue` - 52 edges

## Surprising Connections (you probably didn't know these)
- `MachineTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_hardware.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `RecordingDriver`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/tests/test_hardware.py
- `useRvmStore` --indirect_call--> `endSession()`  [INFERRED]
  FrontEnd/src/store/rvm.js → FrontEnd/src/components/ActiveSessionSheet.vue
- `shownValue()` --calls--> `intlLocale()`  [EXTRACTED]
  FrontEnd/src/components/admin/platform/MonitorPanel.vue → FrontEnd/src/composables/useLanguage.js

## Import Cycles
- None detected.

## Communities (216 total, 55 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (177): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+169 more)

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
Nodes (40): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+32 more)

### Community 7 - "User"
Cohesion: 0.06
Nodes (21): User, AdminRedemptionsListTest, AuthControllerLocaleTest, IndonesianLocaleTest, MachineOrderTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest (+13 more)

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
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.07
Nodes (32): ScanView(), extractQrToken(), scanOutcome(), activeMachines, activeStore, auth, cameraActive, cameraError (+24 more)

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
Cohesion: 0.12
Nodes (6): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule

### Community 20 - "fetchTabData"
Cohesion: 0.05
Nodes (43): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+35 more)

### Community 22 - "DetectionLog"
Cohesion: 0.10
Nodes (5): OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest

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
Cohesion: 0.14
Nodes (4): MachineController, TransactionController, Illuminate\Http\JsonResponse, self

### Community 28 - "router/index.js"
Cohesion: 0.05
Nodes (43): activeKey, route, machines, { t }, AdminView(), GoodbyeView(), GoogleCallbackView(), NotFoundView() (+35 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "LandingView.vue"
Cohesion: 0.07
Nodes (23): error, loading, sources, { t }, LandingView(), formatSourceLine(), acceptedChips, barLabel (+15 more)

### Community 31 - "showToast"
Cohesion: 0.10
Nodes (26): cleanParams(), downloadExcel(), excelFilename(), buildRewardUpdatePayload(), addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize() (+18 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.08
Nodes (30): shouldAutoReturn(), startCountdown(), KioskLandingView(), SessionSummaryView(), isDemoKioskPath(), kioskBase(), pathOf(), transactionPoints() (+22 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.17
Nodes (15): AI_CONFIDENCE_MIN, BIN_LETTERS, cap(), confidenceLevel(), isKnown(), itemLabel(), percentOf(), RESULT_CODES (+7 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "Carbon\Carbon"
Cohesion: 0.22
Nodes (4): DemoDataGenerator, Carbon, Carbon\Carbon, Carbon\CarbonInterface

### Community 36 - "PlatformSimulation"
Cohesion: 0.15
Nodes (7): DemoData, ExpireStaleSessions, MachineKey, PlatformTick, Carbon, PlatformSimulation, Illuminate\Console\Command

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.12
Nodes (26): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, base, clearIntervals(), currentToken, elsewhereMsg (+18 more)

### Community 40 - "api.js"
Cohesion: 0.07
Nodes (28): activity, error, page, pending, props, { t }, error, saved (+20 more)

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (40): DemoKioskSessionView(), DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence() (+32 more)

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
Cohesion: 0.09
Nodes (27): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+19 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "CompactorBatch"
Cohesion: 0.10
Nodes (6): CompactorBatch, ComposterDeposit, UserImpactService, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 49 - "Closure"
Cohesion: 0.09
Nodes (14): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, EnsurePlatformMode, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader (+6 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "auth.js"
Cohesion: 0.14
Nodes (22): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+14 more)

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
Cohesion: 0.24
Nodes (10): bounds, changePage(), changePerPage(), emit, pageItems, props, { t, locale }, paginationBounds() (+2 more)

### Community 61 - "ImageCropper.vue"
Cohesion: 0.11
Nodes (35): baseName(), exportFile(), frame, frameEl, framePoint(), imageStyle, imageUrl, load() (+27 more)

### Community 72 - "isReviewable"
Cohesion: 0.24
Nodes (12): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+4 more)

### Community 92 - "Machine"
Cohesion: 0.15
Nodes (5): chamber_capacity(), Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default.

### Community 93 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 94 - "Transaction"
Cohesion: 0.07
Nodes (8): AdminLog, MaintenanceRecord, PointsHistory, Transaction, AdminUpdateUserTest, DemoDataGeneratorTest, Carbon, TransactionCompleteAtomicityTest

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
Cohesion: 0.13
Nodes (4): create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "vue"
Cohesion: 0.14
Nodes (17): progress, props, seconds, IDLE_COUNTDOWN_MS, IDLE_MS, mount(), useIdleTimer(), SESSION_ENDED_MS (+9 more)

### Community 105 - "LastLoginTest.php"
Cohesion: 0.35
Nodes (4): GoogleCallbackAccountLinkTest, Laravel\Socialite\Facades\Socialite, Laravel\Socialite\Two\User, Mockery

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (18): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+10 more)

### Community 107 - "LanguageSwitch.vue"
Cohesion: 0.20
Nodes (11): act(), button, choose(), { locale, languages, currentLanguage, setLanguage }, onKey, onPointer(), open, root (+3 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.18
Nodes (4): PlaceholderDriver, Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.24
Nodes (13): ACCEPTED_2BIN, AUTO_END_SECONDS, limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL, sessionLimitReached() (+5 more)

### Community 110 - "activityTimeline.test.js"
Cohesion: 0.16
Nodes (14): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, RECYCLING_KINDS, SIMULATED_SOURCES, history (+6 more)

### Community 115 - "Controller"
Cohesion: 0.13
Nodes (8): CarbonSourcesController, Controller, QrController, SessionController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 116 - "Illuminate\Support\Str"
Cohesion: 0.16
Nodes (5): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, static

### Community 117 - "AiService"
Cohesion: 0.09
Nodes (3): AiService, GuestCarbonTest, HardwareCompactorProxyTest

### Community 118 - "RvmMachine"
Cohesion: 0.06
Nodes (12): RvmMachine, MachineDeleteGuardTest, MachineTypeIsolationTest, PlatformTickTest, PublicStatsTest, SecurityLogTest, PlatformTelemetryTest, Illuminate\Support\Facades\Cache (+4 more)

### Community 119 - "UserController.php"
Cohesion: 0.08
Nodes (8): UserController, RouteServiceProvider, AvatarIcon, PlatformFlagTest, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route

### Community 120 - "detectionOptimistic.test.js"
Cohesion: 0.33
Nodes (7): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c

### Community 121 - "PlatformCreditService"
Cohesion: 0.24
Nodes (4): PlatformCreditService, PlatformSettingsService, Illuminate\Filesystem\Filesystem, InvalidArgumentException

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

### Community 129 - "autoStartFlow"
Cohesion: 0.38
Nodes (7): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), machineLocked(), recycleAgain(), refreshChamber()

### Community 130 - "dataScope.test.js"
Cohesion: 0.17
Nodes (9): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), DAYS, weekdayLabel(), fetchChartData() (+1 more)

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "DashboardView.vue"
Cohesion: 0.06
Nodes (26): closeBtn, emit, items, loadError, loading, props, summary, { t, te } (+18 more)

### Community 134 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 136 - "PlatformOverview.vue"
Cohesion: 0.20
Nodes (11): chart, colors, error, format(), kpis, options, overview, props (+3 more)

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
Cohesion: 0.43
Nodes (6): isNetworkError(), newIdempotencyKey(), retryOnce(), saveUserItem(), awardPoints(), randomPointsFallback()

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 151 - "brandLocales.test.js"
Cohesion: 0.24
Nodes (4): adminUi, SOURCES, used, LANGS

### Community 153 - "ExpireStaleSessionsTest.php"
Cohesion: 0.31
Nodes (3): ExpireStaleSessionsTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 156 - "startCameraMode"
Cohesion: 0.40
Nodes (5): captureFromCamera(), captureFromHardware(), openCamera(), startCameraMode(), stopCamera()

### Community 159 - "heartbeat.js"
Cohesion: 0.38
Nodes (4): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault()

### Community 163 - "User.php"
Cohesion: 0.40
Nodes (4): Illuminate\Database\UniqueConstraintViolationException, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 164 - "useLanguage.js"
Cohesion: 0.30
Nodes (9): applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, isLanguage(), LANGUAGES, resolveStoredLanguage() (+1 more)

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 173 - "LocationCard.vue"
Cohesion: 0.33
Nodes (5): icons, { t }, pillTone(), statusPill(), tones

### Community 175 - "RecyclingSession"
Cohesion: 0.09
Nodes (5): RecyclingSession, AdminExportExcelTest, DetectionLogTest, SessionSummaryReceiptTest, UserPlatformImpactTest

### Community 176 - "QrSession"
Cohesion: 0.14
Nodes (5): QrSession, DemoSessionMachineTest, EnforceIdleTimeoutTest, QrLifetimeTest, Illuminate\Support\Carbon

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 186 - "depositAndWait"
Cohesion: 0.31
Nodes (9): claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), recordInvalidItem(), simulateInsert(), stopSwitchTimer() (+1 more)

### Community 188 - "startLimitEnd"
Cohesion: 0.50
Nodes (5): flushChamber(), confirmEndSession(), materialLimit(), { showOverlay: showIdleOverlay, msLeft: idleMsLeft, poke: pokeIdle, sessionEnded, endingSession }, startLimitEnd()

### Community 189 - "Location"
Cohesion: 0.32
Nodes (3): Location, PlatformPublicTest, Illuminate\Database\Eloquent\Relations\HasMany

### Community 190 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 195 - "intlLocale"
Cohesion: 0.18
Nodes (11): numberFormatter, fmt(), date(), number(), { t, locale }, number(), { t, locale }, intlLocale() (+3 more)

### Community 201 - "PrivacyView.vue"
Cohesion: 0.32
Nodes (4): PrivacyView(), content, doc, { locale }

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

### Community 213 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

## Knowledge Gaps
- **740 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+735 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1167 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **55 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Http\Request`, `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AdminEmailFormalReportTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `ExpireStaleSessionsTest.php`, `AdminPlatformSettingsTest`, `AdminCashRedeemSettingsTest`, `User.php`, `Carbon\Carbon`, `PlatformSimulation`, `AdminRewardConfigTest`, `RecyclingSession`, `CompactorBatch`, `Closure`, `AdminAuditLogTest`, `AdminCachingTest`, `QrSession`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `.makeFixture`, `Location`, `AdminCarbonStatsTest`, `PlatformCreditTest`, `WeighAwardsConfiguredPointsTest`, `Transaction`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `LastLoginTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `Controller`, `Illuminate\Support\Str`, `RvmMachine`, `PlatformCreditService`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `User`, `Illuminate\Http\Request`, `PlatformAdminController`, `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AdminEmailFormalReportTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `CarbonService`, `ExpireStaleSessionsTest.php`, `Illuminate\Http\JsonResponse`, `Carbon\Carbon`, `PlatformSimulation`, `PlatformTelemetry`, `RecyclingSession`, `CompactorBatch`, `Closure`, `QrSession`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `.makeFixture`, `Location`, `AdminCarbonStatsTest`, `PlatformCreditTest`, `WeighAwardsConfiguredPointsTest`, `Transaction`, `DemoFlagTest`, `MachineHeartbeatTest`, `Controller`, `PlatformCreditService`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `TestCase` connect `User` to `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AdminEmailFormalReportTest`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `ExpireStaleSessionsTest.php`, `AdminPlatformSettingsTest`, `AdminCashRedeemSettingsTest`, `AdminRewardConfigTest`, `GoogleRedirectFrontendOriginTest`, `RecyclingSession`, `QrSession`, `AdminAuditLogTest`, `AdminCachingTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `Test Application Bootstrap`, `.makeFixture`, `Location`, `ExampleTest`, `AdminCarbonStatsTest`, `PlatformCreditTest`, `WeighAwardsConfiguredPointsTest`, `Transaction`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `LastLoginTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `AiService`, `RvmMachine`, `UserController.php`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _740 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.010100502512562814 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._