<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RvmMachine;
use App\Models\RecyclingSession;
use App\Models\Transaction;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Dashboard stats
    public function dashboard(Request $request): JsonResponse
    {
        $totalUsers       = User::where('role', 'user')->count();
        $totalMachines    = RvmMachine::count();
        $activeSessions   = RecyclingSession::where('status', 'active')->count();
        $totalTransactions= Transaction::count();
        $totalPointsGiven = Transaction::where('is_valid', 1)->sum('points_earned');
        $totalWeight      = Transaction::where('is_valid', 1)->sum('weight_grams');

        $materialStats = Transaction::where('is_valid', 1)
            ->selectRaw('material_selected, COUNT(*) as count, SUM(weight_grams) as total_weight, SUM(points_earned) as total_points')
            ->groupBy('material_selected')
            ->get();

        $recentSessions = RecyclingSession::with(['user', 'machine'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'session_code'  => $s->session_code,
                'user_name'     => $s->user->name,
                'machine_name'  => $s->machine->name,
                'status'        => $s->status,
                'points_earned' => $s->points_earned,
                'started_at'    => $s->started_at,
            ]);

        $fullBins = RvmMachine::where('aluminum_level', '>=', 90)
            ->orWhere('plastic_level', '>=', 90)
            ->orWhere('glass_level', '>=', 90)
            ->orWhere('paper_level', '>=', 90)
            ->get(['id','name','aluminum_level','plastic_level','glass_level','paper_level']);

        return response()->json([
            'success' => true,
            'stats'   => [
                'total_users'        => $totalUsers,
                'total_machines'     => $totalMachines,
                'active_sessions'    => $activeSessions,
                'total_transactions' => $totalTransactions,
                'total_points_given' => $totalPointsGiven,
                'total_weight_kg'    => round($totalWeight / 1000, 2),
                'material_stats'     => $materialStats,
                'recent_sessions'    => $recentSessions,
                'full_bins'          => $fullBins,
            ],
        ]);
    }

    // Users management
    public function users(Request $request): JsonResponse
    {
        $users = User::where('role', 'user')
            ->orderByDesc('total_points')
            ->paginate(20);

        return response()->json(['success' => true, 'users' => $users]);
    }

    public function showUser(Request $request, int $id): JsonResponse
    {
        $user = User::with(['recyclingSessions', 'transactions'])->find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'User not found.'], 404);

        $user->update($request->only(['name', 'email', 'phone', 'total_points', 'is_verified', 'role']));
        $this->log($request->user(), 'update_user', 'user', $id, "Updated user: {$user->name}");

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        $this->log($request->user(), 'delete_user', 'user', $id, "Deleted user: {$user->name}");
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    // Machine management
    public function machines(): JsonResponse
    {
        return response()->json(['success' => true, 'machines' => RvmMachine::all()]);
    }

    public function createMachine(Request $request): JsonResponse
    {
        $request->validate([
            'machine_code'  => 'required|unique:rvm_machines,machine_code',
            'name'          => 'required|string',
            'location_name' => 'nullable|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
        ]);

        $machine = RvmMachine::create($request->all());
        $this->log($request->user(), 'create_machine', 'machine', $machine->id, "Created machine: {$machine->name}");
        return response()->json(['success' => true, 'machine' => $machine], 201);
    }

    public function updateMachine(Request $request, int $id): JsonResponse
    {
        $machine = RvmMachine::find($id);
        if (!$machine) return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);

        $machine->update($request->only(['name', 'location_name', 'latitude', 'longitude', 'status']));
        $this->log($request->user(), 'update_machine', 'machine', $id, "Updated machine: {$machine->name}");
        return response()->json(['success' => true, 'machine' => $machine]);
    }

    public function deleteMachine(Request $request, int $id): JsonResponse
    {
        $machine = RvmMachine::find($id);
        if (!$machine) return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);
        $this->log($request->user(), 'delete_machine', 'machine', $id, "Deleted machine: {$machine->name}");
        $machine->delete();
        return response()->json(['success' => true, 'message' => 'Machine deleted.']);
    }

    public function updateBinLevels(Request $request, int $id): JsonResponse
    {
        $machine = RvmMachine::find($id);
        if (!$machine) return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);

        $machine->update($request->only(['aluminum_level', 'plastic_level', 'glass_level', 'paper_level']));
        return response()->json(['success' => true, 'machine' => $machine]);
    }

    // Sessions & Transactions
    public function allSessions(): JsonResponse
    {
        $sessions = RecyclingSession::with(['user', 'machine'])->latest()->paginate(20);
        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function allTransactions(): JsonResponse
    {
        $transactions = Transaction::with(['user', 'machine', 'session'])->latest()->paginate(20);
        return response()->json(['success' => true, 'transactions' => $transactions]);
    }

    public function stats(): JsonResponse
    {
        return $this->dashboard(request());
    }

    public function logs(): JsonResponse
    {
        $logs = AdminLog::with('admin')->latest()->paginate(50);
        return response()->json(['success' => true, 'logs' => $logs]);
    }

    private function log(User $admin, string $action, string $targetType, int $targetId, string $details): void
    {
        AdminLog::create([
            'admin_id'    => $admin->id,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => $details,
        ]);
    }
}
