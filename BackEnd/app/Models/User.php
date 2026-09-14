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

    // recycling_sessions.user_id / transactions.user_id are NOT NULL foreign
    // keys, so a "Continue as Guest" kiosk session (no login, no real
    // account) is attributed to this single shared placeholder account
    // instead of requiring a schema change to make those columns nullable.
    // Its total_points/is_verified/etc. are never actually used by anything
    // (no one logs into it), it just exists so guest activity has a real,
    // queryable row in recycling_sessions/transactions and therefore counts
    // in the admin dashboard's existing Transaction-based stats without
    // those queries needing to change at all.
    const GUEST_EMAIL = 'guest@rvm.system';

    public static function guest(): self
    {
        return static::firstOrCreate(
            ['email' => self::GUEST_EMAIL],
            // total_points explicit (not just relying on the column's DB
            // default) - firstOrCreate's insert path doesn't re-fetch DB-side
            // defaults into the in-memory model, so without this the very
            // first call in a fresh install returns an instance with
            // total_points still null, which then fails recycling_sessions'
            // NOT NULL start_points/end_points on the first guest session.
            ['name' => 'Guest', 'role' => 'user', 'is_verified' => 1, 'total_points' => 0]
        );
    }
}
