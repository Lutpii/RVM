<?php
// BackEnd/app/Http/Controllers/RewardController.php
namespace App\Http\Controllers;

use App\Models\RewardItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class RewardController extends Controller
{
    public function index(): JsonResponse
    {
        $items = RewardItem::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->orderBy('points_cost')
            ->get();

        return response()->json(['success' => true, 'reward_items' => $items->map(fn (RewardItem $item) => [
            'id'           => $item->id,
            'name'         => $item->name,
            'description'  => $item->description,
            'category'     => $item->category,
            'image_url'    => $item->image_path ? Storage::url($item->image_path) : null,
            'points_cost'  => $item->points_cost,
            'stock'        => $item->stock,
            'valid_from'   => $item->valid_from,
            'valid_until'  => $item->valid_until,
            'is_available' => $item->isAvailable(),
        ])]);
    }
}
