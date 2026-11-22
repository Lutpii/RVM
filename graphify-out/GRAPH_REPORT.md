# Graph Report - RVM  (2026-11-22)

## Corpus Check
- 459 files · ~302,515 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3336 nodes · 7512 edges · 215 communities (113 shown, 57 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 233 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7d83c523`
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
- CarbonService
- LocationCard.vue
- LoginView.vue
- ActivityView.vue
- Illuminate\Http\JsonResponse
- router/index.js
- RewardsView.vue
- App.vue
- t
- SessionSummaryView.vue
- transactionDisplay.js
- Backend Build Config
- Carbon\Carbon
- SessionFloat.vue
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- api.js
- GpioDriverTest
- DemoKioskSessionView.vue
- vue
- Location
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- ActiveSessionSheet.vue
- Closure
- Console Kernel Scheduling
- PlatformAdminController
- auth.js
- Exception Handler
- Logging Configuration
- Test Application Bootstrap
- BackEnd/README.md
- Trusted Hosts Middleware
- Auth Service Provider
- Database Seeder
- SessionDetailModal.vue
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
- MaintenanceRecordsTest
- VoucherView.vue
- tapGesture.test.js
- swipe.js
- LanguageSwitch.vue
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- AppSelect.vue
- AdminCompactorSettingsTest
- CarbonSourcesNote.vue
- MaintenanceHistoryModal.vue
- AdminPagination.vue
- PlaceholderDriver
- PlatformSettingsCard.vue
- _UsbCameraWrapper
- MachineHeartbeatTest
- Transaction
- CashRedeemTest
- RecordingDriver
- RvmMachine
- formatUserId
- AiService
- vitest
- AccountSuspensionTest
- saveDetectionReview
- AdminController.php
- UserAvatar.vue
- AdminTransactionFiltersTest
- RecyclingSession
- VoucherModal.vue
- intl
- test_yolo.py
- PlatformMachineDashboard.vue
- SessionController
- dataScope.test.js
- adminTabState.js
- DashboardView.vue
- normalize_material
- CountUp.vue
- vue-i18n
- AdminAuditLogTest
- Handler
- rvm-kiosk-launch.sh
- submitMaintenance
- redeemNow
- AdminPlatformTest
- AuthVerificationGateTest
- devDependencies
- vercel.json
- CompactorSettingsService
- Illuminate\Support\Facades\Schema
- Controller
- MapsLinkField.vue
- Illuminate\Database\Schema\Blueprint
- PlatformCreditTest
- normalizeMachine
- AdminPlatformSettingsTest
- AdminKioskMaintenanceTest
- locales/index.js
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- ExpireStaleSessionsTest
- UserController.php
- RewardConfigService
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- awardPoints
- LastLoginTest.php
- HardwareError
- QrSession
- @phosphor-icons/vue
- countMachinesInScope
- AdminRewardConfigTest
- OneMachineRuleTest
- GoogleRedirectFrontendOriginTest
- useIdleLogout
- AdminEmailFormalReportTest
- machineCodes.js
- MachineTypeIsolationTest
- IdleOverlay.vue
- 2026_11_15_000001_add_display_no_to_users_table.php
- PlatformActivityTable.vue
- AdminCarbonStatsTest
- useIdleTimer.js
- rewardCategory.test.js
- MachineDeleteGuardTest
- Authenticate.php
- scrollReveal.js
- privacyContent.js
- platformNavGroups
- PlatformRequest.php
- localeChecks.test.js
- idParity.test.js
- index.php
- LandingView.vue

## God Nodes (most connected - your core abstractions)
1. `User` - 233 edges
2. `RvmMachine` - 205 edges
3. `TestCase` - 157 edges
4. `RecyclingSession` - 112 edges
5. `t` - 101 edges
6. `Transaction` - 84 edges
7. `vitest` - 72 edges
8. `AdminController` - 71 edges
9. `RewardItem` - 59 edges
10. `vue` - 56 edges

## Surprising Connections (you probably didn't know these)
- `label()` --calls--> `t`  [INFERRED]
  FrontEnd/src/components/ActiveSessionSheet.vue → FrontEnd/src/views/MaintenanceView.vue
- `materialLabel()` --calls--> `t`  [INFERRED]
  FrontEnd/src/components/SessionDetailModal.vue → FrontEnd/src/views/MaintenanceView.vue
- `across` --calls--> `t`  [INFERRED]
  FrontEnd/src/components/admin/platform/PlatformMachineDashboard.vue → FrontEnd/src/views/MaintenanceView.vue
- `barOptions` --calls--> `t`  [INFERRED]
  FrontEnd/src/components/admin/platform/PlatformMachineDashboard.vue → FrontEnd/src/views/MaintenanceView.vue
- `sideTitle` --calls--> `t`  [INFERRED]
  FrontEnd/src/components/admin/platform/PlatformMachineDashboard.vue → FrontEnd/src/views/MaintenanceView.vue

## Import Cycles
- None detected.

## Communities (215 total, 57 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (171): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+163 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.04
Nodes (82): depositItem(), flushChamber(), isFlapEmpty(), HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), ACCEPTED_2BIN, AUTO_END_SECONDS (+74 more)

### Community 4 - "ai_service/app.py"
Cohesion: 0.22
Nodes (11): capture(), classify(), _generate_mjpeg(), health(), _PiCameraWrapper, route, Wraps Picamera2 so capture_array() always returns true RGB. Picamera2's…, Frees the USB webcam handle (no-op for the Pi's dedicated CSI camera) so a… (+3 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (44): auth, cancelEditPassword(), carbonText, changePassword(), confirmDelete, conversionRate, deleteConfirmText, deletingAccount (+36 more)

### Community 7 - "User"
Cohesion: 0.05
Nodes (31): User, AdminMapsLinkTest, AdminRedemptionsListTest, AuthControllerLocaleTest, ExampleTest, MachineOrderTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest (+23 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (3): AdminController, AdminTableExport, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.07
Nodes (8): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.14
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.13
Nodes (11): GpioDriver, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown)., One relay on for `seconds`; all others off first (interlock)., Run the tilt back until the limit switch is pressed, at most TILT_HOME_TIMEOUT. (+3 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.07
Nodes (31): extractQrToken(), scanOutcome(), activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported (+23 more)

### Community 13 - "FormalReportService"
Cohesion: 0.05
Nodes (18): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, AdminTableExportTest, LastLoginTest, UserDisplayNumberTest, FormalReportServiceTest, Illuminate\Testing\TestResponse (+10 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.11
Nodes (22): auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp(), loading (+14 more)

### Community 17 - "AuthController"
Cohesion: 0.12
Nodes (6): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (35): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+27 more)

### Community 22 - "DetectionLog"
Cohesion: 0.07
Nodes (10): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, PlatformTick, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest (+2 more)

### Community 23 - "CarbonService"
Cohesion: 0.05
Nodes (15): PublicStatsController, CarbonService, GoogleMapsLinkService, UserFactory, ActiveSessionTest, CarbonSourcesTest, CarbonServiceTest, ExampleTest (+7 more)

### Community 24 - "LocationCard.vue"
Cohesion: 0.15
Nodes (16): icons, { t }, buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS (+8 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (19): auth, error, form, handleEmailLogin(), handleResendLoginOtp(), handleSendOtp(), handleVerifyLoginOtp(), handleVerifyOtp() (+11 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.06
Nodes (35): ActivityView(), activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, RECYCLING_KINDS, SIMULATED_SOURCES (+27 more)

### Community 27 - "Illuminate\Http\JsonResponse"
Cohesion: 0.16
Nodes (3): MachineController, TransactionController, Illuminate\Http\JsonResponse

### Community 28 - "router/index.js"
Cohesion: 0.06
Nodes (37): AdminView(), DashboardView(), GoodbyeView(), GoogleCallbackView(), LoginView(), NotFoundView(), PrivacyView(), RegisterView() (+29 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "App.vue"
Cohesion: 0.15
Nodes (14): auth, route, showAppNav, showKioskDemo, showToast(), theme, toastState, toggleTheme() (+6 more)

### Community 31 - "t"
Cohesion: 0.08
Nodes (36): cleanParams(), downloadExcel(), excelFilename(), kioskLink(), buildRewardUpdatePayload(), addRewardItem(), askConfirm(), barChartOptions (+28 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.07
Nodes (32): shouldAutoReturn(), startCountdown(), onEnded(), KioskLandingView(), SessionSummaryView(), setKioskToken(), isDemoKioskPath(), kioskBase() (+24 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.17
Nodes (15): AI_CONFIDENCE_MIN, BIN_LETTERS, cap(), confidenceLevel(), isKnown(), itemLabel(), percentOf(), RESULT_CODES (+7 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "Carbon\Carbon"
Cohesion: 0.10
Nodes (9): MaintenanceRecord, DemoDataGenerator, Carbon, PlatformCreditService, Carbon, PlatformSimulation, Carbon\Carbon, Carbon\CarbonInterface (+1 more)

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
Cohesion: 0.12
Nodes (26): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, base, clearIntervals(), currentToken, elsewhereMsg (+18 more)

### Community 40 - "api.js"
Cohesion: 0.14
Nodes (16): SESSION_ENDED_MS, useKioskSessionGuard(), mount(), useSessionWatch(), demoMachineGuard(), api, hasMachineKey, storedKioskToken (+8 more)

### Community 41 - "GpioDriverTest"
Cohesion: 0.14
Nodes (7): duty(), GpioDriverTest, run(), PWM duty of a servo pulse of `ms` milliseconds at 50 Hz., Switch releases when the tilt drives out; presses again shortly after the…, [(on_time, off_time), ...] of a relay pin., skipIf

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (41): DemoKioskSessionView(), DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence() (+33 more)

### Community 43 - "vue"
Cohesion: 0.17
Nodes (17): applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, isLanguage(), LANGUAGES, resolveStoredLanguage() (+9 more)

### Community 44 - "Location"
Cohesion: 0.32
Nodes (3): Location, PlatformPublicTest, Illuminate\Database\Eloquent\Relations\HasMany

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
Nodes (16): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+8 more)

### Community 49 - "Closure"
Cohesion: 0.09
Nodes (14): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, EnsurePlatformMode, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader (+6 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "auth.js"
Cohesion: 0.26
Nodes (10): ACTIVITY_EVENTS, registerClearAuth(), useActiveSessionStore, useAuthStore, clearIdleActivity(), IDLE_ACTIVITY_STORAGE_KEY, readIdleActivity(), resetIdleActivity() (+2 more)

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

### Community 60 - "SessionDetailModal.vue"
Cohesion: 0.14
Nodes (11): closeBtn, emit, items, loadError, loading, materialLabel(), props, summary (+3 more)

### Community 61 - "ImageCropper.vue"
Cohesion: 0.11
Nodes (35): baseName(), exportFile(), frame, frameEl, framePoint(), imageStyle, imageUrl, load() (+27 more)

### Community 72 - "isReviewable"
Cohesion: 0.24
Nodes (12): closeDetectionViewer(), correctDisabledReason(), isReviewable(), isUnknownPrediction(), markCorrect(), normalizedPrediction(), openIncorrectReview(), reviewDisabledReason() (+4 more)

### Community 92 - "Machine"
Cohesion: 0.15
Nodes (5): chamber_capacity(), Machine, Ask the driver whether the machine is safe to run (flap closed, tilt at its…, Chamber material/count once the running and queued jobs finish, plus the…, A deposit's capacity: a whole number 1..MAX_CHAMBER_CAPACITY, else the default.

### Community 94 - "VoucherView.vue"
Cohesion: 0.15
Nodes (11): VoucherView(), auth, error, load(), loading, markError, marking, markUsed() (+3 more)

### Community 95 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 97 - "LanguageSwitch.vue"
Cohesion: 0.20
Nodes (11): act(), button, choose(), { locale, languages, currentLanguage, setLanguage }, onKey, onPointer(), open, root (+3 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.09
Nodes (20): axios, vite, name, private, scripts, build, dev, preview (+12 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.11
Nodes (5): Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "AppSelect.vue"
Cohesion: 0.16
Nodes (19): button, choose(), close(), emit, enabled, focusItem(), hasValue, label (+11 more)

### Community 105 - "CarbonSourcesNote.vue"
Cohesion: 0.22
Nodes (6): displayedSource(), error, loading, sources, { t }, formatSourceLine()

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (19): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), fmt() (+11 more)

### Community 107 - "AdminPagination.vue"
Cohesion: 0.22
Nodes (11): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+3 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.21
Nodes (3): PlaceholderDriver, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "PlatformSettingsCard.vue"
Cohesion: 0.29
Nodes (5): error, saved, saving, settings, { t }

### Community 110 - "_UsbCameraWrapper"
Cohesion: 0.23
Nodes (5): Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, Wraps an OpenCV VideoCapture (USB webcam) behind the same capture_array() ->…, _UsbCameraWrapper, RuntimeError

### Community 112 - "Transaction"
Cohesion: 0.08
Nodes (7): AdminLog, PointsHistory, Transaction, AdminUpdateUserTest, DemoDataGeneratorTest, Carbon, TransactionCompleteAtomicityTest

### Community 115 - "RvmMachine"
Cohesion: 0.06
Nodes (12): PlatformController, CompactorBatch, ComposterDeposit, RvmMachine, PlatformTelemetry, UserImpactService, PlatformTickTest, PublicStatsTest (+4 more)

### Community 117 - "AiService"
Cohesion: 0.08
Nodes (4): AiService, GuestCarbonTest, HardwareCompactorProxyTest, IndonesianLocaleTest

### Community 118 - "vitest"
Cohesion: 0.19
Nodes (7): flattenKeys(), __dirname, messages, COMPACTOR_KEYS, __dirname, KEYS, vitest

### Community 120 - "saveDetectionReview"
Cohesion: 0.16
Nodes (15): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+7 more)

### Community 121 - "AdminController.php"
Cohesion: 0.18
Nodes (4): SecurityLogTest, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Mail

### Community 122 - "UserAvatar.vue"
Cohesion: 0.18
Nodes (13): iconSize, initial, picked, props, style, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR (+5 more)

### Community 124 - "RecyclingSession"
Cohesion: 0.08
Nodes (7): RecyclingSession, AccountSummaryTest, AdminExportExcelTest, DetectionLogTest, PointsHistoryLocaleTest, SessionSummaryReceiptTest, WeighAwardsConfiguredPointsTest

### Community 125 - "VoucherModal.vue"
Cohesion: 0.18
Nodes (14): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t } (+6 more)

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
Nodes (7): SessionController, RouteServiceProvider, PlatformFlagTest, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route

### Community 130 - "dataScope.test.js"
Cohesion: 0.17
Nodes (9): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), DAYS, weekdayLabel(), fetchChartData() (+1 more)

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "DashboardView.vue"
Cohesion: 0.08
Nodes (20): { t }, demoDataLabel, googleLoginEnabled, platformMode, splitColumns(), auth, AVAILABILITY_LABEL, binTypes (+12 more)

### Community 134 - "normalize_material"
Cohesion: 0.31
Nodes (3): normalize_material(), Material slugs for the 2-bin DSME machine. Only tin (aluminum) and plastic have…, NormalizeMaterialTest

### Community 135 - "CountUp.vue"
Cohesion: 0.29
Nodes (7): el, props, reducedMotion(), run(), seen, shown, text

### Community 136 - "vue-i18n"
Cohesion: 0.12
Nodes (20): barData, chart, colors, error, format(), kpis, options, overview (+12 more)

### Community 138 - "Handler"
Cohesion: 0.33
Nodes (3): BaseHTTPRequestHandler, Handler, Local-only companion for the RVM kiosk's admin "Maintenance" action. Runs as…

### Community 139 - "rvm-kiosk-launch.sh"
Cohesion: 0.60
Nodes (5): new_bytes(), rvm-kiosk-launch.sh script, size_of(), start_ai(), uptime_s()

### Community 140 - "submitMaintenance"
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

### Community 150 - "Controller"
Cohesion: 0.19
Nodes (7): CarbonSourcesController, Controller, QrController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 151 - "MapsLinkField.vue"
Cohesion: 0.22
Nodes (10): busy, emit, error, found, onPaste(), resolve(), { t }, url (+2 more)

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 159 - "locales/index.js"
Cohesion: 0.20
Nodes (5): adminUi, SOURCES, used, LANGS, platformMessages

### Community 168 - "RewardConfigService"
Cohesion: 0.17
Nodes (3): PlatformSettingsService, RewardConfigService, Illuminate\Filesystem\Filesystem

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 170 - "awardPoints"
Cohesion: 0.43
Nodes (6): isNetworkError(), newIdempotencyKey(), retryOnce(), saveUserItem(), awardPoints(), randomPointsFallback()

### Community 173 - "LastLoginTest.php"
Cohesion: 0.35
Nodes (4): GoogleCallbackAccountLinkTest, Laravel\Socialite\Facades\Socialite, Laravel\Socialite\Two\User, Mockery

### Community 175 - "HardwareError"
Cohesion: 0.40
Nodes (4): HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, A move failed (e.g. the tilt never reached its limit switch). All relays are…, GpioDriver on gpiozero's mock pins (no Raspberry Pi needed). Skipped where…

### Community 176 - "QrSession"
Cohesion: 0.09
Nodes (5): QrSession, DemoSessionMachineTest, EnforceIdleTimeoutTest, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Model

### Community 177 - "@phosphor-icons/vue"
Cohesion: 0.29
Nodes (7): activeKey, route, machines, { t }, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 187 - "useIdleLogout"
Cohesion: 0.50
Nodes (9): useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire(), onActivity() (+1 more)

### Community 189 - "machineCodes.js"
Cohesion: 0.50
Nodes (3): plannedCodes(), SUFFIX, plannedMachineCodes

### Community 191 - "IdleOverlay.vue"
Cohesion: 0.50
Nodes (3): progress, props, seconds

### Community 195 - "PlatformActivityTable.vue"
Cohesion: 0.29
Nodes (6): activity, error, page, pending, props, { t }

### Community 201 - "useIdleTimer.js"
Cohesion: 0.60
Nodes (4): IDLE_COUNTDOWN_MS, IDLE_MS, mount(), useIdleTimer()

### Community 202 - "rewardCategory.test.js"
Cohesion: 0.53
Nodes (3): KNOWN, rewardCategoryKey(), rewardCategoryLabel()

### Community 219 - "LandingView.vue"
Cohesion: 0.08
Nodes (24): getLenis(), LandingView(), acceptedChips, barLabel, heroEl, intlLocale, itemsText(), { locale, t } (+16 more)

## Knowledge Gaps
- **753 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+748 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1183 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **57 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `SessionController`, `Illuminate\Http\Request`, `AdminAuditLogTest`, `RewardItem`, `FormalReportService`, `AdminPlatformTest`, `AdminCachingTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `AuthVerificationGateTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `PlatformCreditTest`, `AdminPlatformSettingsTest`, `AdminKioskMaintenanceTest`, `AdminCashRedeemSettingsTest`, `Carbon\Carbon`, `ExpireStaleSessionsTest`, `Location`, `LastLoginTest.php`, `QrSession`, `Closure`, `AdminRewardConfigTest`, `OneMachineRuleTest`, `AdminEmailFormalReportTest`, `MachineTypeIsolationTest`, `AdminCarbonStatsTest`, `MachineDeleteGuardTest`, `MaintenanceRecordsTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `MachineHeartbeatTest`, `Transaction`, `CashRedeemTest`, `RvmMachine`, `AiService`, `AccountSuspensionTest`, `AdminController.php`, `AdminTransactionFiltersTest`, `RecyclingSession`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `SessionController`, `BinCollectionRequested`, `User`, `Illuminate\Http\Request`, `FormalReportService`, `AdminPlatformTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `CarbonService`, `Controller`, `PlatformCreditTest`, `Illuminate\Http\JsonResponse`, `AdminKioskMaintenanceTest`, `Carbon\Carbon`, `ExpireStaleSessionsTest`, `Location`, `QrSession`, `Closure`, `PlatformAdminController`, `OneMachineRuleTest`, `AdminEmailFormalReportTest`, `MachineTypeIsolationTest`, `AdminCarbonStatsTest`, `MachineDeleteGuardTest`, `MaintenanceRecordsTest`, `DemoFlagTest`, `MachineHeartbeatTest`, `Transaction`, `AdminController.php`, `AdminTransactionFiltersTest`, `RecyclingSession`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `PlatformMachineDashboard.vue`, `AdminView.vue`, `RvmSessionView.vue`, `DashboardView.vue`, `UserSettingsView.vue`, `CountUp.vue`, `vue-i18n`, `ScanView.vue`, `RegisterView.vue`, `MapsLinkField.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `App.vue`, `SessionSummaryView.vue`, `SessionFloat.vue`, `KioskQrView.vue`, `api.js`, `DemoKioskSessionView.vue`, `ActiveSessionSheet.vue`, `@phosphor-icons/vue`, `auth.js`, `SessionDetailModal.vue`, `ImageCropper.vue`, `IdleOverlay.vue`, `PlatformActivityTable.vue`, `useIdleTimer.js`, `LandingView.vue`, `VoucherView.vue`, `LanguageSwitch.vue`, `FrontEnd/package.json`, `AppSelect.vue`, `CarbonSourcesNote.vue`, `MaintenanceHistoryModal.vue`, `AdminPagination.vue`, `PlatformSettingsCard.vue`, `UserAvatar.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _753 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.010362146444620671 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._