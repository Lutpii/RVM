<?php
// BackEnd/app/Models/RewardRedemption.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $fillable = [
        'user_id', 'reward_item_id', 'reward_name', 'points_spent',
        'cash_amount_rm', 'ewallet_provider', 'ewallet_account',
        'voucher_code', 'used_at', 'used_by',
    ];

    protected $casts = ['cash_amount_rm' => 'float', 'used_at' => 'datetime'];

    public function user()      { return $this->belongsTo(User::class); }
    public function rewardItem(){ return $this->belongsTo(RewardItem::class); }
}
