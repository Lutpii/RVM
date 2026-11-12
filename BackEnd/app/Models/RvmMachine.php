<?php
// app/Models/RvmMachine.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RvmMachine extends Model {
    protected $fillable = ['machine_code','name','location_name','latitude','longitude','status','aluminum_level','plastic_level','glass_level','paper_level','is_demo'];
    // The key hash identifies the machine; it must never leave the server.
    protected $hidden = ['api_key_hash'];
    protected $casts = [
        'last_seen_at' => 'datetime',
        'fault_active' => 'boolean',
        'fault_since'  => 'datetime',
        'is_demo'      => 'boolean',
    ];
    public function sessions()     { return $this->hasMany(RecyclingSession::class, 'machine_id'); }
    public function transactions() { return $this->hasMany(Transaction::class, 'machine_id'); }
    public function qrSessions()   { return $this->hasMany(QrSession::class, 'machine_id'); }
}
