<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Google OAuth — both must be 'web' routes reached by real browser navigation
// (not the frontend's proxied API client): googleStart() sets a cookie that
// googleCallback() later reads, and both only see it if the browser talks to
// this exact host both times, same as any 'web'-group cookie.
Route::get('/auth/google/start', [AuthController::class, 'googleStart']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
