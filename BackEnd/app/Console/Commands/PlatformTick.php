<?php

namespace App\Console\Commands;

use App\Services\PlatformSimulation;
use Illuminate\Console\Command;

class PlatformTick extends Command
{
    protected $signature = 'platform:tick';
    protected $description = 'Add scheduled simulated Compactor and Composter activity';

    public function handle(PlatformSimulation $simulation): int
    {
        if (!config('platform.enabled')) {
            return self::SUCCESS;
        }

        $counts = $simulation->tick(now());
        $this->info("Added {$counts['compactor_batches']} simulated batches and {$counts['composter_deposits']} simulated deposits.");

        return self::SUCCESS;
    }
}
