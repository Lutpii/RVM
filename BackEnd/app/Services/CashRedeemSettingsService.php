<?php
// BackEnd/app/Services/CashRedeemSettingsService.php
namespace App\Services;

class CashRedeemSettingsService
{
    private const DEFAULTS = [
        'points_per_unit' => 100,
        'rm_per_unit'     => 0.10,
        'min_points'      => 500,
    ];

    public function path(): string
    {
        return storage_path('app/' . (config('rewards.cash_redeem_settings_filename') ?: 'cash_redeem_settings.json'));
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

    public function save(array $settings): void
    {
        file_put_contents($this->path(), json_encode($settings));
    }
}
