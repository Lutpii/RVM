# Graph Report - RVM  (2026-11-15)

## Corpus Check
- 389 files · ~270,205 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2908 nodes · 6332 edges · 190 communities (98 shown, 51 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 125 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `3671e991`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- BinCollectionRequested
- Composer Configuration
- RvmSessionView.vue
- CarbonService
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
- ActiveSessionSheet.vue
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
- DashboardView.vue
- vue
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- UserController
- autoStartFlow
- DemoKioskSessionView.vue
- SessionFloat.vue
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- @phosphor-icons/vue
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
- AdminLog
- AdminKioskMaintenanceTest
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- PointsHistory
- AdminCompactorSettingsTest
- GoogleCallbackAccountLinkTest.php
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
- depositAndWait
- DemoDataGenerator
- AdminCarbonStatsTest
- Illuminate\Database\Schema\Blueprint
- saveDetectionReview
- UserAvatar.vue
- AdminTransactionFiltersTest
- kioskDemo.js
- VoucherModal.vue
- intl
- test_yolo.py
- DemoSessionMachineTest
- IdleOverlay.vue
- dataScope.test.js
- adminTabState.js
- Illuminate\Support\Str
- startLimitEnd
- scripts
- buildRewardUpdatePayload
- heartbeat.js
- Handler
- rvm-kiosk-launch.sh
- extractQrToken
- redeemNow
- transactionPoints.js
- awardPoints
- devDependencies
- vercel.json
- CompactorSettingsService
- RvmMachine
- normalizeMachine
- console.php
- Illuminate\Support\Facades\Schema
- startCameraMode
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- Authenticate.php
- useLanguage.js
- AdminRewardConfigTest
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- GoogleRedirectFrontendOriginTest
- RecyclingSession
- index.php
- AdminAuditLogTest
- countMachinesInScope
- ExampleTest
- MaintenanceRecordsTest
- OneMachineRuleTest
- static
- QrSession
- 2026_11_15_000001_add_display_no_to_users_table.php
- scrollReveal.js
- formatUserId

## God Nodes (most connected - your core abstractions)
1. `User` - 199 edges
2. `RvmMachine` - 141 edges
3. `TestCase` - 131 edges
4. `RecyclingSession` - 106 edges
5. `Transaction` - 77 edges
6. `AdminController` - 64 edges
7. `vitest` - 62 edges
8. `RewardItem` - 59 edges
9. `RewardRedemption` - 47 edges
10. `vue` - 47 edges

## Surprising Connections (you probably didn't know these)
- `MachineTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_hardware.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `RecordingDriver`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/tests/test_hardware.py
- `useRvmStore` --indirect_call--> `endSession()`  [INFERRED]
  FrontEnd/src/store/rvm.js → FrontEnd/src/components/ActiveSessionSheet.vue
- `useActiveSessionStore` --indirect_call--> `start()`  [INFERRED]
  FrontEnd/src/store/activeSession.js → FrontEnd/src/composables/useLenisScroll.js

## Import Cycles
- None detected.

## Communities (190 total, 51 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (166): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+158 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (40): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+32 more)

### Community 4 - "CarbonService"
Cohesion: 0.15
Nodes (6): PublicStatsController, CarbonService, ActiveSessionTest, CarbonServiceTest, ExampleTest, PHPUnit\Framework\TestCase

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (44): auth, cancelEditPassword(), carbonText, changePassword(), confirmDelete, conversionRate, deleteConfirmText, deletingAccount (+36 more)

### Community 7 - "User"
Cohesion: 0.07
Nodes (26): User, AdminRedemptionsListTest, AuthControllerLocaleTest, IndonesianLocaleTest, MachineOrderTest, PointsHistoryOrderTest, QrControllerLocaleTest, QrLifetimeTest (+18 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (4): AdminController, AdminTableExport, Illuminate\Database\Eloquent\Builder, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.07
Nodes (8): RewardItem, RewardItemImage, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.13
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.07
Nodes (30): scanOutcome(), activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported, canvasRef (+22 more)

### Community 13 - "FormalReportService"
Cohesion: 0.06
Nodes (17): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, AdminTableExportTest, UserDisplayNumberTest, FormalReportServiceTest, Illuminate\Testing\TestResponse, PhpOffice\PhpSpreadsheet\Cell\Coordinate (+9 more)

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
Nodes (34): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+26 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (8): DemoData, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest, Illuminate\Console\Command

### Community 23 - "ActiveSessionSheet.vue"
Cohesion: 0.06
Nodes (27): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+19 more)

### Community 24 - "vitest"
Cohesion: 0.09
Nodes (16): sourceFiles(), SRC, adminUi, SOURCES, used, flattenKeys(), __dirname, leaves() (+8 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.08
Nodes (19): ActivityView(), clock(), filters, hasLoadError, loading, materialList(), MATERIALS, openSessionCode (+11 more)

### Community 27 - "Illuminate\Http\JsonResponse"
Cohesion: 0.17
Nodes (3): TransactionController, Illuminate\Http\JsonResponse, self

### Community 28 - "router/index.js"
Cohesion: 0.05
Nodes (42): AdminView(), GoodbyeView(), GoogleCallbackView(), KioskLandingView(), NotFoundView(), PrivacyView(), RegisterView(), routes (+34 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "LandingView.vue"
Cohesion: 0.12
Nodes (15): LandingView(), acceptedChips, barLabel, intlLocale, itemsText(), { locale, t }, MONTH_MATERIALS, monthLabel (+7 more)

### Community 31 - "showToast"
Cohesion: 0.10
Nodes (26): cleanParams(), downloadExcel(), excelFilename(), kioskLink(), addRewardItem(), askConfirm(), buildRewardItemFormData(), capitalize() (+18 more)

### Community 32 - "SessionSummaryView.vue"
Cohesion: 0.13
Nodes (19): shouldAutoReturn(), startCountdown(), SessionSummaryView(), isDemoKioskPath(), kioskBase(), pathOf(), auth, clearCountdown() (+11 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.17
Nodes (15): AI_CONFIDENCE_MIN, BIN_LETTERS, cap(), confidenceLevel(), isKnown(), itemLabel(), percentOf(), RESULT_CODES (+7 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.09
Nodes (22): DashboardView(), buildMachineAlerts(), connectivitySummary(), now, demoDataLabel, googleLoginEnabled, machineAvailability(), machineConnectivity() (+14 more)

### Community 36 - "vue"
Cohesion: 0.10
Nodes (28): IDLE_COUNTDOWN_MS, IDLE_MS, mount(), useIdleTimer(), SESSION_ENDED_MS, useKioskSessionGuard(), onEnded(), mount() (+20 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.12
Nodes (26): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, base, clearIntervals(), currentToken, elsewhereMsg (+18 more)

### Community 40 - "UserController"
Cohesion: 0.16
Nodes (3): UserController, AvatarIcon, Illuminate\Support\Facades\Hash

### Community 41 - "autoStartFlow"
Cohesion: 0.38
Nodes (7): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), recycleAgain(), refreshChamber(), takeBackSwitch()

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (26): DemoKioskSessionView(), aiConfidence, auth, cameraCountdown, cameraFlash, chosenId, confirmEndSession(), currentStepLabel (+18 more)

### Community 43 - "SessionFloat.vue"
Cohesion: 0.12
Nodes (17): summary, auth, isUserRoute, mode, route, shape, store, summary (+9 more)

### Community 44 - "App.vue"
Cohesion: 0.15
Nodes (14): auth, route, showAppNav, showKioskDemo, showToast(), theme, toastState, toggleTheme() (+6 more)

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.05
Nodes (41): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+33 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "@phosphor-icons/vue"
Cohesion: 0.12
Nodes (14): activeKey, route, VoucherView(), NAV_ITEMS, resolveActiveNavKey(), auth, error, loading (+6 more)

### Community 49 - "Closure"
Cohesion: 0.08
Nodes (17): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, RouteServiceProvider (+9 more)

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
Cohesion: 0.18
Nodes (14): bounds, changePage(), changePerPage(), emit, numberFormatter, pageItems, props, { t, locale } (+6 more)

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

### Community 94 - "AdminLog"
Cohesion: 0.09
Nodes (5): AdminLog, MaintenanceRecord, DemoDataGeneratorTest, Carbon, Illuminate\Database\Eloquent\Model

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.11
Nodes (16): axios, vite, name, private, version, AI_SERVICE_API_KEY, autoprefixer, chart.js (+8 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.11
Nodes (5): Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "PointsHistory"
Cohesion: 0.12
Nodes (5): PointsHistory, AccountSummaryTest, AdminUpdateUserTest, PointsHistoryLocaleTest, PointsHistoryRedeemedTypeTest

### Community 105 - "GoogleCallbackAccountLinkTest.php"
Cohesion: 0.36
Nodes (4): GoogleCallbackAccountLinkTest, Laravel\Socialite\Facades\Socialite, Laravel\Socialite\Two\User, Mockery

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (18): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+10 more)

### Community 107 - "LanguageSwitch.vue"
Cohesion: 0.26
Nodes (9): act(), button, choose(), { locale, languages, currentLanguage, setLanguage }, onKey, onPointer(), open, root (+1 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.21
Nodes (3): PlaceholderDriver, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.24
Nodes (13): ACCEPTED_2BIN, AUTO_END_SECONDS, limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL, sessionLimitReached() (+5 more)

### Community 110 - "activityTimeline.test.js"
Cohesion: 0.19
Nodes (12): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, history, redemptions, sessions (+4 more)

### Community 115 - "Controller"
Cohesion: 0.10
Nodes (9): Controller, MachineController, QrController, SessionController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller, Illuminate\Support\Facades\Route (+1 more)

### Community 116 - "depositAndWait"
Cohesion: 0.31
Nodes (9): depositItem(), claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), recordInvalidItem(), simulateInsert() (+1 more)

### Community 117 - "DemoDataGenerator"
Cohesion: 0.07
Nodes (8): AiService, DemoDataGenerator, Carbon, RewardConfigService, GuestCarbonTest, HardwareCompactorProxyTest, Carbon\Carbon, Carbon\CarbonInterface

### Community 120 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 122 - "UserAvatar.vue"
Cohesion: 0.18
Nodes (13): iconSize, initial, picked, props, style, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR (+5 more)

### Community 124 - "kioskDemo.js"
Cohesion: 0.19
Nodes (15): DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence(), autoStartFlow() (+7 more)

### Community 125 - "VoucherModal.vue"
Cohesion: 0.18
Nodes (14): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t } (+6 more)

### Community 126 - "intl"
Cohesion: 0.15
Nodes (16): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditLabel(), auditRows (+8 more)

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 129 - "IdleOverlay.vue"
Cohesion: 0.50
Nodes (3): progress, props, seconds

### Community 130 - "dataScope.test.js"
Cohesion: 0.23
Nodes (7): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), fetchChartData(), setDataScope()

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "Illuminate\Support\Str"
Cohesion: 0.25
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str

### Community 134 - "startLimitEnd"
Cohesion: 0.50
Nodes (5): flushChamber(), confirmEndSession(), materialLimit(), { showOverlay: showIdleOverlay, msLeft: idleMsLeft, poke: pokeIdle, sessionEnded, endingSession }, startLimitEnd()

### Community 135 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

### Community 137 - "heartbeat.js"
Cohesion: 0.32
Nodes (5): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault(), machineLocked()

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

### Community 144 - "awardPoints"
Cohesion: 0.43
Nodes (6): isNetworkError(), newIdempotencyKey(), retryOnce(), saveUserItem(), awardPoints(), randomPointsFallback()

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 153 - "RvmMachine"
Cohesion: 0.08
Nodes (9): RvmMachine, DetectionLogTest, ExpireStaleSessionsTest, SecurityLogTest, CollidingUser, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log (+1 more)

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 159 - "startCameraMode"
Cohesion: 0.40
Nodes (5): captureFromCamera(), captureFromHardware(), openCamera(), startCameraMode(), stopCamera()

### Community 164 - "useLanguage.js"
Cohesion: 0.30
Nodes (9): applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, isLanguage(), LANGUAGES, resolveStoredLanguage() (+1 more)

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 173 - "RecyclingSession"
Cohesion: 0.08
Nodes (9): ExpireStaleSessions, RecyclingSession, Transaction, AdminExportExcelTest, PublicStatsTest, SessionSummaryReceiptTest, TransactionCompleteAtomicityTest, WeighAwardsConfiguredPointsTest (+1 more)

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

## Knowledge Gaps
- **682 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+677 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1081 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **51 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `DemoSessionMachineTest`, `CarbonService`, `Illuminate\Http\Request`, `RewardItem`, `FormalReportService`, `AdminEmailFormalReportTest`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `RvmMachine`, `AdminCashRedeemSettingsTest`, `AdminRewardConfigTest`, `RecyclingSession`, `Closure`, `AdminAuditLogTest`, `AdminCachingTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `static`, `QrSession`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `PointsHistory`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `Controller`, `DemoDataGenerator`, `AdminCarbonStatsTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `AdminView.vue`, `IdleOverlay.vue`, `RvmSessionView.vue`, `UserSettingsView.vue`, `ScanView.vue`, `RegisterView.vue`, `ActiveSessionSheet.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `LandingView.vue`, `SessionSummaryView.vue`, `DashboardView.vue`, `useLanguage.js`, `KioskQrView.vue`, `DemoKioskSessionView.vue`, `SessionFloat.vue`, `App.vue`, `@phosphor-icons/vue`, `auth.js`, `AdminPagination.vue`, `ImageCropper.vue`, `FrontEnd/package.json`, `MaintenanceHistoryModal.vue`, `LanguageSwitch.vue`, `UserAvatar.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Why does `TestCase` connect `User` to `DemoSessionMachineTest`, `CarbonService`, `RewardItem`, `FormalReportService`, `AdminEmailFormalReportTest`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `RvmMachine`, `AdminCashRedeemSettingsTest`, `AdminRewardConfigTest`, `GoogleRedirectFrontendOriginTest`, `RecyclingSession`, `AdminAuditLogTest`, `AdminCachingTest`, `ExampleTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `Test Application Bootstrap`, `QrSession`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `PointsHistory`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `DemoDataGenerator`, `AdminCarbonStatsTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _682 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.010581427390465694 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._