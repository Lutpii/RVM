<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('platform.enabled')) {
            return response()->json(['success' => false], 404);
        }

        return $next($request);
    }
}
