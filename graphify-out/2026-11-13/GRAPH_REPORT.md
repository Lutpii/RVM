# Graph Report - RVM  (2026-11-13)

## Corpus Check
- 361 files · ~256,892 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2798 nodes · 6064 edges · 188 communities (97 shown, 51 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 116 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a4ff6b62`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AdminView.vue
- BinCollectionRequested
- Composer Configuration
- RvmSessionView.vue
- TransactionController
- Illuminate\Database\Migrations\Migration
- UserSettingsView.vue
- User
- Illuminate\Http\JsonResponse
- RewardItem
- RewardRedemption
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
- SessionStatusAndGraceTest
- DetectionLog
- ActiveSessionSheet.vue
- vitest
- LoginView.vue
- ActivityView.vue
- Illuminate\Http\Request
- router/index.js
- RewardsView.vue
- LandingView.vue
- showToast
- SessionSummaryView.vue
- transactionDisplay.js
- Backend Build Config
- DashboardView.vue
- AdminTableExportTest
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- auth.js
- vue
- DemoKioskSessionView.vue
- AdminRewardConfigTest
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- CarbonService
- RouteServiceProvider.php
- Console Kernel Scheduling
- AdminCachingTest
- SessionDetailModal.vue
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
- autoStartFlow
- AdminLog
- AdminKioskMaintenanceTest
- swipe.js
- AuthVerificationGateTest
- toDatetimeLocalValue
- FrontEnd/package.json
- MachineApiTest
- DemoFlagTest
- tapGesture.test.js
- AdminCompactorSettingsTest
- GoogleCallbackAccountLinkTest.php
- MaintenanceHistoryModal.vue
- saveDetectionReview
- PlaceholderDriver
- compactorFlow.js
- QrController.php
- MachineHeartbeatTest
- AccountSuspensionTest
- CashRedeemTest
- RecordingDriver
- heartbeat.js
- depositAndWait
- DemoDataGenerator
- OneMachineRuleTest
- machineAlerts.test.js
- Illuminate\Support\Facades\Schema
- UserAvatar.vue
- AdminTransactionFiltersTest
- GoogleRedirectFrontendOriginTest
- VoucherModal.vue
- formatAuditLog
- test_yolo.py
- KioskLandingView.vue
- AdminCarbonStatsTest
- dataScope.test.js
- adminTabState.js
- AdminController.php
- startLimitEnd
- MaintenanceRecordsTest
- AdminAuditLogTest
- AdminCashRedeemSettingsTest
- Handler
- rvm-kiosk-launch.sh
- extractQrToken
- redeemNow
- AdminUpdateUserTest
- startCameraMode
- awardPoints
- devDependencies
- vercel.json
- VoucherView.vue
- Illuminate\Database\Schema\Blueprint
- QrSession
- normalizeMachine
- DemoSessionMachineTest
- IdleOverlay.vue
- ai_service_4bin/README.md
- start.sh
- setKioskToken
- AdminEmailFormalReportTest
- scrollReveal.js
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- tabLoading.js
- RvmMachine
- materialIcons.js
- setRewardImageFile
- PointsHistoryLocaleTest.php
- SecurityLogTest
- countMachinesInScope
- WeighAwardsConfiguredPointsTest
- @phosphor-icons/vue
- ExampleTest
- kioskDemo.js
- console.php

## God Nodes (most connected - your core abstractions)
1. `User` - 188 edges
2. `RvmMachine` - 136 edges
3. `TestCase` - 125 edges
4. `RecyclingSession` - 104 edges
5. `Transaction` - 75 edges
6. `AdminController` - 62 edges
7. `RewardItem` - 59 edges
8. `vitest` - 51 edges
9. `RewardRedemption` - 47 edges
10. `PointsHistory` - 45 edges

## Surprising Connections (you probably didn't know these)
- `MachineTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_hardware.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `Machine`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/hardware.py
- `MachineApiTest` --uses--> `RecordingDriver`  [INFERRED]
  BackEnd/ai_service/tests/test_machine_api.py → BackEnd/ai_service/tests/test_hardware.py
- `useRvmStore` --indirect_call--> `endSession()`  [INFERRED]
  FrontEnd/src/store/rvm.js → FrontEnd/src/components/ActiveSessionSheet.vue
- `depositAndWait()` --indirect_call--> `getHardwareState()`  [INFERRED]
  FrontEnd/src/views/RvmSessionView.vue → FrontEnd/src/services/compactor.js

## Import Cycles
- None detected.

## Communities (188 total, 51 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (158): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+150 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.05
Nodes (40): aiConfidence, aiDetected, annotatedImageDataUrl, auth, cameraCountdown, cameraMode, cameraStreamUrl, canvasRef (+32 more)

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (44): auth, cancelEditPassword(), carbonText, changePassword(), confirmDelete, conversionRate, deleteConfirmText, deletingAccount (+36 more)

### Community 7 - "User"
Cohesion: 0.07
Nodes (25): User, AdminRedemptionsListTest, AuthControllerLocaleTest, PointsHistoryOrderTest, PointsHistoryRedeemedTypeTest, QrControllerLocaleTest, QrLifetimeTest, RewardHistoryTest (+17 more)

### Community 8 - "Illuminate\Http\JsonResponse"
Cohesion: 0.08
Nodes (4): AdminController, AdminTableExport, Illuminate\Database\Eloquent\Builder, Illuminate\Http\JsonResponse

### Community 9 - "RewardItem"
Cohesion: 0.08
Nodes (7): RewardItem, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.14
Nodes (4): RewardController, PointsHistory, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.07
Nodes (30): scanOutcome(), activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported, canvasRef (+22 more)

### Community 13 - "FormalReportService"
Cohesion: 0.09
Nodes (15): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, FormalReportServiceTest, Illuminate\Testing\TestResponse, PhpOffice\PhpSpreadsheet\Cell\Coordinate, PhpOffice\PhpSpreadsheet\IOFactory, PhpOffice\PhpSpreadsheet\Spreadsheet (+7 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (23): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp() (+15 more)

### Community 16 - "Controller"
Cohesion: 0.11
Nodes (8): Controller, MachineController, QrController, SessionController, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller, Illuminate\Support\Facades\Route

### Community 17 - "AuthController"
Cohesion: 0.12
Nodes (7): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule, Laravel\Socialite\Facades\Socialite

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (36): isFresh(), changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage(), changeTxPerPage(), changeUsersPerPage() (+28 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (8): DemoData, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest, Illuminate\Console\Command

### Community 23 - "ActiveSessionSheet.vue"
Cohesion: 0.06
Nodes (32): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+24 more)

### Community 24 - "vitest"
Cohesion: 0.23
Nodes (7): flattenKeys(), __dirname, messages, COMPACTOR_KEYS, __dirname, KEYS, vitest

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.06
Nodes (32): ActivityView(), activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, history, redemptions (+24 more)

### Community 27 - "Illuminate\Http\Request"
Cohesion: 0.07
Nodes (19): UserController, AdminMiddleware, Authenticate, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated (+11 more)

### Community 28 - "router/index.js"
Cohesion: 0.06
Nodes (37): AdminView(), demoMachineGuard(), GoodbyeView(), GoogleCallbackView(), KioskLandingView(), NotFoundView(), PrivacyView(), router (+29 more)

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
Cohesion: 0.12
Nodes (22): shouldAutoReturn(), startCountdown(), isDemoKioskPath(), kioskBase(), pathOf(), confirmEndSession(), { showOverlay: showIdleOverlay, msLeft: idleMsLeft, poke: pokeIdle, sessionEnded, endingSession }, auth (+14 more)

### Community 33 - "transactionDisplay.js"
Cohesion: 0.24
Nodes (10): AI_CONFIDENCE_MIN, BIN_LETTERS, confidenceLevel(), itemLabel(), MATERIAL_NAMES, percentOf(), RESULT_CODES, resultReason() (+2 more)

### Community 34 - "Backend Build Config"
Cohesion: 0.14
Nodes (12): devDependencies, axios, laravel-vite-plugin, vite, axios, vite, private, scripts (+4 more)

### Community 35 - "DashboardView.vue"
Cohesion: 0.12
Nodes (12): DashboardView(), demoDataLabel, googleLoginEnabled, auth, AVAILABILITY_LABEL, binTypes, loadingMachines, machines (+4 more)

### Community 37 - "App & Broadcast Providers"
Cohesion: 0.20
Nodes (5): AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\Facades\Broadcast, Illuminate\Support\Facades\Facade, Illuminate\Support\ServiceProvider

### Community 38 - "dependencies"
Cohesion: 0.14
Nodes (14): dependencies, axios, chart.js, jsqr, leaflet, lenis, @phosphor-icons/vue, pinia (+6 more)

### Community 39 - "KioskQrView.vue"
Cohesion: 0.12
Nodes (26): KioskQrView(), afterQrExpiry(), QR_LIFETIME_SECONDS, QR_MAX_REFRESHES, base, clearIntervals(), currentToken, elsewhereMsg (+18 more)

### Community 40 - "auth.js"
Cohesion: 0.19
Nodes (17): ACTIVITY_EVENTS, useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire() (+9 more)

### Community 41 - "vue"
Cohesion: 0.11
Nodes (24): IDLE_COUNTDOWN_MS, IDLE_MS, mount(), useIdleTimer(), SESSION_ENDED_MS, useKioskSessionGuard(), mount(), useSessionWatch() (+16 more)

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (32): DemoKioskSessionView(), itemSaveOutcome(), aiConfidence, auth, autoStartFlow(), awardPoints(), cameraCountdown, cameraFlash (+24 more)

### Community 44 - "App.vue"
Cohesion: 0.16
Nodes (15): auth, route, showAppNav, showKioskDemo, showToast(), theme, toastState, toggleTheme() (+7 more)

### Community 45 - "detectionDateRange.js"
Cohesion: 0.48
Nodes (5): dayOffset(), DETECTION_DATE_PRESETS, detectionDateRange(), toLocalDateInput(), setDetectionDatePreset()

### Community 46 - "ai_service_4bin/app.py"
Cohesion: 0.05
Nodes (41): capture(), classify(), _drop(), drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), _generate_mjpeg() (+33 more)

### Community 47 - "Event Service Provider"
Cohesion: 0.29
Nodes (5): EventServiceProvider, Illuminate\Auth\Events\Registered, Illuminate\Auth\Listeners\SendEmailVerificationNotification, Illuminate\Foundation\Support\Providers\EventServiceProvider, Illuminate\Support\Facades\Event

### Community 48 - "CarbonService"
Cohesion: 0.15
Nodes (6): PublicStatsController, CarbonService, ActiveSessionTest, CarbonServiceTest, ExampleTest, PHPUnit\Framework\TestCase

### Community 49 - "RouteServiceProvider.php"
Cohesion: 0.40
Nodes (4): RouteServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Support\Facades\RateLimiter

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "SessionDetailModal.vue"
Cohesion: 0.15
Nodes (10): closeBtn, emit, items, loadError, loading, props, summary, { t, te } (+2 more)

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

### Community 93 - "autoStartFlow"
Cohesion: 0.38
Nodes (7): isFlapEmpty(), autoStartFlow(), delay(), ensureFlapEmpty(), machineLocked(), recycleAgain(), refreshChamber()

### Community 94 - "AdminLog"
Cohesion: 0.08
Nodes (7): AdminLog, MaintenanceRecord, RewardItemImage, DemoDataGeneratorTest, Carbon, Illuminate\Database\Eloquent\Model, Illuminate\Support\Facades\DB

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.08
Nodes (21): axios, vite, name, private, scripts, build, dev, preview (+13 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.11
Nodes (5): Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 105 - "GoogleCallbackAccountLinkTest.php"
Cohesion: 0.42
Nodes (3): GoogleCallbackAccountLinkTest, Laravel\Socialite\Two\User, Mockery

### Community 106 - "MaintenanceHistoryModal.vue"
Cohesion: 0.11
Nodes (17): actionTaken, closeBtn, closeError, closeRecord(), closingId, emit, errorMessage(), form (+9 more)

### Community 107 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 108 - "PlaceholderDriver"
Cohesion: 0.21
Nodes (3): PlaceholderDriver, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.24
Nodes (13): ACCEPTED_2BIN, AUTO_END_SECONDS, limitEndsSession(), limitFor(), oneDecision(), readDepositResult(), SESSION_LIMIT_PER_MATERIAL, sessionLimitReached() (+5 more)

### Community 110 - "QrController.php"
Cohesion: 0.16
Nodes (6): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, SimpleSoftwareIO\QrCode\Facades\QrCode, static

### Community 115 - "heartbeat.js"
Cohesion: 0.38
Nodes (4): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), isMachineFault()

### Community 116 - "depositAndWait"
Cohesion: 0.31
Nodes (9): claimSwitchDecision(), continueSwitch, depositAndWait(), drawBoundingBoxes(), openMaterialSwitch(), recordInvalidItem(), simulateInsert(), stopSwitchTimer() (+1 more)

### Community 117 - "DemoDataGenerator"
Cohesion: 0.08
Nodes (7): AiService, DemoDataGenerator, Carbon, GuestCarbonTest, HardwareCompactorProxyTest, Carbon\Carbon, Carbon\CarbonInterface

### Community 120 - "machineAlerts.test.js"
Cohesion: 0.25
Nodes (10): buildMachineAlerts(), connectivitySummary(), now, machineAvailability(), machineConnectivity(), ONLINE_WINDOW_MS, now, machineAlerts (+2 more)

### Community 122 - "UserAvatar.vue"
Cohesion: 0.18
Nodes (13): iconSize, initial, picked, props, style, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR (+5 more)

### Community 125 - "VoucherModal.vue"
Cohesion: 0.18
Nodes (14): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t } (+6 more)

### Community 126 - "formatAuditLog"
Cohesion: 0.33
Nodes (7): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditRows

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 128 - "KioskLandingView.vue"
Cohesion: 0.20
Nodes (8): base, checked, isDemoRoute, machineLocation, machineName, notDemo, route, router

### Community 130 - "dataScope.test.js"
Cohesion: 0.23
Nodes (7): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), fetchChartData(), setDataScope()

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "AdminController.php"
Cohesion: 0.15
Nodes (4): CompactorSettingsService, RewardConfigService, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Storage

### Community 134 - "startLimitEnd"
Cohesion: 0.50
Nodes (5): flushChamber(), confirmEndSession(), materialLimit(), { showOverlay: showIdleOverlay, msLeft: idleMsLeft, poke: pokeIdle, sessionEnded, endingSession }, startLimitEnd()

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

### Community 143 - "startCameraMode"
Cohesion: 0.40
Nodes (5): captureFromCamera(), captureFromHardware(), openCamera(), startCameraMode(), stopCamera()

### Community 144 - "awardPoints"
Cohesion: 0.48
Nodes (5): isNetworkError(), newIdempotencyKey(), retryOnce(), awardPoints(), randomPointsFallback()

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 148 - "VoucherView.vue"
Cohesion: 0.15
Nodes (9): VoucherView(), auth, error, loading, markError, marking, route, { t } (+1 more)

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 160 - "IdleOverlay.vue"
Cohesion: 0.50
Nodes (3): offset, props, seconds

### Community 163 - "setKioskToken"
Cohesion: 0.50
Nodes (4): onEnded(), setKioskToken(), demoMachineId(), ensureSessionOnThisMachine()

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 173 - "RvmMachine"
Cohesion: 0.06
Nodes (12): ExpireStaleSessions, RecyclingSession, RvmMachine, Transaction, AccountSummaryTest, AdminExportExcelTest, DetectionLogTest, ExpireStaleSessionsTest (+4 more)

### Community 175 - "setRewardImageFile"
Cohesion: 0.67
Nodes (3): handleRewardImageChange(), handleRewardImageDrop(), setRewardImageFile()

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 186 - "@phosphor-icons/vue"
Cohesion: 0.39
Nodes (5): activeKey, route, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

### Community 188 - "kioskDemo.js"
Cohesion: 0.36
Nodes (7): DEMO_ITEMS, itemCounts(), RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence(), demoCounts

## Knowledge Gaps
- **666 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+661 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1056 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **51 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AdminCarbonStatsTest`, `AdminController.php`, `MaintenanceRecordsTest`, `Illuminate\Http\JsonResponse`, `AdminAuditLogTest`, `AdminCashRedeemSettingsTest`, `RewardItem`, `FormalReportService`, `AdminUpdateUserTest`, `Controller`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `QrSession`, `Illuminate\Http\Request`, `DemoSessionMachineTest`, `AdminEmailFormalReportTest`, `AdminTableExportTest`, `AdminRewardConfigTest`, `RvmMachine`, `CarbonService`, `PointsHistoryLocaleTest.php`, `SecurityLogTest`, `AdminCachingTest`, `WeighAwardsConfiguredPointsTest`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `GoogleCallbackAccountLinkTest.php`, `QrController.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `DemoDataGenerator`, `OneMachineRuleTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `AdminView.vue`, `KioskLandingView.vue`, `RvmSessionView.vue`, `UserSettingsView.vue`, `ScanView.vue`, `RegisterView.vue`, `VoucherView.vue`, `ActiveSessionSheet.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `LandingView.vue`, `IdleOverlay.vue`, `SessionSummaryView.vue`, `DashboardView.vue`, `KioskQrView.vue`, `auth.js`, `DemoKioskSessionView.vue`, `App.vue`, `SessionDetailModal.vue`, `@phosphor-icons/vue`, `AdminPagination.vue`, `ImageCropper.vue`, `FrontEnd/package.json`, `MaintenanceHistoryModal.vue`, `UserAvatar.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Why does `RvmMachine` connect `RvmMachine` to `BinCollectionRequested`, `AdminCarbonStatsTest`, `AdminController.php`, `TransactionController`, `User`, `Illuminate\Http\JsonResponse`, `MaintenanceRecordsTest`, `FormalReportService`, `Controller`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `QrSession`, `Illuminate\Http\Request`, `DemoSessionMachineTest`, `AdminEmailFormalReportTest`, `AdminTableExportTest`, `CarbonService`, `PointsHistoryLocaleTest.php`, `SecurityLogTest`, `WeighAwardsConfiguredPointsTest`, `AdminLog`, `AdminKioskMaintenanceTest`, `DemoFlagTest`, `QrController.php`, `MachineHeartbeatTest`, `DemoDataGenerator`, `OneMachineRuleTest`, `AdminTransactionFiltersTest`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _666 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.011049723756906077 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._