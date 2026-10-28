<?php
// BackEnd/app/Models/RewardItemImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardItemImage extends Model
{
    protected $fillable = ['reward_item_id', 'mime', 'data'];

    protected $hidden = ['data'];
}
