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

    // The reward name was saved in Malay when the voucher was redeemed; the
    // English / Indonesian versions come from the reward card (null when the
    // card is gone or has no translation, and the page then shows Malay).
    public function rewardNameTranslations(): array
    {
        return [
            'reward_name_en' => $this->rewardItem?->name_en,
            'reward_name_id' => $this->rewardItem?->name_id,
        ];
    }
}
