<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\PointsHistory;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

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
    const DEDUCTION_INVALID  = 10;

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
                'message'    => "The {$material} bin is full. Please choose a different material.",
                'bin_level'  => $level,
            ]);
        }

        return response()->json([
            'success'   => true,
            'bin_full'  => false,
            'bin_level' => $level,
            'message'   => 'Bin has space. Proceeding to open lid.',
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
            'message' => 'Lid is opening. Please wait...',
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
            'message'  => 'Item received. Starting conveyor...',
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
            'message' => 'Conveyor running. Moving item to scanning station...',
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
            'message'    => 'Image captured successfully.',
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

        $session  = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        $selected = $request->material_selected; // null when no pre-selection

        // Call Python Flask AI service
        $aiResult = $this->ai->classify($request->image_path);
        $detected = $aiResult['material'] ?? $selected ?? 'plastic';
        $confidence = $aiResult['confidence'] ?? 0;

        // Unknown detections are never valid; otherwise, valid unless it mismatches a pre-selection
        if ($detected === 'unknown') {
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
            'message'           => 'Item classified successfully.',
            'step'              => 'validated',
        ]);
    }

    // Step 7: Weigh item
    public function weigh(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'nullable|in:aluminum,plastic,glass,paper',
            'ai_detected_type'  => 'nullable|string',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $session  = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        // Use AI detected type as material (since no pre-selection in new flow)
        $material    = $request->ai_detected_type ?? $request->material_selected ?? 'plastic';
        // No physical scale on this hardware — weight is simulated purely for bin-level
        // tracking (see complete()) and storage; it's no longer shown to the user or
        // used to derive points (points are now a fixed amount per material, see
        // POINTS_PER_ITEM above).
        $weightGrams = match($material) {
            'aluminum', 'plastic' => rand(9, 49),
            'glass', 'paper'      => rand(50, 500),
            'unknown'             => 0,
            default               => rand(9, 49),
        };
        $pointsEarned  = self::calcPoints();

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
            'message'       => "You earned {$pointsEarned} points!",
            'step'          => 'weighed',
        ]);
    }

    // Step 8: Complete transaction (save to DB, update points)
    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'nullable|in:aluminum,plastic,glass,paper',
            'ai_detected_type'  => 'nullable|string',
            'ai_confidence'     => 'nullable|numeric',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        // weight_grams / points_earned are never taken from the client — they must
        // have been computed and cached by weigh() for this exact session. Cache::pull
        // also removes the entry, so the same weighed item can't be completed twice.
        $pending = Cache::pull("rvm:pending_txn:{$session->id}");
        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'No weighed item is pending for this session. Please weigh an item first.',
            ], 400);
        }
        $weightGrams  = $pending['weight_grams'];
        $pointsEarned = $pending['points_earned'];

        $user    = $request->user();
        $machine = $session->machine;

        // Save transaction — material comes from AI detection (no pre-selection)
        $materialUsed = $request->ai_detected_type ?? $request->material_selected ?? 'unknown';
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
        ]);

        // Drop the item into the physical bin that matches its detected material.
        $this->ai->sort($materialUsed);

        // Update user points
        $user->increment('total_points', $pointsEarned);

        // Record points history
        PointsHistory::create([
            'user_id'        => $user->id,
            'transaction_id' => $transaction->id,
            'session_id'     => $session->id,
            'points_change'  => $pointsEarned,
            'balance_after'  => $user->fresh()->total_points,
            'type'           => 'earned',
            'description'    => "Recycled {$weightGrams}g of {$materialUsed}",
        ]);

        // Update bin level
        $binField = $materialUsed . '_level';
        $newLevel = min(100, $machine->$binField + (int)($weightGrams / 50));
        $machine->update([$binField => $newLevel]);

        // Update session totals
        $session->increment('total_items');
        $session->increment('points_earned', $pointsEarned);
        $session->update(['end_points' => $user->fresh()->total_points]);

        return response()->json([
            'success'        => true,
            'message'        => 'Transaction completed successfully!',
            'transaction_id' => $transaction->id,
            'points_earned'  => $pointsEarned,
            'total_points'   => $user->fresh()->total_points,
            'weight_grams'   => $weightGrams,
            'material'       => $materialUsed,
            'carbon_saved'   => \App\Services\CarbonService::forMaterial($materialUsed),
            'step'           => 'complete',
        ]);
    }

    // Step 8b: Reject item (invalid material)
    public function reject(Request $request): JsonResponse
    {
        $request->validate([
            'session_code'      => 'required|string',
            'material_selected' => 'required|in:aluminum,plastic,glass,paper',
            'ai_detected_type'  => 'required|string',
            'ai_confidence'     => 'nullable|numeric',
            'image_path'        => ['nullable', 'string', 'regex:#^captures/[A-Za-z0-9_-]+\.(jpe?g|png)$#i'],
        ]);

        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        $user    = $request->user();
        $machine = $session->machine;

        // Save rejected transaction
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
            'points_deducted'   => self::DEDUCTION_INVALID,
            'image_path'        => $request->image_path,
        ]);

        // Drop the rejected item into the reject bin.
        $this->ai->sort('reject');

        // Deduct points
        $deduction = min($user->total_points, self::DEDUCTION_INVALID);
        $user->decrement('total_points', $deduction);

        // Record deduction
        PointsHistory::create([
            'user_id'        => $user->id,
            'transaction_id' => $transaction->id,
            'session_id'     => $session->id,
            'points_change'  => -$deduction,
            'balance_after'  => $user->fresh()->total_points,
            'type'           => 'deducted',
            'description'    => "Invalid item: selected {$request->material_selected}, detected {$request->ai_detected_type}",
        ]);

        return response()->json([
            'success'          => false,
            'message'          => 'Item rejected. Points deducted.',
            'transaction_id'   => $transaction->id,
            'points_deducted'  => $deduction,
            'total_points'     => $user->fresh()->total_points,
            'material_selected'=> $request->material_selected,
            'ai_detected'      => $request->ai_detected_type,
            'step'             => 'rejected',
        ]);
    }

    // Guest-safe hardware actions (no auth, no session, no points/DB writes) —
    // lets the kiosk's "Continue as Guest" flow still drive the real camera,
    // AI classification, and sorting servo.
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

        return response()->json([
            'success'         => true,
            'is_valid'        => $detected !== 'unknown',
            'ai_detected'     => $detected,
            'confidence'      => $aiResult['confidence'] ?? 0,
            'all_predictions' => $aiResult['all_predictions'] ?? [],
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

    // Helpers
    private function getActiveSession(Request $request): ?RecyclingSession
    {
        return RecyclingSession::with('machine')
            ->where('session_code', $request->session_code)
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();
    }

    private function sessionError(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Active session not found.'], 404);
    }
}
