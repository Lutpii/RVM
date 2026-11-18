# Graph Report - RVM  (2026-11-18)

## Corpus Check
- 456 files · ~299,278 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3303 nodes · 7342 edges · 223 communities (114 shown, 64 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 133 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `79a9420d`
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
- AdminCachingTest
- AuthController
- AdminBinCollectionEmailTest
- AdminPaginationTest
- fetchTabData
- SessionStatusAndGraceTest
- DetectionLog
- GoogleMapsLinkService
- vitest
- LoginView.vue
- ActivityView.vue
- Illuminate\Http\JsonResponse
- router/index.js
- RewardsView.vue
- vue-i18n
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
- CarbonService
- DemoKioskSessionView.vue
- machineAlerts.test.js
- Location
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- api.php
- Closure
- Console Kernel Scheduling
- CompactorBatch
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
- AdminLog
- tapGesture.test.js
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- AppSelect.vue
- AdminCompactorSettingsTest
- LastLoginTest.php
- MaintenanceHistoryModal.vue
- LanguageSwitch.vue
- PlaceholderDriver
- brandLocales.test.js
- activityTimeline.test.js
- MachineHeartbeatTest
- MaintenanceRecordsTest
- CashRedeemTest
- RecordingDriver
- Carbon\Carbon
- intlLocale
- AiService
- RvmMachine
- AccountSuspensionTest
- saveDetectionReview
- PointsHistory
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
- ActiveSessionSheet.vue
- PlatformOverview.vue
- PlatformAdminController
- Handler
- rvm-kiosk-launch.sh
- scanMaintFrame
- redeemNow
- AdminPlatformTest
- ActiveSessionTest
- devDependencies
- vercel.json
- CompactorSettingsService
- Illuminate\Support\Facades\Schema
- PlatformTelemetryTest
- MapsLinkField.vue
- Illuminate\Database\Schema\Blueprint
- CountUp.vue
- normalizeMachine
- AdminPlatformSettingsTest
- AdminKioskMaintenanceTest
- PrivacyView.vue
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- UserPlatformImpactTest
- useLanguage.js
- RewardConfigService
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- qrToken.js
- AdminAuditLogTest
- RecyclingSession
- QrSession
- HardwareCompactorProxyTest
- countMachinesInScope
- carbonSources.test.js
- OneMachineRuleTest
- PlatformSimulation
- DemoSessionMachineTest
- AdminEmailFormalReportTest
- machineCodes.js
- scripts
- index.php
- 2026_11_15_000001_add_display_no_to_users_table.php
- AdminMapsLinkTest
- GoogleRedirectFrontendOriginTest
- featureFlags.js
- voucher.js
- rewardCategory.test.js
- ExpireStaleSessionsTest
- User.php
- FrontEnd/vite.config.js
- platformNavGroups
- AdminRewardConfigTest
- IndonesianLocaleTest
- WeighAwardsConfiguredPointsTest
- buildRewardUpdatePayload
- cancelEditPassword
- num
- scanOutcome
- Illuminate\Support\Facades\Artisan
- ExampleTest
- LandingView.vue
- localeChecks.test.js
- transactionPoints.js
- scrollReveal.js

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

## Communities (223 total, 64 thin omitted)

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
Cohesion: 0.04
Nodes (87): flushChamber(), isFlapEmpty(), HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), ACCEPTED_2BIN, AUTO_END_SECONDS, isMachineFault() (+79 more)

### Community 4 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (39): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+31 more)

### Community 7 - "User"
Cohesion: 0.06
Nodes (20): User, AdminRedemptionsListTest, AuthControllerLocaleTest, MachineOrderTest, PointsHistoryOrderTest, QrControllerLocaleTest, RewardHistoryTest, RewardRedemptionCashColumnsTest (+12 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (6): AdminController, Authenticate, AdminTableExport, PlatformRequest, Illuminate\Auth\Middleware\Authenticate, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.07
Nodes (8): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.15
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.08
Nodes (26): activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported, canvasRef, elsewhere (+18 more)

### Community 13 - "FormalReportService"
Cohesion: 0.05
Nodes (18): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, AdminTableExportTest, LastLoginTest, UserDisplayNumberTest, FormalReportServiceTest, Illuminate\Testing\TestResponse (+10 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (23): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp() (+15 more)

### Community 17 - "AuthController"
Cohesion: 0.13
Nodes (6): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (35): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+27 more)

### Community 22 - "DetectionLog"
Cohesion: 0.07
Nodes (10): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, PlatformTick, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest (+2 more)

### Community 23 - "GoogleMapsLinkService"
Cohesion: 0.10
Nodes (9): GoogleMapsLinkService, UserFactory, ExampleTest, GoogleMapsLinkParseTest, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, PHPUnit\Framework\TestCase, self (+1 more)

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
Nodes (34): AdminView(), DemoKioskSessionView(), demoMachineGuard(), GoodbyeView(), GoogleCallbackView(), NotFoundView(), routes, RvmSessionView() (+26 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "vue-i18n"
Cohesion: 0.07
Nodes (22): activeKey, route, error, loading, sources, { t }, machines, { t } (+14 more)

### Community 31 - "showToast"
Cohesion: 0.10
Nodes (26): cleanParams(), downloadExcel(), excelFilename(), kioskLink(), addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize() (+18 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.08
Nodes (28): shouldAutoReturn(), startCountdown(), KioskLandingView(), isDemoKioskPath(), kioskBase(), pathOf(), confirmEndSession(), { showOverlay: showIdleOverlay, msLeft: idleMsLeft, poke: pokeIdle, sessionEnded, endingSession } (+20 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.17
Nodes (15): AI_CONFIDENCE_MIN, BIN_LETTERS, cap(), confidenceLevel(), isKnown(), itemLabel(), percentOf(), RESULT_CODES (+7 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DemoDataGenerator"
Cohesion: 0.17
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
Cohesion: 0.13
Nodes (23): KioskQrView(), base, clearIntervals(), currentToken, elsewhereMsg, expiresInSec, generateQr(), handleExpiry() (+15 more)

### Community 40 - "vue"
Cohesion: 0.07
Nodes (38): activity, error, page, pending, props, { t }, error, saved (+30 more)

### Community 41 - "CarbonService"
Cohesion: 0.18
Nodes (4): PublicStatsController, CarbonService, CarbonSourcesTest, CarbonServiceTest

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.06
Nodes (42): progress, props, seconds, DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep() (+34 more)

### Community 43 - "machineAlerts.test.js"
Cohesion: 0.25
Nodes (10): buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS, now, machineAlerts (+2 more)

### Community 44 - "Location"
Cohesion: 0.32
Nodes (3): Location, PlatformPublicTest, Illuminate\Database\Eloquent\Relations\HasMany

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.09
Nodes (27): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+19 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "api.php"
Cohesion: 0.15
Nodes (6): RouteServiceProvider, PlatformFlagTest, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route

### Community 49 - "Closure"
Cohesion: 0.10
Nodes (14): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, EnsurePlatformMode, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader (+6 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 51 - "CompactorBatch"
Cohesion: 0.09
Nodes (7): CompactorBatch, ComposterDeposit, UserImpactService, AvatarIcon, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 52 - "auth.js"
Cohesion: 0.10
Nodes (33): auth, route, showAppNav, showKioskDemo, showToast(), theme, toastState, toggleTheme() (+25 more)

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
Cohesion: 0.27
Nodes (11): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+3 more)

### Community 92 - "Machine"
Cohesion: 0.15
Nodes (5): chamber_capacity(), Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default.

### Community 93 - "LocationCard.vue"
Cohesion: 0.29
Nodes (7): icons, { t }, isRvmMachine(), pillTone(), statusPill(), tones, isRvmCard()

### Community 94 - "AdminLog"
Cohesion: 0.11
Nodes (4): AdminLog, MaintenanceRecord, DemoDataGeneratorTest, Carbon

### Community 95 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

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

### Community 103 - "AppSelect.vue"
Cohesion: 0.16
Nodes (19): button, choose(), close(), emit, enabled, focusItem(), hasValue, label (+11 more)

### Community 105 - "LastLoginTest.php"
Cohesion: 0.35
Nodes (4): GoogleCallbackAccountLinkTest, Laravel\Socialite\Facades\Socialite, Laravel\Socialite\Two\User, Mockery

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (18): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+10 more)

### Community 107 - "LanguageSwitch.vue"
Cohesion: 0.26
Nodes (9): act(), button, choose(), { locale, languages, currentLanguage, setLanguage }, onKey, onPointer(), open, root (+1 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.18
Nodes (4): PlaceholderDriver, Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "brandLocales.test.js"
Cohesion: 0.24
Nodes (4): adminUi, SOURCES, used, LANGS

### Community 110 - "activityTimeline.test.js"
Cohesion: 0.16
Nodes (14): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, RECYCLING_KINDS, SIMULATED_SOURCES, history (+6 more)

### Community 115 - "Carbon\Carbon"
Cohesion: 0.24
Nodes (3): PlatformController, PlatformTelemetry, Carbon\Carbon

### Community 116 - "intlLocale"
Cohesion: 0.18
Nodes (11): numberFormatter, fmt(), date(), number(), { t, locale }, number(), { t, locale }, intlLocale() (+3 more)

### Community 118 - "RvmMachine"
Cohesion: 0.10
Nodes (6): RvmMachine, AdminCarbonStatsTest, DetectionLogTest, MachineDeleteGuardTest, MachineTypeIsolationTest, PlatformTickTest

### Community 120 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 121 - "PointsHistory"
Cohesion: 0.06
Nodes (11): PointsHistory, AdminUpdateUserTest, PlatformCreditTest, PointsHistoryLocaleTest, PointsHistoryRedeemedTypeTest, SecurityLogTest, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Http (+3 more)

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
Cohesion: 0.09
Nodes (16): DashboardView(), splitColumns(), auth, AVAILABILITY_LABEL, binTypes, loadingMachines, locationError, machines (+8 more)

### Community 134 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 135 - "ActiveSessionSheet.vue"
Cohesion: 0.06
Nodes (27): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+19 more)

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

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 151 - "MapsLinkField.vue"
Cohesion: 0.22
Nodes (10): busy, emit, error, found, onPaste(), resolve(), { t }, url (+2 more)

### Community 153 - "CountUp.vue"
Cohesion: 0.29
Nodes (7): el, props, reducedMotion(), run(), seen, shown, text

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 159 - "PrivacyView.vue"
Cohesion: 0.32
Nodes (4): PrivacyView(), content, doc, { locale }

### Community 164 - "useLanguage.js"
Cohesion: 0.30
Nodes (9): applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, isLanguage(), LANGUAGES, resolveStoredLanguage() (+1 more)

### Community 168 - "RewardConfigService"
Cohesion: 0.16
Nodes (3): PlatformSettingsService, RewardConfigService, Illuminate\Filesystem\Filesystem

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 170 - "qrToken.js"
Cohesion: 0.43
Nodes (6): afterQrExpiry(), extractQrToken(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, handleScan(), onQrDetected()

### Community 175 - "RecyclingSession"
Cohesion: 0.10
Nodes (7): RecyclingSession, Transaction, AdminExportExcelTest, PublicStatsTest, SessionSummaryReceiptTest, TransactionCompleteAtomicityTest, Illuminate\Support\Facades\Cache

### Community 176 - "QrSession"
Cohesion: 0.16
Nodes (5): QrController, QrSession, EnforceIdleTimeoutTest, QrLifetimeTest, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 186 - "PlatformSimulation"
Cohesion: 0.21
Nodes (4): PlatformCreditService, Carbon, PlatformSimulation, InvalidArgumentException

### Community 189 - "machineCodes.js"
Cohesion: 0.50
Nodes (3): plannedCodes(), SUFFIX, plannedMachineCodes

### Community 190 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 201 - "featureFlags.js"
Cohesion: 0.40
Nodes (3): demoDataLabel, googleLoginEnabled, platformMode

### Community 202 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 203 - "rewardCategory.test.js"
Cohesion: 0.53
Nodes (3): KNOWN, rewardCategoryKey(), rewardCategoryLabel()

### Community 205 - "User.php"
Cohesion: 0.40
Nodes (4): Illuminate\Database\UniqueConstraintViolationException, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 206 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 207 - "platformNavGroups"
Cohesion: 0.50
Nodes (3): platformNavGroups(), t(), groupedNav

### Community 219 - "LandingView.vue"
Cohesion: 0.09
Nodes (19): LandingView(), acceptedChips, barLabel, intlLocale, itemsText(), { locale, t }, machineTypes, MONTH_MATERIALS (+11 more)

## Knowledge Gaps
- **760 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+755 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1201 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **64 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Controller`, `Illuminate\Http\Request`, `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `ActiveSessionTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminCachingTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `GoogleMapsLinkService`, `Illuminate\Http\JsonResponse`, `AdminKioskMaintenanceTest`, `AdminPlatformSettingsTest`, `AdminCashRedeemSettingsTest`, `DemoDataGenerator`, `UserPlatformImpactTest`, `Location`, `AdminAuditLogTest`, `RecyclingSession`, `QrSession`, `CompactorBatch`, `OneMachineRuleTest`, `PlatformSimulation`, `DemoSessionMachineTest`, `AdminEmailFormalReportTest`, `AdminMapsLinkTest`, `ExpireStaleSessionsTest`, `User.php`, `AdminRewardConfigTest`, `IndonesianLocaleTest`, `WeighAwardsConfiguredPointsTest`, `AdminLog`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `LastLoginTest.php`, `MachineHeartbeatTest`, `MaintenanceRecordsTest`, `CashRedeemTest`, `RvmMachine`, `AccountSuspensionTest`, `PointsHistory`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `User`, `Illuminate\Http\Request`, `PlatformAdminController`, `FormalReportService`, `AdminPlatformTest`, `ActiveSessionTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `PlatformTelemetryTest`, `Illuminate\Http\JsonResponse`, `AdminKioskMaintenanceTest`, `DemoDataGenerator`, `UserPlatformImpactTest`, `CarbonService`, `Location`, `RecyclingSession`, `QrSession`, `Closure`, `CompactorBatch`, `OneMachineRuleTest`, `PlatformSimulation`, `DemoSessionMachineTest`, `AdminEmailFormalReportTest`, `ExpireStaleSessionsTest`, `WeighAwardsConfiguredPointsTest`, `AdminLog`, `DemoFlagTest`, `MachineHeartbeatTest`, `MaintenanceRecordsTest`, `Carbon\Carbon`, `PointsHistory`, `AdminTransactionFiltersTest`, `AccountSummaryTest`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `MonitorPanel.vue`, `AdminView.vue`, `RvmSessionView.vue`, `DashboardView.vue`, `UserSettingsView.vue`, `ActiveSessionSheet.vue`, `PlatformOverview.vue`, `ScanView.vue`, `RegisterView.vue`, `MapsLinkField.vue`, `CountUp.vue`, `ActivityView.vue`, `LoginView.vue`, `router/index.js`, `RewardsView.vue`, `vue-i18n`, `PrivacyView.vue`, `SessionSummaryView.vue`, `SessionFloat.vue`, `useLanguage.js`, `KioskQrView.vue`, `DemoKioskSessionView.vue`, `auth.js`, `AdminPagination.vue`, `ImageCropper.vue`, `LandingView.vue`, `FrontEnd/package.json`, `AppSelect.vue`, `MaintenanceHistoryModal.vue`, `LanguageSwitch.vue`, `UserAvatar.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _760 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.00999950741342791 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._