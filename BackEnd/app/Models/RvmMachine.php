<?php
// app/Models/RvmMachine.php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RvmMachine extends Model {
    protected $fillable = ['machine_type','location_id','machine_code','name','location_name','latitude','longitude','status','aluminum_level','plastic_level','glass_level','paper_level','is_demo'];
    public const TYPES = ['rvm', 'compactor', 'composter'];

    // Show platform fields only in responses that explicitly opt in.
    protected $hidden = ['api_key_hash', 'machine_type', 'location_id'];
    protected $casts = [
        'last_seen_at' => 'datetime',
        'fault_active' => 'boolean',
        'fault_since'  => 'datetime',
        'is_demo'      => 'boolean',
    ];

    public function scopeRvmOnly(Builder $query): Builder
    {
        return $query->ofType('rvm');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('rvm_machines.machine_type', $type);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function sessions()     { return $this->hasMany(RecyclingSession::class, 'machine_id'); }
    public function transactions() { return $this->hasMany(Transaction::class, 'machine_id'); }
    public function qrSessions()   { return $this->hasMany(QrSession::class, 'machine_id'); }
}
