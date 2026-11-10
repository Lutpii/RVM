<?php
// app/Support/SecurityLog.php
namespace App\Support;

use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Security-relevant events (Assessment §6): authentication outcomes and
 * machine fault changes. Never logs passwords, OTPs or tokens; identifiers
 * are masked.
 */
class SecurityLog
{
    public static function auth(string $event, ?User $user, ?string $identifier, Request $request): void
    {
        Log::info("[Auth] {$event}", array_filter([
            'user_id'    => $user?->id,
            'identifier' => $identifier !== null && $identifier !== '' ? self::mask($identifier) : null,
            'ip'         => $request->ip(),
        ], fn ($v) => $v !== null));
    }

    public static function machineFault(RvmMachine $machine, bool $raised, ?string $reason): void
    {
        Log::warning('[Machine] ' . ($raised ? 'fault_raised' : 'fault_cleared'), array_filter([
            'machine_code' => $machine->machine_code,
            'reason'       => $reason,
        ], fn ($v) => $v !== null));
    }

    /** "user@test.local" -> "us***@test.local", "0123456789" -> "*******789". */
    public static function mask(string $identifier): string
    {
        if (str_contains($identifier, '@')) {
            [$local, $domain] = explode('@', $identifier, 2);
            return mb_substr($local, 0, min(2, mb_strlen($local))) . '***@' . $domain;
        }
        $len = mb_strlen($identifier);
        return $len <= 3 ? str_repeat('*', $len) : str_repeat('*', $len - 3) . mb_substr($identifier, -3);
    }
}
