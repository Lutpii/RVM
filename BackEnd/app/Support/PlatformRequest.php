<?php

namespace App\Support;

use Illuminate\Http\Request;

// One place that decides whether a request wants platform data (compactor and
// composter CO2, history, etc.). The platform frontend sends `X-Platform: 1` on
// every call (see FrontEnd/src/services/api.js); `?platform=1` is also accepted.
// An OFF frontend sends neither, so it keeps the legacy Ultra-RVM-only numbers
// even when the backend runs with PLATFORM_MODE=true.
class PlatformRequest
{
    public static function wants(?Request $request = null): bool
    {
        $request ??= request();

        return (bool) config('platform.enabled')
            && ($request->header('X-Platform') === '1' || $request->boolean('platform'));
    }
}
