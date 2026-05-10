<?php

namespace App\Http\Middleware;

use App\Models\QrSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\TransientToken;

class KioskAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $kioskToken = $request->header('X-Kiosk-Token');

        Log::info('[KioskAuth] path=' . $request->path() . ' token=' . ($kioskToken ? substr($kioskToken, 0, 8) . '...' : 'NONE'));

        if ($kioskToken) {
            $qrSession = QrSession::with('scannedUser')
                ->where('kiosk_token', $kioskToken)
                ->whereNotNull('scanned_by')
                ->first();

            Log::info('[KioskAuth] session_found=' . ($qrSession ? 'YES id=' . $qrSession->id : 'NO') . ' user=' . ($qrSession?->scannedUser?->name ?? 'none'));

            if ($qrSession && $qrSession->scannedUser) {
                $user = $qrSession->scannedUser;
                $user->withAccessToken(new TransientToken);
                // Set user on both the sanctum guard and the request resolver
                Auth::guard('sanctum')->setUser($user);
                Auth::setUser($user);
                $request->setUserResolver(fn() => $user);
            }
        }

        return $next($request);
    }
}
