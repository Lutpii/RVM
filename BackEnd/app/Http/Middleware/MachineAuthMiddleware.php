<?php

namespace App\Http\Middleware;

use App\Models\RvmMachine;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Identifies the physical machine behind a /hardware/* call from its
 * X-Machine-Key header (stored only as a SHA-256 hash). A recognised call
 * also counts as a heartbeat (last_seen_at).
 *
 *   machine.auth           401 only when config('rvm.machine_auth_enforce') is on
 *   machine.auth:required  always 401 without a valid key
 */
class MachineAuthMiddleware
{
    public function handle(Request $request, Closure $next, ?string $mode = null)
    {
        $key = (string) $request->header('X-Machine-Key', '');
        $machine = $key !== ''
            ? RvmMachine::where('api_key_hash', hash('sha256', $key))->first()
            : null;

        if ($machine) {
            $machine->forceFill(['last_seen_at' => now()])->saveQuietly();
            $request->attributes->set('machine', $machine);
            return $next($request);
        }

        $required = $mode === 'required' || config('rvm.machine_auth_enforce');
        if ($required) {
            return response()->json(['success' => false, 'message' => 'Unknown machine.'], 401);
        }

        Log::warning('[MachineAuth] hardware call without a valid machine key', ['path' => $request->path()]);
        return $next($request);
    }
}
