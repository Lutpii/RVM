<?php

namespace App\Http\Controllers;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SessionController extends Controller
{
    // Start a new recycling session
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'machine_id' => 'required|exists:rvm_machines,id',
            'qr_token'   => 'required|string',
        ]);

        $user    = $request->user();
        $machine = RvmMachine::find($request->machine_id);

        if ($machine->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'This machine is not currently active.'], 400);
        }

        // Check for existing active session
        $existing = RecyclingSession::where('user_id', $user->id)
            ->where('status', 'active')->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You already have an active session.', 'session_code' => $existing->session_code], 400);
        }

        $sessionCode = 'RVM-' . strtoupper(Str::random(12));

        $session = RecyclingSession::create([
            'session_code'  => $sessionCode,
            'user_id'       => $user->id,
            'machine_id'    => $machine->id,
            'status'        => 'active',
            'start_points'  => $user->total_points,
            'end_points'    => $user->total_points,
            'points_earned' => 0,
            'total_items'   => 0,
            'started_at'    => Carbon::now(),
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Session started successfully.',
            'session_code' => $sessionCode,
            'session'      => $this->formatSession($session, $user, $machine),
        ]);
    }

    // Get session details
    public function show(Request $request, string $sessionCode): JsonResponse
    {
        $session = RecyclingSession::with(['machine', 'transactions'])
            ->where('session_code', $sessionCode)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'session' => $this->formatSession($session, $request->user(), $session->machine),
        ]);
    }

    // End session
    public function end(Request $request, string $sessionCode): JsonResponse
    {
        $session = RecyclingSession::with('transactions')
            ->where('session_code', $sessionCode)
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Active session not found.'], 404);
        }

        $user = $request->user()->fresh();

        // Recalculate from transactions saved in DB for this session
        $earned     = $session->transactions->where('is_valid', true)->sum('points_earned');
        $deducted   = $session->transactions->where('is_valid', false)->sum('points_deducted');
        $totalItems = $session->transactions->where('is_valid', true)->count();
        $endPoints  = max(0, $session->start_points + $earned - $deducted);

        // Persist correct total_points to users table
        $user->update(['total_points' => $endPoints]);

        $session->update([
            'status'        => 'completed',
            'end_points'    => $endPoints,
            'points_earned' => $earned - $deducted,
            'total_items'   => $totalItems,
            'ended_at'      => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session ended successfully.',
            'session' => $this->formatSession($session->fresh(), $user->fresh(), $session->machine),
        ]);
    }

    // Session summary
    public function summary(Request $request, string $sessionCode): JsonResponse
    {
        $session = RecyclingSession::with(['machine', 'transactions'])
            ->where('session_code', $sessionCode)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found.'], 404);
        }

        $user = $request->user();

        return response()->json([
            'success' => true,
            'summary' => [
                'session_code'  => $session->session_code,
                'user_name'     => $user->name,
                'machine_name'  => $session->machine->name,
                'start_points'  => $session->start_points,
                'end_points'    => $session->end_points,
                'points_earned' => $session->points_earned,
                'total_items'   => $session->total_items,
                'started_at'    => $session->started_at,
                'ended_at'      => $session->ended_at,
                'transactions'  => $session->transactions->map(fn($t) => [
                    'material'       => $t->material_selected,
                    'weight'         => $t->weight_grams,
                    'points_earned'  => $t->points_earned,
                    'points_deducted'=> $t->points_deducted,
                    'is_valid'       => $t->is_valid,
                ]),
            ],
        ]);
    }

    private function formatSession(RecyclingSession $session, User $user, RvmMachine $machine): array
    {
        return [
            'session_code'  => $session->session_code,
            'status'        => $session->status,
            'user_name'     => $user->name,
            'current_points'=> $user->total_points,
            'end_points'    => $session->end_points,
            'start_points'  => $session->start_points,
            'points_earned' => $session->points_earned,
            'total_items'   => $session->total_items,
            'machine'       => [
                'id'            => $machine->id,
                'name'          => $machine->name,
                'location'      => $machine->location_name,
                'aluminum_level'=> $machine->aluminum_level,
                'plastic_level' => $machine->plastic_level,
                'glass_level'   => $machine->glass_level,
                'paper_level'   => $machine->paper_level,
            ],
            'started_at'    => $session->started_at,
        ];
    }
}
