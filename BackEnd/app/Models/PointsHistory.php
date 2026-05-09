<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PointsHistory extends Model {
    protected $table    = 'points_history';
    protected $fillable = ['user_id','transaction_id','session_id','points_change','balance_after','type','description'];
    public function user() { return $this->belongsTo(User::class); }
}
