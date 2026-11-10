<?php
// BackEnd/app/Console/Commands/MachineKey.php
namespace App\Console\Commands;

use App\Models\RvmMachine;
use Illuminate\Console\Command;

class MachineKey extends Command
{
    protected $signature = 'rvm:machine-key {machine_code}';
    protected $description = 'Generate (or replace) the API key a kiosk uses to identify its machine';

    public function handle(): int
    {
        $machine = RvmMachine::where('machine_code', $this->argument('machine_code'))->first();
        if (!$machine) {
            $this->error('Machine not found: ' . $this->argument('machine_code'));
            return self::FAILURE;
        }

        $key = bin2hex(random_bytes(32));
        $machine->forceFill(['api_key_hash' => hash('sha256', $key)])->saveQuietly();

        $this->info("Machine: {$machine->machine_code} ({$machine->name})");
        $this->line("Key: {$key}");
        $this->warn('Shown once. Put it in the kiosk build as VITE_MACHINE_KEY. Any previous key stops working.');
        return self::SUCCESS;
    }
}
