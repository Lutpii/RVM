<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class EnforceIdleTimeout
{
    // Must run BEFORE auth:sanctum (see $middlewarePriority in Kernel.php):
    // Sanctum's own guard bumps last_used_at to now() as part of authenticating
    // the request, so checking it after auth:sanctum has already run would
    // always see "just now" and never detect idleness.
    private const IDLE_MINUTES = 30;

    public function handle(Request $request, Closure $next)
    {
        // A kiosk_token request authenticates via a TransientToken (no row in
        // personal_access_tokens, no last_used_at to check) and is a physical
        // machine session, not the kind of "left logged in" session this guards.
        if ($request->attributes->get('via_kiosk_token')) {
            return $next($request);
        }

        $bearerToken = $request->bearerToken();
        if (! $bearerToken) {
            return $next($request);
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);
        if (! $accessToken) {
            return $next($request);
        }

        $lastActivity = $accessToken->last_used_at ?? $accessToken->created_at;
        if ($lastActivity->lt(Carbon::now()->subMinutes(self::IDLE_MINUTES))) {
            $accessToken->delete();

            return response()->json([
                'success' => false,
                'message' => __('messages.session_expired_idle'),
            ], 401);
        }

        return $next($request);
    }
}
