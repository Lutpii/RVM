<?php
// app/Models/RvmMachine.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RvmMachine extends Model {
    protected $fillable = ['machine_code','name','location_name','latitude','longitude','status','aluminum_level','plastic_level','glass_level','paper_level'];
    public function sessions()     { return $this->hasMany(RecyclingSession::class, 'machine_id'); }
    public function transactions() { return $this->hasMany(Transaction::class, 'machine_id'); }
    public function qrSessions()   { return $this->hasMany(QrSession::class, 'machine_id'); }
}
