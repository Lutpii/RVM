<?php
// BackEnd/app/Models/RewardItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardItem extends Model
{
    protected $fillable = [
        'name', 'description', 'category', 'image_path',
        'points_cost', 'stock', 'valid_from', 'valid_until', 'is_active',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active'  => 'boolean',
    ];

    protected $appends = ['image_url'];

    // A relative path (not Storage::url(), which prepends config('filesystems.disks.public.url')
    // i.e. APP_URL) so this works regardless of APP_URL drift between environments (this project's
    // dev setup serves the Vue frontend and Laravel backend on different origins/ports, proxied —
    // see vite.config.js's /storage proxy entry) and avoids mixed-content blocks on the HTTPS dev server.
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? '/storage/' . $this->image_path : null;
    }

    public function redemptions() { return $this->hasMany(RewardRedemption::class); }

    public function isAvailable(): bool
    {
        if (!$this->is_active) return false;

        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        if ($this->stock !== null && $this->stock <= 0) return false;

        return true;
    }
}
