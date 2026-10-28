<?php
// BackEnd/app/Models/RewardItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardItem extends Model
{
    protected $fillable = [
        'name', 'description', 'category', 'image_path', 'image_stored_at',
        'points_cost', 'stock', 'valid_from', 'valid_until', 'is_active',
    ];

    protected $hidden = ['image_stored_at'];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active'  => 'boolean',
        'image_stored_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    // A relative path (not Storage::url(), which prepends config('filesystems.disks.public.url')
    // i.e. APP_URL) so this works regardless of APP_URL drift between environments (this project's
    // dev setup serves the Vue frontend and Laravel backend on different origins/ports, proxied —
    // see vite.config.js's /storage proxy entry) and avoids mixed-content blocks on the HTTPS dev server.
    //
    // A database image (see RewardItemImage) wins over a legacy on-disk
    // image_path; ?v= changes on every new upload so the long-cached image
    // response is never stale.
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_stored_at) {
            return "/api/reward-items/{$this->id}/image?v={$this->image_stored_at->timestamp}";
        }
        return $this->image_path ? '/storage/' . $this->image_path : null;
    }

    public function redemptions() { return $this->hasMany(RewardRedemption::class); }
    public function image()       { return $this->hasOne(RewardItemImage::class); }

    // Saves an uploaded photo into the database and drops any legacy disk
    // file it replaces (only after the new one is stored).
    public function storeImage(\Illuminate\Http\UploadedFile $file): void
    {
        $oldPath = $this->image_path;

        RewardItemImage::updateOrCreate(
            ['reward_item_id' => $this->id],
            ['mime' => $file->getMimeType() ?: 'image/jpeg', 'data' => $file->get()],
        );
        $this->update(['image_stored_at' => now(), 'image_path' => null]);

        if ($oldPath) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }
    }

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
