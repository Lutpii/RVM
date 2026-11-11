<?php

namespace App\Http\Controllers;

use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RewardRedemption;
use App\Models\Transaction;
use App\Services\CarbonService;
use App\Support\AvatarIcon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'avatar_url'   => $user->avatar_url,
                'avatar_icon'  => $user->avatar_icon,
                'total_points' => $user->total_points,
                'role'         => $user->role,
                'is_verified'  => $user->is_verified,
                'created_at'   => $user->created_at,
                'has_password' => !empty($user->password_hash),
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'name'     => 'sometimes|string|max:100',
            'password' => 'sometimes|string|min:8|confirmed',
            'preferred_language' => 'sometimes|in:en,id',
            'theme_preference'   => 'sometimes|in:dark,light',
            // null = back to the initial-letter avatar
            'avatar_icon'        => ['sometimes', 'nullable', 'string', 'regex:' . AvatarIcon::pattern()],
        ]);

        $data = $request->only(['name', 'preferred_language', 'theme_preference']);
        if ($request->exists('avatar_icon')) {
            $data['avatar_icon'] = $request->input('avatar_icon');
        }
        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $user->update($data);
        return response()->json(['success' => true, 'user' => $user]);
    }

    // Separate from updateProfile()'s own password support: this one requires
    // the current password (updateProfile changes it on trust alone, fine for
    // "edit my profile" but not what a dedicated "Change Password" form should
    // do) and matches the field names FrontEnd/src/views/UserSettingsView.vue
    // already sends (current_password/new_password/new_password_confirmation).
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'      => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();
        // A Google-only account (no password_hash yet) can never satisfy a
        // "current password" check — Hash::check() against null would throw.
        if (!$user->password_hash || !Hash::check($request->current_password, $user->password_hash)) {
            return response()->json(['success' => false, 'message' => __('messages.current_password_incorrect')], 422);
        }

        $user->update(['password_hash' => Hash::make($request->new_password)]);
        return response()->json(['success' => true, 'message' => __('messages.password_updated')]);
    }

    // The frontend's "Danger Zone" delete-account confirmation already calls
    // this with no body (just the bearer token) — no extra password re-entry
    // step exists in that UI, so none is required here either. recycling_
    // sessions/transactions/points_history/reward_redemptions/admin_logs all
    // cascade-delete with the user (see their migrations); detection_logs
    // nulls its user_id instead so the exhibition/analytics data survives.
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->user()->delete();
        return response()->json(['success' => true, 'message' => __('messages.account_deleted')]);
    }

    public function pointsHistory(Request $request): JsonResponse
    {
        $history = PointsHistory::with('transaction')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id') // same-second rows: newest first (BUG-001)
            ->paginate(20);

        // Built fresh in the current request's locale rather than read from
        // the stored `description` column — that column is written once, in
        // whatever language was active at transaction time, and would stay
        // frozen in that language forever otherwise (e.g. a user who
        // recycles today and switches language tomorrow would see old
        // entries stuck in the old language).
        $history->getCollection()->transform(function (PointsHistory $entry) {
            $entry->description = $this->describeHistoryEntry($entry);
            return $entry;
        });

        return response()->json(['success' => true, 'history' => $history]);
    }

    private function describeHistoryEntry(PointsHistory $entry): string
    {
        $t = $entry->transaction;
        if (!$t) {
            return $entry->description; // fallback for any orphaned/legacy row
        }

        if ($entry->type === 'earned') {
            return __('messages.recycled_item', [
                'weight'   => $t->weight_grams,
                'material' => __('messages.materials.' . $t->material_selected),
            ]);
        }

        return __('messages.invalid_item', [
            'selected' => __('messages.materials.' . $t->material_selected),
            'detected' => __('messages.materials.' . ($t->ai_detected_type ?? 'unknown')),
        ]);
    }

    /**
     * The numbers on the Account page summary card: lifetime items and CO2,
     * this month's points in and out, and vouchers not used yet.
     */
    public function accountSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $monthStart = now()->startOfMonth();

        $counts = Transaction::where('user_id', $user->id)->where('is_valid', 1)
            ->selectRaw('material_selected, COUNT(*) AS n')
            ->groupBy('material_selected')
            ->pluck('n', 'material_selected');

        $carbon = 0.0;
        foreach ($counts as $material => $n) {
            $carbon += $n * CarbonService::forMaterial($material);
        }

        $month = PointsHistory::where('user_id', $user->id)
            ->where('created_at', '>=', $monthStart)
            ->selectRaw('COALESCE(SUM(CASE WHEN points_change > 0 THEN points_change ELSE 0 END), 0) AS earned,
                         COALESCE(SUM(CASE WHEN points_change < 0 THEN -points_change ELSE 0 END), 0) AS redeemed')
            ->first();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_points'        => (int) $user->total_points,
                'items_recycled'      => (int) $counts->sum(),
                'carbon_saved_kg'     => round($carbon, 2),
                'earned_this_month'   => (int) $month->earned,
                'redeemed_this_month' => (int) $month->redeemed,
                'active_vouchers'     => RewardRedemption::where('user_id', $user->id)
                    ->whereNotNull('voucher_code')->whereNull('used_at')->count(),
            ],
        ]);
    }

    public function sessions(Request $request): JsonResponse
    {
        $sessions = RecyclingSession::with('machine')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('started_at')
            ->paginate(10);

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }
}
