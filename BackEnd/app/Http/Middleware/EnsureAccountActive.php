<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Refuses every protected request from a suspended account (A08), whichever
 * way its token was obtained (password, OTP, Google, kiosk QR).
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->account_status === 'suspended') {
            return response()->json([
                'success'           => false,
                'account_suspended' => true,
                'message'           => __('messages.account_suspended'),
            ], 403);
        }
        return $next($request);
    }
}
