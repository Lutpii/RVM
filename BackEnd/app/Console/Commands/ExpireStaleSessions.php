<?php

namespace App\Console\Commands;

use App\Models\RecyclingSession;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireStaleSessions extends Command
{
    protected $signature = 'sessions:expire-stale';

    protected $description = 'Mark recycling sessions abandoned for too long as expired';

    // updated_at already advances on every item processed (TransactionController
    // increments session totals per item), so it doubles as a last-activity
    // timestamp with no extra instrumentation needed.
    private const IDLE_MINUTES = 15;

    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes(self::IDLE_MINUTES);

        $expired = RecyclingSession::where('status', 'active')
            ->where('updated_at', '<', $cutoff)
            ->get();

        foreach ($expired as $session) {
            // Points already landed on the user in real time as each item was
            // processed (TransactionController::complete()) — an abandoned
            // session just needs closing out, not re-crediting anything.
            $session->update([
                'status'   => 'expired',
                'ended_at' => Carbon::now(),
            ]);
        }

        $this->info("Expired {$expired->count()} stale session(s).");

        return self::SUCCESS;
    }
}
