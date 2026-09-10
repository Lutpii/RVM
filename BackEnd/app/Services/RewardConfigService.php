<?php
// BackEnd/app/Services/RewardConfigService.php
namespace App\Services;

class RewardConfigService
{
    private const DEFAULTS = [
        'plastic'  => 5,
        'aluminum' => 8,
        'glass'    => 5,
        'paper'    => 3,
    ];

    public function load(): array
    {
        $path = storage_path('app/reward_config.json');
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            if ($decoded && is_array($decoded)) return $decoded;
        }
        return self::DEFAULTS;
    }

    public function save(array $config): void
    {
        file_put_contents(storage_path('app/reward_config.json'), json_encode($config));
    }
}
