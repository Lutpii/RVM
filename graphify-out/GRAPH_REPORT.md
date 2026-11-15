# Graph Report - RVM  (2026-11-15)

## Corpus Check
- 397 files · ~274,736 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2943 nodes · 6425 edges · 192 communities (101 shown, 47 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 127 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `de88cda3`
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
- TestCase
- Illuminate\Http\Request
- RewardItem
- RewardRedemption
- GpioDriver
- ScanView.vue
- FormalReportService
- ♻️ Reverse Vending Machine (RVM) — Full Stack Web System
- RegisterView.vue
- User
- AuthController
- AdminBinCollectionEmailTest
- AdminPaginationTest
- fetchTabData
- SessionStatusAndGraceTest
- DetectionLog
- SessionDetailModal.vue
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
- Transaction
- vue
- App & Broadcast Providers
- dependencies
- KioskQrView.vue
- auth.js
- VoucherView.vue
- DemoKioskSessionView.vue
- ActiveSessionSheet.vue
- App.vue
- detectionDateRange.js
- ai_service_4bin/app.py
- Event Service Provider
- QrSession
- Closure
- Console Kernel Scheduling
- AdminCachingTest
- useIdleLogout
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
- useKioskSessionGuard.js
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
- DemoDataGenerator
- AdminCarbonStatsTest
- saveDetectionReview
- Illuminate\Support\Facades\Schema
- UserAvatar.vue
- AdminTransactionFiltersTest
- PointsHistory
- VoucherModal.vue
- intl
- test_yolo.py
- @phosphor-icons/vue
- rvm.js
- dataScope.test.js
- adminTabState.js
- DashboardView.vue
- voucher.js
- RewardConfigService
- FrontEnd/vite.config.js
- IdleOverlay.vue
- Handler
- rvm-kiosk-launch.sh
- scanMaintFrame
- redeemNow
- downloadExcel
- awardPoints
- devDependencies
- vercel.json
- CompactorSettingsService
- Authenticate.php
- Illuminate\Database\Schema\Blueprint
- cancelEditPassword
- normalizeMachine
- num
- AdminCashRedeemSettingsTest
- ai_service_4bin/README.md
- start.sh
- useLanguage.js
- AdminRewardConfigTest
- RVM Web Application: Test Plan Results (T01–T09) and Issue Register
- GoogleRedirectFrontendOriginTest
- AdminController.php
- RvmMachine
- AdminAuditLogTest
- countMachinesInScope
- MaintenanceRecordsTest
- OneMachineRuleTest
- UserDisplayNumberTest
- EnforceIdleTimeoutTest
- scripts
- index.php
- 2026_11_15_000001_add_display_no_to_users_table.php
- formatUserId

## God Nodes (most connected - your core abstractions)
1. `User` - 204 edges
2. `RvmMachine` - 145 edges
3. `TestCase` - 135 edges
4. `RecyclingSession` - 109 edges
5. `Transaction` - 78 edges
6. `AdminController` - 64 edges
7. `vitest` - 64 edges
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

## Communities (192 total, 47 thin omitted)

### Community 0 - "AdminView.vue"
Cohesion: 0.01
Nodes (167): activeDetectionDateFilter, activeMachines, activeTab, adminMachines, adminRedemptions, applyingRewardCrop, auditAction, auditActions (+159 more)

### Community 1 - "BinCollectionRequested"
Cohesion: 0.12
Nodes (17): BinCollectionRequested, Content, Envelope, FormalReportGenerated, Content, Envelope, OtpMail, Content (+9 more)

### Community 2 - "Composer Configuration"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "RvmSessionView.vue"
Cohesion: 0.04
Nodes (60): flushChamber(), isFlapEmpty(), aiConfidence, aiDetected, annotatedImageDataUrl, auth, autoStartFlow(), cameraCountdown (+52 more)

### Community 4 - "CarbonService"
Cohesion: 0.15
Nodes (6): PublicStatsController, CarbonService, ActiveSessionTest, CarbonServiceTest, ExampleTest, PHPUnit\Framework\TestCase

### Community 6 - "UserSettingsView.vue"
Cohesion: 0.04
Nodes (40): auth, carbonText, confirmDelete, conversionRate, deleteConfirmText, deletingAccount, draft, draftHex (+32 more)

### Community 7 - "TestCase"
Cohesion: 0.07
Nodes (19): RecyclingSession, AdminRedemptionsListTest, ExampleTest, MachineOrderTest, PointsHistoryOrderTest, QrControllerLocaleTest, QrLifetimeTest, RewardHistoryTest (+11 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (4): AdminController, AdminTableExport, Illuminate\Database\Eloquent\Builder, Illuminate\Http\Request

### Community 9 - "RewardItem"
Cohesion: 0.08
Nodes (7): RewardItem, AdminRewardItemsCrudTest, RewardCatalogTest, RewardItemAvailabilityTest, RewardRedeemTest, RewardVoucherTest, UploadedFile

### Community 10 - "RewardRedemption"
Cohesion: 0.14
Nodes (3): RewardController, RewardRedemption, CashRedeemSettingsService

### Community 11 - "GpioDriver"
Cohesion: 0.06
Nodes (22): GpioDriver, HardwareError, Real GPIO driver for the 2-bin DSME machine (HW_DRIVER=gpio). Ported from…, Stop every motor and close the flap. Called after any failure., Open the flap and hold it so the item drops into the compactor., Press the batch, then bring the compactor back., Tilt to the material's bin, hold, then return to the limit switch., Release the pins (tests and shutdown). (+14 more)

### Community 12 - "ScanView.vue"
Cohesion: 0.07
Nodes (31): extractQrToken(), scanOutcome(), activeMachines, activeStore, auth, cameraActive, cameraError, cameraSupported (+23 more)

### Community 13 - "FormalReportService"
Cohesion: 0.06
Nodes (17): FormalReportService, ReadsExcelDownloads, AdminDataScopeTest, AdminTableExportTest, LastLoginTest, FormalReportServiceTest, Illuminate\Testing\TestResponse, PhpOffice\PhpSpreadsheet\Cell\Coordinate (+9 more)

### Community 14 - "♻️ Reverse Vending Machine (RVM) — Full Stack Web System"
Cohesion: 0.05
Nodes (38): 0. Prasyarat ✅ (sudah diverifikasi), 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt), 1. Install paket sistem, 2. Permission GPIO & kamera, 3. Clone project & setup database, 4. Backend (Laravel), 5. AI service (kamera + YOLO + servo), 6. Frontend (build production) (+30 more)

### Community 15 - "RegisterView.vue"
Cohesion: 0.10
Nodes (23): RegisterView(), auth, clearDraft(), error, form, handleRegister(), handleResendOtp(), handleVerifyOtp() (+15 more)

### Community 16 - "User"
Cohesion: 0.08
Nodes (11): User, AdminEmailFormalReportTest, AuthControllerLocaleTest, IndonesianLocaleTest, SessionControllerLocaleTest, TransactionControllerLocaleTest, CollidingUser, Illuminate\Database\UniqueConstraintViolationException (+3 more)

### Community 17 - "AuthController"
Cohesion: 0.12
Nodes (6): AuthController, FonnteService, SecurityLog, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Validator, Illuminate\Validation\Rule

### Community 20 - "fetchTabData"
Cohesion: 0.06
Nodes (34): isFresh(), resolveLoadingFlag(), TAB_LOADING_FLAG, changeAuditPerPage(), changeDetectionPerPage(), changeRedemptionsPerPage(), changeRewardItemsPerPage(), changeSessionsPerPage() (+26 more)

### Community 22 - "DetectionLog"
Cohesion: 0.08
Nodes (9): DemoData, ExpireStaleSessions, MachineKey, OrganizeDetectionCaptures, DetectionLog, DetectionCaptureStorage, AdminDetectionLogsTest, OrganizeDetectionCapturesTest (+1 more)

### Community 23 - "SessionDetailModal.vue"
Cohesion: 0.14
Nodes (10): closeBtn, emit, items, loadError, loading, props, summary, { t, te } (+2 more)

### Community 24 - "vitest"
Cohesion: 0.06
Nodes (27): sourceFiles(), SRC, adminUi, SOURCES, used, LANGS, flattenKeys(), __dirname (+19 more)

### Community 25 - "LoginView.vue"
Cohesion: 0.10
Nodes (17): LoginView(), auth, error, form, handleEmailLogin(), handleResendLoginOtp(), loading, loginMethod (+9 more)

### Community 26 - "ActivityView.vue"
Cohesion: 0.08
Nodes (19): ActivityView(), clock(), filters, hasLoadError, loading, materialList(), MATERIALS, openSessionCode (+11 more)

### Community 27 - "Illuminate\Http\JsonResponse"
Cohesion: 0.11
Nodes (5): TransactionController, UserController, AvatarIcon, Illuminate\Http\JsonResponse, self

### Community 28 - "router/index.js"
Cohesion: 0.06
Nodes (37): AdminView(), DemoKioskSessionView(), demoMachineGuard(), GoodbyeView(), GoogleCallbackView(), NotFoundView(), PrivacyView(), routes (+29 more)

### Community 29 - "RewardsView.vue"
Cohesion: 0.12
Nodes (19): RewardsView(), activeCategory, activeVoucher, auth, categories, detailItem, fetchItems(), fetchVouchers() (+11 more)

### Community 30 - "LandingView.vue"
Cohesion: 0.12
Nodes (15): LandingView(), acceptedChips, barLabel, intlLocale, itemsText(), { locale, t }, MONTH_MATERIALS, monthLabel (+7 more)

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

### Community 35 - "Transaction"
Cohesion: 0.14
Nodes (4): Transaction, AdminExportExcelTest, SessionSummaryReceiptTest, TransactionCompleteAtomicityTest

### Community 36 - "vue"
Cohesion: 0.09
Nodes (20): mounted(), prefersReducedMotion(), app, i18n, pinia, startLanguage, KioskLandingView(), router (+12 more)

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
Cohesion: 0.26
Nodes (10): ACTIVITY_EVENTS, registerClearAuth(), useActiveSessionStore, useAuthStore, clearIdleActivity(), IDLE_ACTIVITY_STORAGE_KEY, readIdleActivity(), resetIdleActivity() (+2 more)

### Community 41 - "VoucherView.vue"
Cohesion: 0.15
Nodes (9): VoucherView(), auth, error, loading, markError, marking, route, { t } (+1 more)

### Community 42 - "DemoKioskSessionView.vue"
Cohesion: 0.07
Nodes (39): DEMO_ITEMS, itemCounts(), itemSaveOutcome(), RESUME_TO_CHOOSE, resumeStep(), sessionMatchesMachine(), simulatedConfidence(), aiConfidence (+31 more)

### Community 43 - "ActiveSessionSheet.vue"
Cohesion: 0.06
Nodes (32): cancelBtn, closeBtn, confirming, dialogEl, emit, endBtn, endError, ending (+24 more)

### Community 44 - "App.vue"
Cohesion: 0.16
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

### Community 48 - "QrSession"
Cohesion: 0.29
Nodes (3): QrController, QrSession, SimpleSoftwareIO\QrCode\Facades\QrCode

### Community 49 - "Closure"
Cohesion: 0.10
Nodes (13): AdminMiddleware, EnforceIdleTimeout, EnsureAccountActive, KioskAuthMiddleware, MachineAuthMiddleware, RedirectIfAuthenticated, SetLocaleFromHeader, Closure (+5 more)

### Community 50 - "Console Kernel Scheduling"
Cohesion: 0.47
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 52 - "useIdleLogout"
Cohesion: 0.50
Nodes (9): useIdleLogout(), arm(), attachListeners(), clearTimer(), detachListeners(), disarm(), expire(), onActivity() (+1 more)

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

### Community 93 - "tapGesture.test.js"
Cohesion: 0.26
Nodes (9): installGhostClickGuard(), mounted(), GHOST_CLICK_MS, GHOST_CLICK_RADIUS_PX, isGhostClick(), isTap(), TAP_MAX_MOVE_PX, TAP_MAX_MS (+1 more)

### Community 94 - "AdminLog"
Cohesion: 0.08
Nodes (6): AdminLog, MaintenanceRecord, RewardItemImage, DemoDataGeneratorTest, Carbon, Illuminate\Database\Eloquent\Model

### Community 96 - "swipe.js"
Cohesion: 0.19
Nodes (19): applyTouchAction(), isEnabled(), mounted(), prefersReducedMotion(), setFeedback(), setOffset(), snapBack(), updated() (+11 more)

### Community 98 - "toDatetimeLocalValue"
Cohesion: 0.29
Nodes (6): toDatetimeLocalValue(), applyRewardCrop(), openAddRewardItem(), openEditRewardItem(), resetRewardImage(), setRewardImagePreview()

### Community 99 - "FrontEnd/package.json"
Cohesion: 0.14
Nodes (13): axios, vite, name, private, version, autoprefixer, chart.js, jsqr (+5 more)

### Community 100 - "MachineApiTest"
Cohesion: 0.11
Nodes (5): Compactor hardware for the 2-bin DSME RVM. A gate (flap) servo holds the item…, create_machine_blueprint(), HTTP routes for the 2-bin compactor (see hardware.Machine). Laravel proxies…, fake_require_api_key(), MachineApiTest

### Community 103 - "useKioskSessionGuard.js"
Cohesion: 0.18
Nodes (13): IDLE_COUNTDOWN_MS, IDLE_MS, mount(), useIdleTimer(), SESSION_ENDED_MS, useKioskSessionGuard(), onEnded(), mount() (+5 more)

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
Cohesion: 0.21
Nodes (3): PlaceholderDriver, Stands in for the real GPIO driver: same timing, no hardware., PlaceholderDriverTest

### Community 109 - "compactorFlow.js"
Cohesion: 0.13
Nodes (23): HEARTBEAT_INTERVAL_MS, startHeartbeat(), tick(), ACCEPTED_2BIN, AUTO_END_SECONDS, isMachineFault(), limitEndsSession(), limitFor() (+15 more)

### Community 110 - "activityTimeline.test.js"
Cohesion: 0.19
Nodes (12): activeFilterCount(), buildActivityTimeline(), filterTimeline(), groupByDay(), RANGE_DAYS, history, redemptions, sessions (+4 more)

### Community 115 - "Controller"
Cohesion: 0.10
Nodes (11): Controller, MachineController, SessionController, RouteServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Illuminate\Foundation\Validation\ValidatesRequests (+3 more)

### Community 116 - "Illuminate\Support\Str"
Cohesion: 0.16
Nodes (5): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, static

### Community 117 - "DemoDataGenerator"
Cohesion: 0.07
Nodes (7): AiService, DemoDataGenerator, Carbon, GuestCarbonTest, HardwareCompactorProxyTest, Carbon\Carbon, Carbon\CarbonInterface

### Community 120 - "saveDetectionReview"
Cohesion: 0.15
Nodes (16): filterHidden(), pruneSettled(), removeLog(), restoreLog(), a, b, c, closeIncorrectReview() (+8 more)

### Community 122 - "UserAvatar.vue"
Cohesion: 0.18
Nodes (13): iconSize, initial, picked, props, style, AVATAR_COLOURS, AVATAR_ICONS, DEFAULT_AVATAR (+5 more)

### Community 124 - "PointsHistory"
Cohesion: 0.12
Nodes (5): PointsHistory, AccountSummaryTest, AdminUpdateUserTest, PointsHistoryLocaleTest, PointsHistoryRedeemedTypeTest

### Community 125 - "VoucherModal.vue"
Cohesion: 0.25
Nodes (8): formatDate(), isUsed, props, qrDataUrl, saveError, saveImage(), saving, { t }

### Community 126 - "intl"
Cohesion: 0.13
Nodes (18): AUDIT_ACTION_LABELS, detailsMessage(), formatAuditLog(), base, titleCase(), auditActionLabel(), auditLabel(), auditMessage() (+10 more)

### Community 127 - "test_yolo.py"
Cohesion: 0.46
Nodes (7): drop_back_left(), drop_back_right(), drop_front_left(), drop_front_right(), move_slowly(), reset_servo_initial(), trigger_servo_thread()

### Community 128 - "@phosphor-icons/vue"
Cohesion: 0.39
Nodes (5): activeKey, route, NAV_ITEMS, resolveActiveNavKey(), @phosphor-icons/vue

### Community 129 - "rvm.js"
Cohesion: 0.50
Nodes (5): depositItem(), getHardwareState(), readKioskState(), readStoredProfile(), useRvmStore

### Community 130 - "dataScope.test.js"
Cohesion: 0.17
Nodes (9): DATA_SCOPE_KEY, DATA_SCOPES, isStaleScope(), readDataScope(), writeDataScope(), DAYS, weekdayLabel(), fetchChartData() (+1 more)

### Community 131 - "adminTabState.js"
Cohesion: 0.32
Nodes (6): buildAdminQuery(), DEFAULT_ADMIN_TAB, DEFAULT_DETECTION_VIEW, DETECTION_VIEWS, parseAdminQuery(), TABS

### Community 132 - "DashboardView.vue"
Cohesion: 0.12
Nodes (13): DashboardView(), demoDataLabel, googleLoginEnabled, auth, AVAILABILITY_LABEL, binTypes, loadingMachines, machineDotClass() (+5 more)

### Community 134 - "voucher.js"
Cohesion: 0.39
Nodes (6): loadImage(), voucherImageDataUrl(), voucherLink(), voucherQrDataUrl(), wrapLines(), qrcode

### Community 136 - "FrontEnd/vite.config.js"
Cohesion: 0.33
Nodes (3): AI_SERVICE_API_KEY, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue

### Community 137 - "IdleOverlay.vue"
Cohesion: 0.50
Nodes (3): progress, props, seconds

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

### Community 142 - "downloadExcel"
Cohesion: 0.52
Nodes (5): cleanParams(), downloadExcel(), excelFilename(), exportDetectionLogsExcel(), exportTable()

### Community 144 - "awardPoints"
Cohesion: 0.43
Nodes (6): isNetworkError(), newIdempotencyKey(), retryOnce(), saveUserItem(), awardPoints(), randomPointsFallback()

### Community 145 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, autoprefixer, postcss, tailwindcss, vite, @vitejs/plugin-basic-ssl, @vitejs/plugin-vue, vitest

### Community 146 - "vercel.json"
Cohesion: 0.50
Nodes (3): framework, rewrites, $schema

### Community 154 - "normalizeMachine"
Cohesion: 0.31
Nodes (5): BIN_LEVEL_KEYS, normalizeMachine(), validateMachineName(), addMachine(), saveMachine()

### Community 164 - "useLanguage.js"
Cohesion: 0.21
Nodes (13): numberFormatter, fmt(), applyLanguage(), browserStorage(), CODES, htmlLang(), INTL, intlLocale() (+5 more)

### Community 169 - "RVM Web Application: Test Plan Results (T01–T09) and Issue Register"
Cohesion: 0.40
Nodes (4): Issue Register (§8), Not covered by this run, Results, RVM Web Application: Test Plan Results (T01–T09) and Issue Register

### Community 173 - "AdminController.php"
Cohesion: 0.12
Nodes (7): SecurityLogTest, Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Storage

### Community 176 - "RvmMachine"
Cohesion: 0.09
Nodes (7): RvmMachine, DemoSessionMachineTest, DetectionLogTest, ExpireStaleSessionsTest, MachineDeleteGuardTest, PublicStatsTest, WeighAwardsConfiguredPointsTest

### Community 178 - "countMachinesInScope"
Cohesion: 0.43
Nodes (5): countMachinesInScope(), machinesInScope(), machines, scopedMachines, statsCards

### Community 190 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, build, dev, preview, test

## Knowledge Gaps
- **685 isolated node(s):** `name`, `type`, `description`, `keywords`, `license` (+680 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1091 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **47 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `CarbonService`, `TestCase`, `Illuminate\Http\Request`, `RewardItem`, `FormalReportService`, `AuthController`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `AdminCashRedeemSettingsTest`, `Transaction`, `AdminRewardConfigTest`, `AdminController.php`, `RvmMachine`, `Closure`, `AdminAuditLogTest`, `AdminCachingTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `UserDisplayNumberTest`, `EnforceIdleTimeoutTest`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `LastLoginTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `Controller`, `Illuminate\Support\Str`, `DemoDataGenerator`, `AdminCarbonStatsTest`, `AdminTransactionFiltersTest`, `PointsHistory`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `@phosphor-icons/vue`, `rvm.js`, `AdminView.vue`, `RvmSessionView.vue`, `DashboardView.vue`, `UserSettingsView.vue`, `IdleOverlay.vue`, `ScanView.vue`, `RegisterView.vue`, `SessionDetailModal.vue`, `LoginView.vue`, `ActivityView.vue`, `router/index.js`, `RewardsView.vue`, `LandingView.vue`, `SessionSummaryView.vue`, `useLanguage.js`, `KioskQrView.vue`, `auth.js`, `VoucherView.vue`, `DemoKioskSessionView.vue`, `ActiveSessionSheet.vue`, `App.vue`, `AdminPagination.vue`, `ImageCropper.vue`, `FrontEnd/package.json`, `useKioskSessionGuard.js`, `MaintenanceHistoryModal.vue`, `LanguageSwitch.vue`, `UserAvatar.vue`, `VoucherModal.vue`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `CarbonService`, `RewardItem`, `FormalReportService`, `User`, `AdminBinCollectionEmailTest`, `AdminPaginationTest`, `SessionStatusAndGraceTest`, `DetectionLog`, `AdminCashRedeemSettingsTest`, `Transaction`, `AdminRewardConfigTest`, `GoogleRedirectFrontendOriginTest`, `AdminController.php`, `RvmMachine`, `AdminAuditLogTest`, `AdminCachingTest`, `MaintenanceRecordsTest`, `OneMachineRuleTest`, `Test Application Bootstrap`, `UserDisplayNumberTest`, `EnforceIdleTimeoutTest`, `AdminLog`, `AdminKioskMaintenanceTest`, `AuthVerificationGateTest`, `DemoFlagTest`, `AdminCompactorSettingsTest`, `LastLoginTest.php`, `MachineHeartbeatTest`, `AccountSuspensionTest`, `CashRedeemTest`, `DemoDataGenerator`, `AdminCarbonStatsTest`, `AdminTransactionFiltersTest`, `PointsHistory`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `RvmMachine` (e.g. with `.booted()` and `.booted()`) actually correct?**
  _`RvmMachine` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _685 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AdminView.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.010525741710296684 - nodes in this community are weakly interconnected._
- **Should `BinCollectionRequested` be split into smaller, more focused modules?**
  _Cohesion score 0.12258064516129032 - nodes in this community are weakly interconnected._