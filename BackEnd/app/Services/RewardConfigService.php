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

    public function path(): string
    {
        // ?: not the config() default arg: that default only applies when the key is
        // absent, not when config/rewards.php loaded but resolved it to '' or null
        // (e.g. a stale config:cache from before this file existed) — which would
        // silently fall back to the real filename this isolation exists to avoid.
        return storage_path('app/' . (config('rewards.config_filename') ?: 'reward_config.json'));
    }

    public function load(): array
    {
        $path = $this->path();
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            if ($decoded && is_array($decoded)) return $decoded;
        }
        return self::DEFAULTS;
    }

    public function save(array $config): void
    {
        file_put_contents($this->path(), json_encode($config));
    }
}
