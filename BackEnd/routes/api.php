<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\RewardController;

/*
|--------------------------------------------------------------------------
| API Routes - Reverse Vending Machine
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    // Just hands back this server's real absolute URL for /auth/google/start
    // (see AuthController::googleRedirect docblock) — sets no cookie itself,
    // so no special middleware needed here.
    Route::get('/google/redirect', [AuthController::class, 'googleRedirect']);
    Route::post('/google/exchange', [AuthController::class, 'googleExchange']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// QR public endpoint (for RVM machine display)
Route::get('/qr/generate/{machineCode}', [QrController::class, 'generate']);
Route::get('/qr/status/{token}', [QrController::class, 'status'])->middleware('throttle:qr-status');

// Machine public info
Route::get('/machines', [MachineController::class, 'index']);
Route::get('/machines/{id}', [MachineController::class, 'show']);

// Hardware-only actions (camera capture, AI classify, servo sort) — no login
// required, used by the kiosk's "Continue as Guest" flow so the physical
// machine still works for guests. No points/session records are created here;
// /hardware/classify does write one detection_logs row per classification, for
// exhibition accuracy review.
Route::post('/hardware/capture', [TransactionController::class, 'hardwareCapture']);
Route::post('/hardware/classify', [TransactionController::class, 'hardwareClassify']);
Route::post('/hardware/sort', [TransactionController::class, 'hardwareSort']);

// Guest recycling_sessions/transactions rows, tied to the shared
// App\Models\User::guest() placeholder account so guest activity shows up in
// the admin dashboard's existing Transaction-based stats/charts alongside
// real logged-in sessions, without those queries needing to change.
Route::post('/hardware/session/start', [TransactionController::class, 'guestSessionStart']);
Route::post('/hardware/session/complete', [TransactionController::class, 'guestSessionComplete']);
Route::post('/hardware/session/end', [TransactionController::class, 'guestSessionEnd']);

// Protected routes (require auth or valid kiosk token)
Route::middleware(['kiosk.auth', 'idle.timeout', 'auth:sanctum'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // QR scan (user scans RVM QR code)
    Route::post('/qr/scan', [QrController::class, 'scan']);

    // User profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    Route::delete('/user/account', [UserController::class, 'deleteAccount']);
    Route::get('/user/points-history', [UserController::class, 'pointsHistory']);
    Route::get('/user/sessions', [UserController::class, 'sessions']);
    Route::get('/user/reward-items', [RewardController::class, 'index']);
    Route::post('/user/reward-items/{id}/redeem', [RewardController::class, 'redeem']);
    // Named /user/reward-redemptions, not /user/redemptions: that path is already
    // used by UserSettingsView.vue's unrelated (currently dead) points→cash
    // redemption feature, which expects a plain array response.
    Route::get('/user/reward-redemptions', [RewardController::class, 'history']);

    // Recycling Sessions
    Route::post('/sessions/start', [SessionController::class, 'start']);
    Route::get('/sessions/{sessionCode}', [SessionController::class, 'show']);
    Route::post('/sessions/{sessionCode}/end', [SessionController::class, 'end']);
    Route::get('/sessions/{sessionCode}/summary', [SessionController::class, 'summary']);

    // Transactions (item processing steps)
    Route::post('/transactions/check-bin', [TransactionController::class, 'checkBin']);
    Route::post('/transactions/open-lid', [TransactionController::class, 'openLid']);
    Route::post('/transactions/insert-item', [TransactionController::class, 'insertItem']);
    Route::post('/transactions/process-conveyor', [TransactionController::class, 'processConveyor']);
    Route::post('/transactions/capture-image', [TransactionController::class, 'captureImage']);
    Route::post('/transactions/classify', [TransactionController::class, 'classify']);
    Route::post('/transactions/weigh', [TransactionController::class, 'weigh']);
    Route::post('/transactions/complete', [TransactionController::class, 'complete']);
    Route::post('/transactions/reject', [TransactionController::class, 'reject']);

    // Admin only routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/machines', [AdminController::class, 'machines']);
        Route::post('/machines', [AdminController::class, 'createMachine']);
        Route::put('/machines/{id}', [AdminController::class, 'updateMachine']);
        Route::delete('/machines/{id}', [AdminController::class, 'deleteMachine']);
        Route::put('/machines/{id}/bin-levels', [AdminController::class, 'updateBinLevels']);
        Route::post('/machines/{id}/maintenance', [AdminController::class, 'maintainMachine']);
        Route::get('/sessions', [AdminController::class, 'allSessions']);
        Route::get('/transactions', [AdminController::class, 'allTransactions']);
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/logs', [AdminController::class, 'logs']);
        Route::get('/reward-config', [AdminController::class, 'getRewardConfig']);
        Route::put('/reward-config', [AdminController::class, 'updateRewardConfig']);
        Route::get('/reward-items', [AdminController::class, 'rewardItems']);
        Route::post('/reward-items', [AdminController::class, 'createRewardItem']);
        Route::put('/reward-items/{id}', [AdminController::class, 'updateRewardItem']);
        Route::delete('/reward-items/{id}', [AdminController::class, 'deleteRewardItem']);
        Route::get('/redemptions', [AdminController::class, 'redemptions']);
        Route::post('/request-bin-collection', [AdminController::class, 'requestBinCollection']);
        Route::get('/export-excel', [AdminController::class, 'exportExcel']);
        Route::post('/export-excel/email', [AdminController::class, 'emailExcelReport']);
        Route::get('/chart-data', [AdminController::class, 'chartData']);
        // Own throttle (see RouteServiceProvider) — the Detection Review tab
        // fires one image request per gallery row, which the generic 60/min
        // 'api' limit would 429 halfway through a single page. Unlike the
        // qr-status route this *loosens* the cap, so the api middleware group's
        // throttle:api has to come off too or it would still bind at 60.
        Route::middleware('throttle:detection-review')->withoutMiddleware('throttle:api')->group(function () {
            Route::get('/detection-logs', [AdminController::class, 'detectionLogs']);
            Route::patch('/detection-logs/{id}', [AdminController::class, 'reviewDetectionLog']);
            Route::get('/detection-logs/{id}/image', [AdminController::class, 'detectionLogImage']);
            Route::get('/detection-logs/export', [AdminController::class, 'exportDetectionLogs']);
        });
    });
});
