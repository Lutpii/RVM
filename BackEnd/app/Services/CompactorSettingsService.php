<?php
// BackEnd/app/Services/CompactorSettingsService.php
namespace App\Services;

// 2-bin DSME machine: how many items of each material one session may put in,
// which is also how many the compactor chamber takes before it is emptied.
// Set by the admin (Machines > Compactor Settings); the kiosk reads it from
// /hardware/state and the machine gets it with every /hardware/deposit.
class CompactorSettingsService
{
    public const MATERIALS = ['plastic', 'aluminum'];
    public const MIN = 1;
    public const MAX = 10;

    private const DEFAULTS = ['plastic' => 3, 'aluminum' => 3];

    public function path(): string
    {
        return storage_path('app/' . (config('rewards.compactor_settings_filename') ?: 'compactor_settings.json'));
    }

    public function load(): array
    {
        $settings = self::DEFAULTS;
        $path = $this->path();
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            foreach (self::MATERIALS as $material) {
                $value = is_array($decoded) ? ($decoded[$material] ?? null) : null;
                if (is_int($value) && $value >= self::MIN && $value <= self::MAX) {
                    $settings[$material] = $value;
                }
            }
        }
        return $settings;
    }

    public function save(array $settings): void
    {
        $clean = [];
        foreach (self::MATERIALS as $material) {
            $clean[$material] = (int) $settings[$material];
        }
        file_put_contents($this->path(), json_encode($clean));
    }

    // Max for one material, or null for a material without a bin (the machine
    // refuses those anyway).
    public function capacityFor(string $material): ?int
    {
        return $this->load()[$material] ?? null;
    }
}
