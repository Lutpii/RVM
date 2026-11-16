<?php
// BackEnd/app/Services/RewardConfigService.php
namespace App\Services;

class RewardConfigService
{
    // Fallback per item when a material has no configured amount (100 points = RM 1).
    public const POINTS_MIN = 15;
    public const POINTS_MAX = 20;

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

    // Points for one item of $material: the admin-configured amount, or a random
    // 15-20 when that material is not configured. Shared by Ultra RVM (weigh,
    // guest sessions) and the platform compactor so both always award the same.
    public function pointsFor(string $material, ?array $config = null): int
    {
        $config ??= $this->load();
        $value = $config[$material] ?? null;
        return is_numeric($value) ? (int) $value : rand(self::POINTS_MIN, self::POINTS_MAX);
    }

    public function save(array $config): void
    {
        file_put_contents($this->path(), json_encode($config));
    }
}
