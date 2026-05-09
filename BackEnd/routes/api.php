<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\QrController;

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
    Route::get('/google/redirect', [AuthController::class, 'googleRedirect']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// QR public endpoint (for RVM machine display)
Route::get('/qr/generate/{machineCode}', [QrController::class, 'generate']);
Route::get('/qr/status/{token}', [QrController::class, 'status']);

// Machine public info
Route::get('/machines', [MachineController::class, 'index']);
Route::get('/machines/{id}', [MachineController::class, 'show']);

// Protected routes (require auth or valid kiosk token)
Route::middleware(['kiosk.auth', 'auth:sanctum'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // QR scan (user scans RVM QR code)
    Route::post('/qr/scan', [QrController::class, 'scan']);

    // User profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::get('/user/points-history', [UserController::class, 'pointsHistory']);
    Route::get('/user/sessions', [UserController::class, 'sessions']);

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
        Route::get('/sessions', [AdminController::class, 'allSessions']);
        Route::get('/transactions', [AdminController::class, 'allTransactions']);
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/logs', [AdminController::class, 'logs']);
    });
});
