<?php

namespace App\Http\Middleware;

use App\Models\QrSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\TransientToken;

class KioskAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $kioskToken = $request->header('X-Kiosk-Token');

        if ($kioskToken) {
            $qrSession = QrSession::with('scannedUser')
                ->where('kiosk_token', $kioskToken)
                ->where('status', 'scanned')
                ->first();

            if ($qrSession && $qrSession->scannedUser) {
                $user = $qrSession->scannedUser;
                $user->withAccessToken(new TransientToken);
                Auth::guard('sanctum')->setUser($user);
            }
        }

        return $next($request);
    }
}
