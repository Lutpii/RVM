<?php
// app/Models/User.php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasApiTokens, Notifiable;
    protected $fillable = ['name','email','phone','password_hash','google_id','avatar_url','total_points','role','is_verified','otp_code','otp_expires_at','preferred_language','theme_preference'];
    protected $hidden   = ['password_hash','otp_code'];
    public function recyclingSessions() { return $this->hasMany(RecyclingSession::class); }
    public function transactions()      { return $this->hasMany(Transaction::class); }
    public function pointsHistory()     { return $this->hasMany(PointsHistory::class); }
}
