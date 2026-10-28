<?php
// BackEnd/app/Http/Controllers/RewardController.php
namespace App\Http\Controllers;

use App\Models\PointsHistory;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Services\CashRedeemSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    private const EWALLET_PROVIDERS = ["Touch 'n Go eWallet", 'GrabPay', 'Boost', 'ShopeePay'];

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

    public function rate(CashRedeemSettingsService $settings): JsonResponse
    {
        $s = $settings->load();
        return response()->json([
            'success'    => true,
            'rate'       => ['points' => $s['points_per_unit'], 'rm' => $s['rm_per_unit']],
            'min_points' => $s['min_points'],
        ]);
    }

    public function redeemCash(Request $request, CashRedeemSettingsService $settings): JsonResponse
    {
        // Same reasoning as redeem() above: a kiosk_token proves device proximity,
        // not the account holder's own intent to spend their points.
        if ($request->attributes->get('via_kiosk_token')) {
            return response()->json(['success' => false, 'message' => 'Redemption is only available from your own account, not a shared kiosk.'], 403);
        }

        $validated = $request->validate([
            'points'           => 'required|integer|min:1',
            'ewallet_provider' => 'required|string|in:' . implode(',', self::EWALLET_PROVIDERS),
            'ewallet_account'  => 'required|string|max:50',
        ]);

        $s = $settings->load();
        if ($validated['points'] < $s['min_points']) {
            return response()->json(['success' => false, 'message' => "Minimum redeem is {$s['min_points']} points."], 422);
        }
        if ($validated['points'] % $s['points_per_unit'] !== 0) {
            return response()->json(['success' => false, 'message' => "Points must be a multiple of {$s['points_per_unit']}."], 422);
        }

        return DB::transaction(function () use ($request, $validated, $s) {
            $user = \App\Models\User::where('id', $request->user()->id)->lockForUpdate()->first();
            if ($user->total_points < $validated['points']) {
                return response()->json(['success' => false, 'message' => 'Insufficient points.'], 422);
            }

            $rm = round(($validated['points'] / $s['points_per_unit']) * $s['rm_per_unit'], 2);

            $user->decrement('total_points', $validated['points']);

            RewardRedemption::create([
                'user_id'          => $user->id,
                'reward_item_id'   => null,
                'reward_name'      => 'Cash Redemption',
                'points_spent'     => $validated['points'],
                'cash_amount_rm'   => $rm,
                'ewallet_provider' => $validated['ewallet_provider'],
                'ewallet_account'  => $validated['ewallet_account'],
            ]);

            PointsHistory::create([
                'user_id'       => $user->id,
                'points_change' => -$validated['points'],
                'balance_after' => $user->total_points,
                'type'          => 'redeemed',
                'description'   => 'Redeemed: RM ' . number_format($rm, 2) . " to {$validated['ewallet_provider']}",
            ]);

            return response()->json([
                'success'          => true,
                'total_points'     => $user->total_points,
                'cash_amount_rm'   => $rm,
                'ewallet_provider' => $validated['ewallet_provider'],
                'message'          => 'RM ' . number_format($rm, 2) . " sent to your {$validated['ewallet_provider']} account.",
            ]);
        });
    }

    public function cashHistory(Request $request): JsonResponse
    {
        $redemptions = RewardRedemption::where('user_id', $request->user()->id)
            ->whereNull('reward_item_id')
            ->whereNotNull('ewallet_provider')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'redemptions' => $redemptions->map(fn (RewardRedemption $r) => [
            'id'          => $r->id,
            'points_used' => $r->points_spent,
            'amount'      => $r->cash_amount_rm,
            'status'      => 'completed',
            'created_at'  => $r->created_at,
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
                'voucher_code'   => $this->newVoucherCode(),
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

    // Public — the voucher QR links here, so whoever scans it (a shop, the
    // admin) can see whether it is still valid without logging in. Only the
    // holder's masked name is exposed, never their email or account.
    public function showVoucher(string $code): JsonResponse
    {
        $redemption = RewardRedemption::with('user:id,name')
            ->where('voucher_code', strtoupper($code))
            ->first();

        if (!$redemption) {
            return response()->json(['success' => false, 'message' => 'Voucher not found.'], 404);
        }

        return response()->json(['success' => true, 'voucher' => $this->formatVoucher($redemption)]);
    }

    public static function formatVoucher(RewardRedemption $redemption): array
    {
        return [
            'code'         => $redemption->voucher_code,
            'reward_name'  => $redemption->reward_name,
            'points_spent' => $redemption->points_spent,
            'redeemed_at'  => $redemption->created_at,
            'holder'       => self::maskName($redemption->user?->name),
            'status'       => $redemption->used_at ? 'used' : 'valid',
            'used_at'      => $redemption->used_at,
        ];
    }

    // "Adi Luthfi" -> "Adi L***": enough for a shop to match the person in
    // front of them, without publishing full names to anyone with the link.
    private static function maskName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) {
            return '-';
        }
        $first = array_shift($parts);
        return $parts ? $first . ' ' . mb_substr($parts[0], 0, 1) . '***' : $first;
    }

    // RVM-XXXX-XXXX without 0/O/1/I, so a code read out loud or typed from a
    // screenshot can't be misread.
    private function newVoucherCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $chars = '';
            for ($i = 0; $i < 8; $i++) {
                $chars .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = 'RVM-' . substr($chars, 0, 4) . '-' . substr($chars, 4);
        } while (RewardRedemption::where('voucher_code', $code)->exists());

        return $code;
    }
}
