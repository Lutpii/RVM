<?php
// BackEnd/app/Console/Commands/DemoData.php
namespace App\Console\Commands;

use App\Services\DemoDataGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DemoData extends Command
{
    protected $signature = 'rvm:demo-data {--days=30} {--seed=} {--purge}';
    protected $description = 'Generate (or --purge) realistic demo data for presentations';

    public function handle(DemoDataGenerator $generator): int
    {
        $this->info('Target DB: ' . config('database.connections.mysql.host') . '/' . config('database.connections.mysql.database'));

        if ($this->option('purge')) {
            $summary = $generator->purge();
        } else {
            $seed = $this->option('seed');
            $summary = $generator->generate((int) $this->option('days'), now(), $seed === null ? null : (int) $seed);
            if ($summary['admin_logs'] === 0) {
                $this->warn('No admin logs written (no admin user exists, or the days were already filled).');
            }
        }

        $this->table(['key', 'value'], collect($summary)->map(fn ($v, $k) => [$k, is_bool($v) ? ($v ? 'yes' : 'no') : $v])->values()->all());

        foreach (['all', 'real', 'demo'] as $scope) {
            Cache::forget("admin:dashboard:stats:{$scope}");
            Cache::forget("admin:dashboard:chart-data:{$scope}");
        }

        return self::SUCCESS;
    }
}
