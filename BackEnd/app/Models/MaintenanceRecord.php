<?php
// app/Models/MaintenanceRecord.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model {
    protected $table    = 'machine_maintenance_records';
    protected $fillable = ['machine_id', 'technician_name', 'issue', 'action_taken', 'status', 'opened_by', 'closed_by', 'closed_at'];
    protected $casts    = ['closed_at' => 'datetime'];

    public function machine()  { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
    public function opener()   { return $this->belongsTo(User::class, 'opened_by'); }
    public function closer()   { return $this->belongsTo(User::class, 'closed_by'); }

    /** Shape returned by the admin API. */
    public function toApi(): array
    {
        return [
            'id'              => $this->id,
            'machine_id'      => $this->machine_id,
            'technician_name' => $this->technician_name,
            'issue'           => $this->issue,
            'action_taken'    => $this->action_taken,
            'status'          => $this->status,
            'opened_by_name'  => $this->opener?->name,
            'closed_by_name'  => $this->closer?->name,
            'opened_at'       => $this->created_at?->toIso8601String(),
            'closed_at'       => $this->closed_at?->toIso8601String(),
        ];
    }
}
