<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\PointsHistory;
use App\Models\DetectionLog;
use App\Services\AiService;
use App\Services\CompactorSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Points awarded per recycled item — random per transaction, same range for
    // every material (conversion baseline: 100 points = RM 1).
    const POINTS_MIN = 15;
    const POINTS_MAX = 20;

    private static function calcPoints(): int
    {
        return rand(self::POINTS_MIN, self::POINTS_MAX);
    }

    const BIN_FULL_THRESHOLD = 90; // 90% = full

    protected AiService $ai;

    public function __construct(AiService $ai)
    {
        $this->ai = $ai;
    }

    // Step 1: Check bin status for selected material
    public function checkBin(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'    => 'required|string',
            'material_selected' => 'required|in:aluminum,plastic,glass,paper',
        ]);

        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        $machine  = $session->machine;
        $material = $request->material_selected;
        $level    = $machine->{$material . '_level'};

        if ($level >= self::BIN_FULL_THRESHOLD) {
            return response()->json([
                'success'    => false,
                'bin_full'   => true,
                'message'    => __('messages.bin_full', ['material' => __('messages.materials.' . $material)]),
                'bin_level'  => $level,
            ]);
        }

        return response()->json([
            'success'   => true,
            'bin_full'  => false,
            'bin_level' => $level,
            'message'   => __('messages.bin_has_space'),
        ]);
    }

    // Step 2: Open lid
    public function openLid(Request $request): JsonResponse
    {
        $request->validate(['session_code' => 'required|string']);
        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        // Simulate lid opening delay (in real hardware, trigger GPIO)
        return response()->json([
            'success' => true,
            'message' => __('messages.lid_opening'),
            'step'    => 'lid_opening',
        ]);
    }

    // Step 3: Item inserted (ready to accept)
    public function insertItem(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'required|in:aluminum,plastic,glass,paper',
        ]);

        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        return response()->json([
            'success'  => true,
            'message'  => __('messages.item_received'),
            'step'     => 'item_inserted',
            'material' => $request->material_selected,
        ]);
    }

    // Step 4: Conveyor running
    public function processConveyor(Request $request): JsonResponse
    {
        $request->validate(['session_code' => 'required|string']);
        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        return response()->json([
            'success' => true,
            'message' => __('messages.conveyor_running'),
            'step'    => 'conveyor',
        ]);
    }

    // Step 5: Capture image
    public function captureImage(Request $request): JsonResponse
    {
        $request->validate([
            'session_code' => 'required|string',
            'image'        => 'nullable|file|image|mimes:jpg,jpeg,png|max:5120',
        ]);
        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        // Priority: explicit browser upload (dev/testing) -> real hardware camera -> null (AI service mocks).
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('captures', 'public');
        } else {
            $imagePath = $this->ai->capture();
        }

        return response()->json([
            'success'    => true,
            'message'    => __('messages.image_captured'),
            'step'       => 'image_captured',
            'image_path' => $imagePath,
        ]);
    }

    // Step 6: AI Classification
    public function classify(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'nullable|in:aluminum,plastic,glass,paper',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $session  = $this->getSessionForItemInFlight($request);
        if (!$session) return $this->sessionError();

        $selected = $request->material_selected; // null when no pre-selection

        // Call Python Flask AI service
        $aiResult = $this->ai->classify($request->image_path);
        $detected = $aiResult['material'] ?? $selected ?? 'plastic';
        $confidence = $aiResult['confidence'] ?? 0;

        // Log every classification attempt (independent of whether this
        // transaction goes on to complete() or reject()) so exhibition data
        // isn't lost when a session is abandoned mid-flow.
        DetectionLog::create([
            'image_path'       => $request->image_path,
            'ai_detected_type' => $aiResult['material'] ?? null,
            'ai_confidence'    => $aiResult['confidence'] ?? null,
            'is_mock'          => $aiResult['mock'] ?? false,
            'is_guest'         => false,
            'session_id'       => $session->id,
            'user_id'          => $session->user_id,
            'machine_id'       => $session->machine_id,
        ]);

        // 'unknown' (nothing recognized) and 'reject' (recognized but no accept
        // slot for this material — see ai_service/app.py's normalize_material)
        // are never valid; otherwise, valid unless it mismatches a pre-selection.
        if ($detected === 'unknown' || $detected === 'reject') {
            $isValid = false;
        } else {
            $isValid = $selected === null ? true : ($detected === $selected);
        }

        return response()->json([
            'success'           => true,
            'is_valid'          => $isValid,
            'material_selected' => $selected,
            'ai_detected'       => $detected,
            'confidence'        => $confidence,
            'all_predictions'   => $aiResult['all_predictions'] ?? [],
            // true when AiService fell back to mockClassify(): the 2-bin
            // kiosk then treats the item as unknown instead of dropping it.
            'mock'              => (bool) ($aiResult['mock'] ?? false),
            'message'           => __('messages.item_classified'),
            'step'              => 'validated',
        ]);
    }

    // Step 7: Weigh item
    public function weigh(Request $request, \App\Services\RewardConfigService $rewardConfig): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'nullable|in:aluminum,plastic,glass,paper',
            'ai_detected_type'  => 'nullable|string',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $session  = $this->getSessionForItemInFlight($request);
        if (!$session) return $this->sessionError();

        // Use AI detected type as material (since no pre-selection in new flow)
        $material    = $request->ai_detected_type ?? $request->material_selected ?? 'plastic';
        // No physical scale on this hardware — weight is simulated purely for bin-level
        // tracking (see complete()) and storage; it's no longer shown to the user or
        // used to derive points (points are now a fixed amount per material, see
        // POINTS_PER_ITEM above).
        $weightGrams = match($material) {
            'aluminum', 'plastic'  => rand(9, 49),
            'glass', 'paper'       => rand(50, 500),
            'unknown', 'reject'    => 0,
            default                => rand(9, 49),
        };
        $pointsEarned  = $rewardConfig->load()[$material] ?? self::calcPoints();

        // Server-authoritative result for this session's pending item — complete()
        // reads this back instead of trusting client-supplied weight/points, so a
        // forged request body can no longer mint arbitrary points (Cache::pull in
        // complete() also makes this single-use, preventing replay).
        Cache::put(
            "rvm:pending_txn:{$session->id}",
            ['weight_grams' => $weightGrams, 'points_earned' => $pointsEarned, 'material' => $material],
            now()->addMinutes(15)
        );

        return response()->json([
            'success'       => true,
            'weight_grams'  => $weightGrams,
            'points_earned' => $pointsEarned,
            'material'      => $material,
            'message'       => __('messages.points_earned_message', ['points' => $pointsEarned]),
            'step'          => 'weighed',
        ]);
    }

    // Step 8: Complete transaction (save to DB, update points)
    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'nullable|in:aluminum,plastic,glass,paper',
            // Used as a bin column name below, so only the four real materials.
            'ai_detected_type'  => 'nullable|in:aluminum,plastic,glass,paper',
            'ai_confidence'     => 'nullable|numeric',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
            'idempotency_key'   => 'nullable|string|max:64',
        ]);

        $session = $this->getSessionForItemInFlight($request);
        if (!$session) return $this->sessionError();

        // Retry of an item this session already completed (the kiosk lost the
        // first response): return that result, credit nothing (U04). Looked up
        // only inside the caller's own active session, so a key can't reach
        // another user's transaction.
        $key = $request->idempotency_key;
        if ($key !== null) {
            $done = Transaction::where('session_id', $session->id)->where('idempotency_key', $key)->first();
            if ($done) {
                return response()->json($this->completedBody($done, $request->user()->fresh()->total_points) + ['replayed' => true]);
            }
        }

        // weight_grams / points_earned are never taken from the client — they must
        // have been computed and cached by weigh() for this exact session. Cache::pull
        // also removes the entry, so the same weighed item can't be completed twice.
        $pending = Cache::pull("rvm:pending_txn:{$session->id}");
        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => __('messages.no_pending_item'),
            ], 400);
        }
        $weightGrams  = $pending['weight_grams'];
        $pointsEarned = $pending['points_earned'];

        $user    = $request->user();
        $machine = $session->machine;

        // Save transaction — material comes from AI detection (no pre-selection)
        $materialUsed = $request->ai_detected_type ?? $request->material_selected ?? 'unknown';

        // All-or-nothing: a failure part-way must not leave a transaction
        // without its points, or points without their history row (U04).
        $transaction = DB::transaction(function () use ($session, $user, $machine, $materialUsed, $weightGrams, $pointsEarned, $request, $key) {
            $transaction = Transaction::create([
                'session_id'        => $session->id,
                'user_id'           => $user->id,
                'machine_id'        => $machine->id,
                'material_selected' => $materialUsed,
                'ai_detected_type'  => $materialUsed,
                'ai_confidence'     => $request->ai_confidence,
                'is_valid'          => 1,
                'weight_grams'      => $weightGrams,
                'points_earned'     => $pointsEarned,
                'points_deducted'   => 0,
                'image_path'        => $request->image_path,
                'idempotency_key'   => $key,
            ]);

            $user->increment('total_points', $pointsEarned);

            PointsHistory::create([
                'user_id'        => $user->id,
                'transaction_id' => $transaction->id,
                'session_id'     => $session->id,
                'points_change'  => $pointsEarned,
                'balance_after'  => $user->fresh()->total_points,
                'type'           => 'earned',
                'description'    => "Recycled {$weightGrams}g of {$materialUsed}",
            ]);

            $binField = $materialUsed . '_level';
            $newLevel = min(100, $machine->$binField + (int)($weightGrams / 50));
            $machine->update([$binField => $newLevel]);

            if ($session->status === 'active') {
                $session->increment('total_items');
                $session->increment('points_earned', $pointsEarned);
                $session->update(['end_points' => $user->fresh()->total_points]);
            } else {
                // Finished within the grace window after the session ended.
                $session->refreshTotalsFromTransactions((int) $user->fresh()->total_points);
            }

            return $transaction;
        });

        // Drop the item into the physical bin only after the records are safely
        // committed, so the hardware never moves for a rolled-back write.
        $this->ai->sort($materialUsed);

        return response()->json($this->completedBody($transaction, $user->fresh()->total_points));
    }

    // Step 8b: Reject item (invalid material)
    public function reject(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'required|in:aluminum,plastic,glass,paper,reject',
            'ai_detected_type'  => 'required|string',
            'ai_confidence'     => 'nullable|numeric',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $session = $this->getSessionForItemInFlight($request);
        if (!$session) return $this->sessionError();

        $user    = $request->user();
        $machine = $session->machine;

        // A rejected item earns nothing and costs nothing: the user simply gets
        // it back. Same rule as the kiosk flow (unknown / reject = 0 points).
        $transaction = Transaction::create([
            'session_id'        => $session->id,
            'user_id'           => $user->id,
            'machine_id'        => $machine->id,
            'material_selected' => $request->material_selected,
            'ai_detected_type'  => $request->ai_detected_type,
            'ai_confidence'     => $request->ai_confidence,
            'is_valid'          => 0,
            'weight_grams'      => 0,
            'points_earned'     => 0,
            'points_deducted'   => 0,
            'image_path'        => $request->image_path,
        ]);
        $deduction = 0;

        if ($session->status !== 'active') {
            $session->refreshTotalsFromTransactions((int) $user->fresh()->total_points);
        }

        // Drop the rejected item into the reject bin, after the commit.
        $this->ai->sort('reject');

        return response()->json([
            'success'          => false,
            'message'          => __('messages.item_rejected'),
            'transaction_id'   => $transaction->id,
            'points_deducted'  => $deduction,
            'total_points'     => $user->fresh()->total_points,
            'material_selected'=> $request->material_selected,
            'ai_detected'      => $request->ai_detected_type,
            'step'             => 'rejected',
        ]);
    }

    // Guest-safe hardware actions (no auth, no session, no points writes) —
    // lets the kiosk's "Continue as Guest" flow still drive the real camera,
    // AI classification, and sorting servo. hardwareClassify() does write one
    // detection_logs row per classification, for exhibition accuracy review.
    public function hardwareCapture(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'nullable|file|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('captures', 'public');
        } else {
            $imagePath = $this->ai->capture();
        }

        return response()->json(['success' => true, 'image_path' => $imagePath]);
    }

    public function hardwareClassify(Request $request): JsonResponse
    {
        $request->validate([
            'image_path' => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $aiResult = $this->ai->classify($request->image_path);
        $detected = $aiResult['material'] ?? 'unknown';

        DetectionLog::create([
            'image_path'       => $request->image_path,
            'ai_detected_type' => $aiResult['material'] ?? null,
            'ai_confidence'    => $aiResult['confidence'] ?? null,
            'is_mock'          => $aiResult['mock'] ?? false,
            'is_guest'         => true,
        ]);

        return response()->json([
            'success'         => true,
            'is_valid'        => $detected !== 'unknown' && $detected !== 'reject',
            'ai_detected'     => $detected,
            'confidence'      => $aiResult['confidence'] ?? 0,
            'all_predictions' => $aiResult['all_predictions'] ?? [],
            'mock'            => (bool) ($aiResult['mock'] ?? false),
            // Guests never hit complete() (no DB record — see
            // RvmStore.processStep's isGuest branch), which is where a
            // logged-in session's carbon_saved comes from. This is the one
            // real (non-mocked) call guests make with the material already
            // known, so it's included here instead for the guest flow to use.
            'carbon_saved'    => \App\Services\CarbonService::forMaterial($detected),
        ]);
    }

    public function hardwareSort(Request $request): JsonResponse
    {
        $request->validate(['material' => 'nullable|string']);
        $this->ai->sort($request->material ?? 'reject');

        return response()->json(['success' => true]);
    }

    // 2-bin compactor (DSME machine) — thin proxies to ai_service/machine_api.py.
    // Public like hardwareSort, since the kiosk's guest flow needs them too.
    public function hardwareState(Request $request, CompactorSettingsService $compactor): JsonResponse
    {
        $request->validate(['job' => 'nullable|string|max:64']);
        $result = $this->ai->state($request->query('job'));

        // Only ai_service_4bin answers 404 here (it has no /state route), so
        // only that means "use the old /hardware/sort flow". Anything else —
        // unreachable, restarting, 401/500 — is "unavailable": falling back to
        // legacy there would award points without the flap ever opening.
        if ($result !== null && $result['status'] === 404) {
            return response()->json(['profile' => 'legacy']);
        }
        if ($result === null || $result['status'] !== 200) {
            return response()->json(['profile' => 'unavailable']);
        }

        $body = $result['body'];
        // The kiosk's per-session maximum per material (admin setting).
        if (($body['profile'] ?? null) === '2bin') {
            $body['limits'] = $compactor->load();
        }
        return response()->json($body);
    }

    public function hardwareDeposit(Request $request, CompactorSettingsService $compactor): JsonResponse
    {
        $request->validate([
            'material'    => 'required|string|max:32',
            'allow_flush' => 'required|boolean',
        ]);

        // The machine empties the chamber before an item past the admin's maximum.
        return $this->machineResponse($this->ai->deposit(
            $request->material, $request->boolean('allow_flush'), $compactor->capacityFor($request->material)
        ));
    }

    public function hardwareFlush(): JsonResponse
    {
        return $this->machineResponse($this->ai->flush());
    }

    public function hardwareFlapCheck(): JsonResponse
    {
        return $this->machineResponse($this->ai->flapCheck());
    }

    // Only 200/400 are real answers from the machine. Anything else (401 key
    // mismatch, 404, 500) becomes 503: a 401 must never reach the browser,
    // where services/api.js treats it as "logged out".
    private function machineResponse(?array $result): JsonResponse
    {
        if ($result === null || !in_array($result['status'], [200, 400], true)) {
            return response()->json(['success' => false, 'error' => 'machine_unavailable'], 503);
        }

        return response()->json($result['body'], $result['status']);
    }

    // Step: start a real recycling_sessions row for a "Continue as Guest" kiosk
    // session, tied to the shared \App\Models\User::guest() placeholder account
    // instead of a real logged-in user — see that model's docblock for why. This
    // is what lets guest activity show up in the admin dashboard's stats/charts
    // (they all query recycling_sessions/transactions), which previously never
    // saw guests at all since the old guest flow was pure client-side simulation.
    public function guestSessionStart(Request $request): JsonResponse
    {
        $request->validate(['machine_id' => 'required|exists:rvm_machines,id']);

        $guest   = \App\Models\User::guest();
        $machine = RvmMachine::find($request->machine_id);

        // Unlike a real user, the guest placeholder is shared by every
        // concurrent guest on the kiosk — "one active session per user" would
        // incorrectly serialize unrelated guests, so that check (present in
        // SessionController::start) is deliberately skipped here.
        $session = RecyclingSession::create([
            'session_code'  => 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'user_id'       => $guest->id,
            'machine_id'    => $machine->id,
            'status'        => 'active',
            'start_points'  => $guest->total_points,
            'end_points'    => $guest->total_points,
            'points_earned' => 0,
            'total_items'   => 0,
            'started_at'    => now(),
        ]);

        return response()->json(['success' => true, 'session_code' => $session->session_code]);
    }

    // Step: record one guest item as a real transaction (points computed the
    // same way a logged-in session's weigh()+complete() would - see those for
    // the reasoning), then immediately mark it complete. Guests skip the
    // separate weigh step entirely (no pre-selection UI, no Cache-staged
    // pending item to confirm), so this does both in one call.
    public function guestSessionComplete(Request $request, \App\Services\RewardConfigService $rewardConfig): JsonResponse
    {
        $request->validate([
            'session_code'     => 'required|string',
            'ai_detected_type' => 'required|string',
            'material_selected' => 'nullable|in:reject',
            'ai_confidence'    => 'nullable|numeric',
            'image_path'       => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $guest   = \App\Models\User::guest();
        $session = RecyclingSession::where('session_code', $request->session_code)
            ->where('user_id', $guest->id)
            ->where('status', 'active')
            ->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => __('messages.active_session_not_found')], 404);
        }

        $material = $request->ai_detected_type;
        // A named reject (demo kiosk): the item is recognised but the machine does not take it.
        $named    = $request->material_selected === 'reject';
        $isValid  = !$named && $material !== 'unknown' && $material !== 'reject';

        $weightGrams = match($material) {
            'aluminum', 'plastic' => rand(9, 49),
            'glass', 'paper'      => rand(50, 500),
            default               => 0, // unknown/reject
        };
        $pointsEarned = $isValid ? ($rewardConfig->load()[$material] ?? self::calcPoints()) : 0;

        $transaction = Transaction::create([
            'session_id'        => $session->id,
            'user_id'           => $guest->id,
            'machine_id'        => $session->machine_id,
            'material_selected' => $named ? 'reject' : $material,
            'ai_detected_type'  => $material,
            'ai_confidence'     => $request->ai_confidence,
            'is_valid'          => $isValid,
            'weight_grams'      => $weightGrams,
            'points_earned'     => $pointsEarned,
            'points_deducted'   => 0,
            'image_path'        => $request->image_path,
        ]);

        if ($isValid) {
            $machine = $session->machine;
            $guest->increment('total_points', $pointsEarned);
            PointsHistory::create([
                'user_id'        => $guest->id,
                'transaction_id' => $transaction->id,
                'session_id'     => $session->id,
                'points_change'  => $pointsEarned,
                'balance_after'  => $guest->fresh()->total_points,
                'type'           => 'earned',
                'description'    => "Recycled {$weightGrams}g of {$material} (guest)",
            ]);

            $binField = $material . '_level';
            $machine->update([$binField => min(100, $machine->$binField + (int) ($weightGrams / 50))]);

            $session->increment('total_items');
            $session->increment('points_earned', $pointsEarned);
            $session->update(['end_points' => $guest->fresh()->total_points]);
        }

        return response()->json([
            'success'       => true,
            'points_earned' => $pointsEarned,
            'weight_grams'  => $weightGrams,
            'material'      => $material,
            'carbon_saved'  => $named ? 0 : \App\Services\CarbonService::forMaterial($material),
        ]);
    }

    // Step: close out a guest session's recycling_sessions row (mirrors
    // SessionController::end, minus updating any real user's total_points -
    // the guest placeholder's running total was already kept current per-item
    // in guestSessionComplete above).
    public function guestSessionEnd(Request $request): JsonResponse
    {
        $request->validate(['session_code' => 'required|string']);

        $guest   = \App\Models\User::guest();
        $session = RecyclingSession::with('transactions')
            ->where('session_code', $request->session_code)
            ->where('user_id', $guest->id)
            ->where('status', 'active')
            ->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => __('messages.active_session_not_found')], 404);
        }

        $session->update(['status' => 'completed', 'ended_at' => now()]);

        return response()->json([
            'success' => true,
            'session' => [
                'session_code'  => $session->session_code,
                'points_earned' => $session->points_earned,
                'total_items'   => $session->total_items,
            ],
        ]);
    }

    // Helpers
    // Response of a completed deposit; shared by the first call and a replay.
    private function completedBody(Transaction $transaction, int $totalPoints): array
    {
        return [
            'success'        => true,
            'message'        => __('messages.transaction_completed'),
            'transaction_id' => $transaction->id,
            'points_earned'  => (int) $transaction->points_earned,
            'total_points'   => $totalPoints,
            'weight_grams'   => (int) $transaction->weight_grams,
            'material'       => $transaction->material_selected,
            'carbon_saved'   => \App\Services\CarbonService::forMaterial($transaction->material_selected),
            'step'           => 'complete',
        ];
    }

    // Item-start endpoints: the session must be active right now.
    private function getActiveSession(Request $request): ?RecyclingSession
    {
        return RecyclingSession::with('machine')
            ->where('session_code', $request->session_code)
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();
    }

    // Item-continuation endpoints (classify, weigh, complete, reject): active, or
    // ended within the grace window so an item already in flight can finish.
    private function getSessionForItemInFlight(Request $request): ?RecyclingSession
    {
        return RecyclingSession::with('machine')
            ->where('session_code', $request->session_code)
            ->where('user_id', $request->user()->id)
            ->where(function ($q) {
                $q->where('status', 'active')
                  ->orWhere(function ($q) {
                      $q->whereIn('status', ['completed', 'expired'])
                        ->where('ended_at', '>=', now()->subSeconds(RecyclingSession::IN_FLIGHT_GRACE_SECONDS));
                  });
            })
            ->first();
    }

    private function sessionError(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => __('messages.active_session_not_found')], 404);
    }
}
