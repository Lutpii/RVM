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

class TransactionController extends Controller
{
    // Points awarded per 10g of material
    const POINTS_PER_10G = [
        'aluminum' => 7,
        'plastic'  => 5,
        'glass'    => 1,
        'paper'    => 1,
    ];

    private static function calcPoints(string $material, int $weightGrams): int
    {
        $rate = self::POINTS_PER_10G[$material] ?? 1;
        return (int) floor($weightGrams / 10) * $rate;
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
        $request->validate(['session_code' => 'required|string']);
        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        // In real hardware: trigger camera capture
        // For web prototype: accept uploaded image OR use placeholder
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('captures', 'public');
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
            'image_path'        => 'nullable|string',
        ]);

        $session  = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        $selected = $request->material_selected; // null when no pre-selection

        // Call Python Flask AI service
        $aiResult = $this->ai->classify($request->image_path);
        $detected = $aiResult['material'] ?? $selected ?? 'plastic';
        $confidence = $aiResult['confidence'] ?? 0;

        // When no material was pre-selected, AI result is always valid
        $isValid = $selected === null ? true : ($detected === $selected);

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
            'image_path'        => 'nullable|string',
        ]);

        $session  = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

        // Use AI detected type as material (since no pre-selection in new flow)
        $material    = $request->ai_detected_type ?? $request->material_selected ?? 'plastic';
        // Simulate weight from sensor
        $weightGrams = match($material) {
            'aluminum', 'plastic' => rand(9, 49),
            'glass'               => rand(50, 500),
            'paper'               => rand(50, 500),
            default               => rand(9, 49),
        };
        $pointsEarned  = self::calcPoints($material, $weightGrams);

        return response()->json([
            'success'       => true,
            'weight_grams'  => $weightGrams,
            'points_earned' => $pointsEarned,
            'material'      => $material,
            'message'       => "Weight: {$weightGrams}g — You earned {$pointsEarned} points!",
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
            'weight_grams'      => 'required|numeric',
            'points_earned'     => 'required|integer',
            'image_path'        => 'nullable|string',
        ]);

        $session = $this->getActiveSession($request);
        if (!$session) return $this->sessionError();

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
            'weight_grams'      => $request->weight_grams,
            'points_earned'     => $request->points_earned,
            'points_deducted'   => 0,
            'image_path'        => $request->image_path,
        ]);

        // Update user points
        $user->increment('total_points', $request->points_earned);

        // Record points history
        PointsHistory::create([
            'user_id'        => $user->id,
            'transaction_id' => $transaction->id,
            'session_id'     => $session->id,
            'points_change'  => $request->points_earned,
            'balance_after'  => $user->fresh()->total_points,
            'type'           => 'earned',
            'description'    => "Recycled {$request->weight_grams}g of {$request->material_selected}",
        ]);

        // Update bin level
        $binField = $request->material_selected . '_level';
        $newLevel = min(100, $machine->$binField + (int)($request->weight_grams / 50));
        $machine->update([$binField => $newLevel]);

        // Update session totals
        $session->increment('total_items');
        $session->increment('points_earned', $request->points_earned);
        $session->update(['end_points' => $user->fresh()->total_points]);

        return response()->json([
            'success'        => true,
            'message'        => 'Transaction completed successfully!',
            'transaction_id' => $transaction->id,
            'points_earned'  => $request->points_earned,
            'total_points'   => $user->fresh()->total_points,
            'weight_grams'   => $request->weight_grams,
            'material'       => $request->material_selected,
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
            'image_path'        => 'nullable|string',
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
