<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RvmMachine;
use App\Models\RecyclingSession;
use App\Models\Transaction;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AdminController extends Controller
{
    private const DEFAULT_REWARD_CONFIG = [
        'plastic'  => 5,
        'aluminum' => 8,
        'glass'    => 5,
        'paper'    => 3,
    ];

    private function loadRewardConfig(): array
    {
        $path = storage_path('app/reward_config.json');
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            if ($decoded && is_array($decoded)) return $decoded;
        }
        return self::DEFAULT_REWARD_CONFIG;
    }

    private const ALLOWED_PER_PAGE = [15, 25, 50, 100, 200];
    private const CACHE_TTL_SECONDS = 15;

    private function resolvePerPage(Request $request): int
    {
        $value = (int) $request->query('per_page', 15);
        return in_array($value, self::ALLOWED_PER_PAGE, true) ? $value : 15;
    }

    // Dashboard stats
    public function dashboard(Request $request): JsonResponse
    {
        $stats = Cache::remember('admin:dashboard:stats', self::CACHE_TTL_SECONDS, fn () => $this->computeDashboardStats());

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    private function computeDashboardStats(): array
    {
        $totalUsers        = User::count();
        $totalMachines     = RvmMachine::count();
        $activeSessions    = RecyclingSession::where('status', 'active')->count();
        $totalTransactions = Transaction::count();
        $totalPointsGiven  = Transaction::where('is_valid', 1)->sum('points_earned');
        $activeUsers       = User::where('role', 'user')->whereHas('recyclingSessions')->count();
        $redemptionsToday  = Transaction::where('is_valid', 1)->whereDate('created_at', today())->count();

        $materialCounts = Transaction::where('is_valid', 1)
            ->selectRaw('material_selected, COUNT(*) as count')
            ->groupBy('material_selected')
            ->pluck('count', 'material_selected');

        $totalCarbonSaved = 0.0;
        foreach ($materialCounts as $material => $count) {
            $totalCarbonSaved += $count * \App\Services\CarbonService::forMaterial($material);
        }

        $materialStats = Transaction::where('is_valid', 1)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('material_selected, COUNT(*) as count, SUM(points_earned) as total_points')
            ->groupBy('material_selected')
            ->get()
            ->map(function ($row) {
                $row->total_carbon_kg = round($row->count * \App\Services\CarbonService::forMaterial($row->material_selected), 3);
                return $row;
            });

        $recentSessions = RecyclingSession::with(['user', 'machine'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'session_code'  => $s->session_code,
                'user_name'     => $s->user?->name ?? 'Guest',
                'machine_name'  => $s->machine?->name ?? '—',
                'status'        => $s->status,
                'points_earned' => $s->points_earned,
                'started_at'    => $s->started_at,
            ]);

        $fullBins = RvmMachine::where('aluminum_level', '>=', 90)
            ->orWhere('plastic_level', '>=', 90)
            ->orWhere('glass_level', '>=', 90)
            ->orWhere('paper_level', '>=', 90)
            ->get(['id','name','aluminum_level','plastic_level','glass_level','paper_level']);

        return [
                'total_users'          => $totalUsers,
                'total_machines'       => $totalMachines,
                'active_sessions'      => $activeSessions,
                'total_transactions'   => $totalTransactions,
                'total_points_given'   => $totalPointsGiven,
                'total_carbon_saved_kg'=> round($totalCarbonSaved, 2),
                'active_users'         => $activeUsers,
                'redemptions_today'    => $redemptionsToday,
                'material_stats'       => $materialStats,
                'recent_sessions'      => $recentSessions,
                'full_bins'            => $fullBins,
        ];
    }

    // Users management
    public function users(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = User::orderByDesc('total_points');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($this->resolvePerPage($request));

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
        $validated = $request->validate([
            'machine_code'  => 'required|string|max:50|unique:rvm_machines,machine_code',
            'name'          => 'required|string|max:100',
            'location_name' => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'status'        => 'nullable|in:active,inactive,maintenance',
        ]);

        $machine = RvmMachine::create([
            'machine_code'  => $validated['machine_code'],
            'name'          => $validated['name'],
            'location_name' => $validated['location_name'] ?? null,
            'latitude'      => isset($validated['latitude']) && $validated['latitude'] !== '' ? $validated['latitude'] : null,
            'longitude'     => isset($validated['longitude']) && $validated['longitude'] !== '' ? $validated['longitude'] : null,
            'status'        => $validated['status'] ?? 'active',
            'aluminum_level'=> 0,
            'plastic_level' => 0,
            'glass_level'   => 0,
            'paper_level'   => 0,
        ]);

        $this->log($request->user(), 'create_machine', 'machine', $machine->id, "Created machine: {$machine->name}");
        return response()->json(['success' => true, 'machine' => $machine], 201);
    }

    public function updateMachine(Request $request, int $id): JsonResponse
    {
        $machine = RvmMachine::find($id);
        if (!$machine) return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:100',
            'location_name' => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'status'        => 'nullable|in:active,inactive,maintenance',
        ]);

        $machine->update([
            'name'          => $validated['name']          ?? $machine->name,
            'location_name' => array_key_exists('location_name', $validated) ? $validated['location_name'] : $machine->location_name,
            'latitude'      => array_key_exists('latitude',  $validated) && $validated['latitude']  !== '' ? $validated['latitude']  : $machine->latitude,
            'longitude'     => array_key_exists('longitude', $validated) && $validated['longitude'] !== '' ? $validated['longitude'] : $machine->longitude,
            'status'        => $validated['status'] ?? $machine->status,
        ]);

        $this->log($request->user(), 'update_machine', 'machine', $id, "Updated machine: {$machine->name}");
        return response()->json(['success' => true, 'machine' => $machine->fresh()]);
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

        $request->validate([
            'aluminum_level' => 'sometimes|integer|min:0|max:100',
            'plastic_level'  => 'sometimes|integer|min:0|max:100',
            'glass_level'    => 'sometimes|integer|min:0|max:100',
            'paper_level'    => 'sometimes|integer|min:0|max:100',
        ]);

        $machine->update($request->only(['aluminum_level', 'plastic_level', 'glass_level', 'paper_level']));
        $this->log($request->user(), 'update_bin_levels', 'machine', $machine->id, "Bin levels updated for {$machine->name}");
        return response()->json(['success' => true, 'machine' => $machine]);
    }

    // Sessions & Transactions
    public function allSessions(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = RecyclingSession::with(['user', 'machine'])->latest();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('machine', fn ($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        $sessions = $query->paginate($this->resolvePerPage($request));
        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function allTransactions(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $query = Transaction::with(['user', 'machine', 'session'])->latest();

        if ($status === 'valid')    $query->where('is_valid', 1);
        if ($status === 'rejected') $query->where('is_valid', 0);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('material_selected', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $transactions = $query->paginate($this->resolvePerPage($request));
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

    // Reward Points Configuration
    public function getRewardConfig(): JsonResponse
    {
        return response()->json(['success' => true, 'config' => $this->loadRewardConfig()]);
    }

    public function updateRewardConfig(Request $request): JsonResponse
    {
        $request->validate([
            'plastic'  => 'required|integer|min:0|max:9999',
            'aluminum' => 'required|integer|min:0|max:9999',
            'glass'    => 'required|integer|min:0|max:9999',
            'paper'    => 'required|integer|min:0|max:9999',
        ]);

        $config = $request->only(['plastic', 'aluminum', 'glass', 'paper']);
        file_put_contents(storage_path('app/reward_config.json'), json_encode($config));
        $this->log($request->user(), 'update_reward_config', 'system', 0, 'Updated reward points configuration');

        return response()->json(['success' => true, 'config' => $config]);
    }

    // Notify that a physical collection has been requested for full bins —
    // deliberately does NOT touch bin levels. A bin isn't actually empty just
    // because someone was asked to come empty it; only updateBinLevels() (once
    // a bin has genuinely been emptied) should zero it out. The dashboard's
    // "Dismiss Alerts" button is UI-only for the same reason and has no
    // backend endpoint at all.
    public function requestBinCollection(Request $request): JsonResponse
    {
        $machines = RvmMachine::where('aluminum_level', '>=', 90)
            ->orWhere('plastic_level', '>=', 90)
            ->orWhere('glass_level', '>=', 90)
            ->orWhere('paper_level', '>=', 90)
            ->get();

        $this->log($request->user(), 'request_bin_collection', 'system', 0, "Collection requested for {$machines->count()} machine(s)");
        return response()->json(['success' => true, 'message' => 'Bin collection request sent.', 'affected' => $machines->count()]);
    }

    // 7-day daily collection trend + category breakdown + today overview
    public function chartData(): JsonResponse
    {
        $data = Cache::remember('admin:dashboard:chart-data', self::CACHE_TTL_SECONDS, fn () => $this->computeChartData());

        return response()->json(array_merge(['success' => true], $data));
    }

    private function computeChartData(): array
    {
        $days   = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
        $labels = $days->map(fn($d) => \Carbon\Carbon::parse($d)->format('D'))->values();

        $materials = ['plastic', 'aluminum', 'paper', 'glass'];

        $raw = \App\Models\Transaction::where('is_valid', 1)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, material_selected, COUNT(*) as count')
            ->groupBy('day', 'material_selected')
            ->get()
            ->groupBy('day');

        $datasets = [];
        foreach ($materials as $mat) {
            $datasets[$mat] = $days->map(fn($d) =>
                round(($raw->get($d)?->firstWhere('material_selected', $mat)?->count ?? 0) * \App\Services\CarbonService::forMaterial($mat), 3)
            )->values();
        }

        // Category breakdown totals (kg CO2e saved, all-time, per material)
        $breakdownCounts = \App\Models\Transaction::where('is_valid', 1)
            ->selectRaw('material_selected, COUNT(*) as count')
            ->groupBy('material_selected')
            ->pluck('count', 'material_selected');

        // Today vs yesterday counts
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todayCounts = \App\Models\Transaction::where('is_valid', 1)
            ->whereDate('created_at', $today)
            ->selectRaw('material_selected, COUNT(*) as cnt')
            ->groupBy('material_selected')
            ->pluck('cnt', 'material_selected');

        $yesterdayCounts = \App\Models\Transaction::where('is_valid', 1)
            ->whereDate('created_at', $yesterday)
            ->selectRaw('material_selected, COUNT(*) as cnt')
            ->groupBy('material_selected')
            ->pluck('cnt', 'material_selected');

        $overview = [];
        foreach ($materials as $mat) {
            $t = (int) ($todayCounts[$mat]     ?? 0);
            $y = (int) ($yesterdayCounts[$mat] ?? 0);
            $pct = $y > 0 ? round((($t - $y) / $y) * 100, 1) : ($t > 0 ? 100 : 0);
            $overview[$mat] = ['today' => $t, 'yesterday' => $y, 'pct' => $pct];
        }

        return [
            'labels'    => $labels,
            'datasets'  => $datasets,
            'breakdown' => [
                'plastic'  => round(($breakdownCounts['plastic']  ?? 0) * \App\Services\CarbonService::forMaterial('plastic'), 3),
                'aluminum' => round(($breakdownCounts['aluminum'] ?? 0) * \App\Services\CarbonService::forMaterial('aluminum'), 3),
                'paper'    => round(($breakdownCounts['paper']    ?? 0) * \App\Services\CarbonService::forMaterial('paper'), 3),
                'glass'    => round(($breakdownCounts['glass']    ?? 0) * \App\Services\CarbonService::forMaterial('glass'), 3),
            ],
            'overview'  => $overview,
        ];
    }

    // Export all transactions as a formatted Excel workbook (bordered table,
    // auto-sized columns — a plain CSV has no styling capability at all).
    public function exportExcel(Request $request)
    {
        $transactions = Transaction::with(['user', 'machine'])->latest()->get();
        $filename = 'rvm_report_' . now()->format('Y-m-d') . '.xlsx';
        $this->log($request->user(), 'export_csv', 'transactions', 0, "Exported {$transactions->count()} transactions");

        $headers = ['ID', 'Time', 'User', 'Machine', 'Material', 'AI Detected', 'AI Confidence %', 'Valid', 'Carbon Saved (kg)', 'Points Earned', 'Status'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($transactions as $t) {
            $sheet->fromArray([
                $t->id,
                $t->created_at?->toDateTimeString(),
                $t->user?->name ?? 'Guest',
                $t->machine?->name ?? '—',
                $t->material_selected,
                $t->ai_detected_type ?? '—',
                round(($t->ai_confidence ?? 0) * 100),
                $t->is_valid ? 'Valid' : 'Rejected',
                $t->is_valid ? \App\Services\CarbonService::forMaterial($t->material_selected) : 0.0,
                $t->points_earned,
                $t->is_valid ? 'OK' : 'REJECTED',
            ], null, "A{$row}");
            $row++;
        }

        $lastCol = chr(ord('A') + count($headers) - 1); // 11 headers -> 'K'
        $lastRow = max($row - 1, 1);
        $range = "A1:{$lastCol}{$lastRow}";

        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Row height is intentionally left unset — Excel auto-fits row height
        // to content by default, so there's nothing to configure for that.

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function log(User $admin, string $action, string $targetType, int $targetId, string $details): void
    {
        AdminLog::create([
            'admin_id'    => $admin->id,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => json_encode(['message' => $details]),
        ]);
    }
}
