<?php
// BackEnd/app/Http/Controllers/RewardController.php
namespace App\Http\Controllers;

use App\Models\PointsHistory;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'image_url'    => $item->image_url,
            'points_cost'  => $item->points_cost,
            'stock'        => $item->stock,
            'valid_from'   => $item->valid_from,
            'valid_until'  => $item->valid_until,
            'is_available' => $item->isAvailable(),
        ])]);
    }

    public function redeem(Request $request, int $id): JsonResponse
    {
        // A kiosk_token proves "this device is near a scanned session," not "this is
        // the account holder acting with full intent" (see KioskAuthMiddleware) — never
        // let it spend the scanned user's points, even though it's fine for reading the
        // catalog/history on this same route group.
        if ($request->attributes->get('via_kiosk_token')) {
            return response()->json(['success' => false, 'message' => 'Redemption is only available from your own account, not a shared kiosk.'], 403);
        }

        return DB::transaction(function () use ($request, $id) {
            $item = RewardItem::where('id', $id)->lockForUpdate()->first();
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Reward not found.'], 404);
            }

            if (!$item->isAvailable()) {
                $reason = match (true) {
                    !$item->is_active => 'This reward is no longer active.',
                    $item->valid_until && now()->gt($item->valid_until) => 'This reward has expired.',
                    $item->valid_from && now()->lt($item->valid_from) => 'This reward is not available yet.',
                    $item->stock !== null && $item->stock <= 0 => 'This reward is sold out.',
                    default => 'This reward is not available.',
                };
                return response()->json(['success' => false, 'message' => $reason], 422);
            }

            $user = \App\Models\User::where('id', $request->user()->id)->lockForUpdate()->first();
            if ($user->total_points < $item->points_cost) {
                return response()->json(['success' => false, 'message' => 'Insufficient points.'], 422);
            }

            if ($item->stock !== null) {
                $item->decrement('stock');
            }
            $user->decrement('total_points', $item->points_cost);

            $redemption = RewardRedemption::create([
                'user_id'        => $user->id,
                'reward_item_id' => $item->id,
                'reward_name'    => $item->name,
                'points_spent'   => $item->points_cost,
            ]);

            PointsHistory::create([
                'user_id'       => $user->id,
                'points_change' => -$item->points_cost,
                // decrement() already updates the in-memory attribute — no need
                // for a fresh() round-trip to read back what was just written.
                'balance_after' => $user->total_points,
                'type'          => 'redeemed',
                'description'   => "Redeemed: {$item->name}",
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Reward redeemed.',
                'total_points' => $user->total_points,
                'redemption'   => $redemption,
            ]);
        });
    }

    public function history(Request $request): JsonResponse
    {
        $redemptions = RewardRedemption::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'redemptions' => $redemptions]);
    }
}
