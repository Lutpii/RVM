<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RvmMachine;
use App\Models\RecyclingSession;
use App\Models\Transaction;
use App\Models\AdminLog;
use App\Models\DetectionLog;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\QrSession;
use App\Mail\BinCollectionRequested;
use App\Mail\FormalReportGenerated;
use App\Services\FormalReportService;
use App\Services\RewardConfigService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AdminController extends Controller
{
    private const ALLOWED_PER_PAGE = [15, 20, 25, 50, 100, 200];
    private const DETECTION_REVIEW_LABELS = [
        'aluminum', 'plastic', 'glass', 'paper', 'wood', 'metal', 'brick', 'other',
    ];
    private const CACHE_TTL_SECONDS = 15;

    private function resolvePerPage(Request $request): int
    {
        $value = (int) $request->query('per_page', 20);
        return in_array($value, self::ALLOWED_PER_PAGE, true) ? $value : 20;
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
    private const USER_SORTABLE_COLUMNS = [
        'id', 'name', 'email', 'total_points', 'role', 'is_verified', 'created_at',
    ];

    public function users(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        // Never interpolate the raw query param as a column name — resolve
        // it through this allowlist first, so an unrecognized or malicious
        // value can only ever fall back to the default sort.
        $sortColumn = in_array($request->query('sort'), self::USER_SORTABLE_COLUMNS, true)
            ? $request->query('sort')
            : 'id';
        $sortDirection = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        // The shared guest placeholder account (see User::guest()) isn't a
        // real person — hide it from user management, same as it's excluded
        // from anywhere users are browsed/managed individually.
        $query = User::where('email', '!=', User::GUEST_EMAIL)->orderBy($sortColumn, $sortDirection);
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

    // Kiosk maintenance shutdown — gated by an admin session (this middleware
    // group) AND physical presence: the qr_token must be the QR currently
    // live on that machine's own display (60s window, same rows QrController
    // generates for the ordinary recycling flow). Never touches that flow's
    // own /qr/scan — this only checks and consumes the token.
    public function maintainMachine(Request $request, int $id): JsonResponse
    {
        $machine = RvmMachine::find($id);
        if (!$machine) return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);

        $validated = $request->validate(['qr_token' => 'required|string']);

        $qrSession = QrSession::where('qr_token', $validated['qr_token'])
            ->where('machine_id', $machine->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$qrSession) {
            return response()->json([
                'success' => false,
                'message' => 'QR is invalid, expired, or for a different machine. Scan the code currently shown on the kiosk.',
            ], 400);
        }

        // Consume it immediately so it can't be replayed for a second maintenance
        // trigger (or picked up by the ordinary /qr/scan flow afterward).
        $qrSession->update(['status' => 'expired']);

        $this->log($request->user(), 'kiosk_maintenance', 'machine', $machine->id, "Triggered kiosk maintenance shutdown for machine: {$machine->name}");

        try {
            $response = Http::timeout(3)->post(config('services.kiosk_control.url'));
            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Kiosk control service responded with an error.'], 502);
            }
        } catch (\Throwable $e) {
            Log::error('[KioskMaintenance] failed to reach control service: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not reach the kiosk control service on this machine.'], 502);
        }

        return response()->json(['success' => true, 'message' => 'Kiosk closed for maintenance.']);
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

    /**
     * Shared by the list and CSV export so both honour the same date window and
     * review queue. An export that ignored the visible filters would quietly
     * mix pending and reviewed rows in the exhibition accuracy figures.
     */
    private function filterDetectionLogs(Builder $query, Request $request, ?string $statusOverride = null): Builder
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        $status = $statusOverride ?? $request->query('review_status', 'pending');

        if ($status === 'test') {
            return $query->where('is_mock', true);
        }

        // Mock rows are diagnostic data, not camera evidence. Keep them out of
        // the real review queue, accuracy history, and reviewed CSV exports.
        $query->where('is_mock', false);

        switch ($status) {
            case 'reviewed':
                $query->whereNotNull('ground_truth_correct');
                break;
            case 'correct':
                $query->where('ground_truth_correct', true);
                break;
            case 'incorrect':
                $query->where('ground_truth_correct', false);
                break;
            case 'all':
                break;
            default:
                $query->whereNull('ground_truth_correct');
        }

        return $query;
    }

    /**
     * Move a reviewed capture between the pending root and its review result
     * folder. The database path is updated by the caller only after the file
     * move succeeds, so History never points at the old location.
     */
    private function moveDetectionCapture(DetectionLog $log, ?bool $correct): string
    {
        $disk = Storage::disk('public');
        $source = str_replace('\\', '/', trim((string) $log->image_path));

        if ($source === '' || !str_starts_with($source, 'captures/') || !$disk->exists($source)) {
            throw new \RuntimeException('Detection capture is missing or outside the captures folder.');
        }

        $directory = match ($correct) {
            true => 'captures/correct',
            false => 'captures/incorrect',
            null => 'captures',
        };
        $target = $directory . '/' . basename($source);

        if ($source === $target) {
            return $source;
        }

        if ($disk->exists($target)) {
            $path = pathinfo($target);
            $extension = isset($path['extension']) ? '.' . $path['extension'] : '';
            $target = $directory . '/' . $path['filename'] . '-' . $log->id . '-' . now()->format('YmdHisv') . $extension;
        }

        if (!$disk->move($source, $target)) {
            throw new \RuntimeException('Detection capture could not be moved.');
        }

        return $target;
    }

    /**
     * Keep the filesystem move and database path in sync. If the database
     * update fails, make a best-effort move back to the original location.
     */
    private function updateDetectionReviewWithCaptureMove(DetectionLog $log, ?bool $correct, array $attributes): void
    {
        $disk = Storage::disk('public');
        $originalPath = $log->image_path;
        $newPath = $this->moveDetectionCapture($log, $correct);

        try {
            if (!$log->update(array_merge(['image_path' => $newPath], $attributes))) {
                throw new \RuntimeException('Detection review could not be saved.');
            }
        } catch (\Throwable $exception) {
            if ($newPath !== $originalPath && $disk->exists($newPath) && !$disk->exists($originalPath)) {
                $disk->move($newPath, $originalPath);
            }
            throw $exception;
        }
    }

    public function detectionLogs(Request $request): JsonResponse
    {
        $query = $this->filterDetectionLogs(
            DetectionLog::with(['user', 'machine', 'reviewer'])->latest(),
            $request
        );

        $logs = $query->paginate($this->resolvePerPage($request));
        return response()->json(['success' => true, 'detection_logs' => $logs]);
    }

    public function reviewDetectionLog(Request $request, int $id): JsonResponse
    {
        $log = DetectionLog::find($id);
        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Detection log not found.'], 404);
        }

        if ($request->boolean('undo')) {
            try {
                $this->updateDetectionReviewWithCaptureMove($log, null, [
                    'ground_truth_correct' => null,
                    'ground_truth_label'   => null,
                    'reviewed_by'          => null,
                    'reviewed_at'          => null,
                ]);
            } catch (\Throwable $exception) {
                Log::error('Failed to undo detection review capture move.', [
                    'detection_log_id' => $log->id,
                    'error' => $exception->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'The capture could not be returned to the pending folder.',
                ], 500);
            }

            return response()->json(['success' => true, 'detection_log' => $log->fresh('reviewer')]);
        }

        if ($log->is_mock || !$log->image_path || !Storage::disk('public')->exists($log->image_path)) {
            return response()->json([
                'success' => false,
                'message' => 'A real captured image is required before this detection can be reviewed.',
            ], 422);
        }

        $validated = $request->validate([
            'ground_truth_correct' => ['required', 'boolean'],
            'ground_truth_label'   => ['nullable', 'string', 'in:' . implode(',', self::DETECTION_REVIEW_LABELS)],
        ]);

        $correct = (bool) $validated['ground_truth_correct'];
        $predicted = strtolower(trim((string) $log->ai_detected_type));

        if ($correct) {
            if ($predicted === '' || $predicted === 'unknown' || !in_array($predicted, self::DETECTION_REVIEW_LABELS, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown predictions must be assigned an actual material.',
                ], 422);
            }
            $groundTruthLabel = $predicted;
        } else {
            $groundTruthLabel = $validated['ground_truth_label'] ?? null;
            if (!$groundTruthLabel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select the actual material for an incorrect prediction.',
                ], 422);
            }
            if ($groundTruthLabel === $predicted) {
                return response()->json([
                    'success' => false,
                    'message' => 'The actual material must differ from the AI prediction.',
                ], 422);
            }
        }

        // Deliberately no $this->log(...) here, unlike the other mutating admin
        // actions: an exhibition review pass marks hundreds of photos in one
        // sitting, which would bury every other entry in the 50-per-page
        // admin_logs view. reviewed_at on the row is the audit trail instead.
        try {
            $this->updateDetectionReviewWithCaptureMove($log, $correct, [
                'ground_truth_correct' => $correct,
                'ground_truth_label'   => $groundTruthLabel,
                'reviewed_by'          => $request->user()->id,
                'reviewed_at'          => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to move detection review capture.', [
                'detection_log_id' => $log->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The review could not be saved because the capture could not be moved.',
            ], 500);
        }

        return response()->json(['success' => true, 'detection_log' => $log->fresh('reviewer')]);
    }

    public function detectionLogImage(int $id)
    {
        $log = DetectionLog::find($id);
        if (!$log || !$log->image_path) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . $log->image_path);
        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        return response()->stream(function () use ($fullPath) {
            readfile($fullPath);
        }, 200, ['Content-Type' => $mimeType]);
    }

    // Separate from exportExcel() (transactions) — this is a different unit
    // of analysis: one row per detection event, not per completed transaction.
    public function exportDetectionLogs(Request $request)
    {
        $requestedStatus = $request->query('review_status', 'reviewed');
        $exportStatus = in_array($requestedStatus, ['reviewed', 'correct', 'incorrect'], true)
            ? $requestedStatus
            : 'reviewed';
        $logs = $this->filterDetectionLogs(
            DetectionLog::with('reviewer')->latest(),
            $request,
            $exportStatus
        )->get();
        $this->log($request->user(), 'export_csv', 'detection_logs', 0, "Exported {$logs->count()} detection logs");

        $filename = 'detection_reviews_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'image_path', 'ai_detected_type', 'ai_confidence', 'is_guest', 'is_mock',
                'ground_truth_correct', 'ground_truth_label', 'reviewed_by', 'reviewed_at', 'created_at',
            ]);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->image_path,
                    $log->ai_detected_type,
                    $log->ai_confidence,
                    $log->is_guest ? '1' : '0',
                    $log->is_mock ? '1' : '0',
                    is_null($log->ground_truth_correct) ? '' : ($log->ground_truth_correct ? '1' : '0'),
                    $log->ground_truth_label,
                    $log->reviewer?->name,
                    $log->reviewed_at?->toDateTimeString(),
                    $log->created_at?->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
    public function getRewardConfig(RewardConfigService $rewardConfig): JsonResponse
    {
        return response()->json(['success' => true, 'config' => $rewardConfig->load()]);
    }

    public function updateRewardConfig(Request $request, RewardConfigService $rewardConfig): JsonResponse
    {
        $request->validate([
            'plastic'  => 'required|integer|min:0|max:9999',
            'aluminum' => 'required|integer|min:0|max:9999',
            'glass'    => 'required|integer|min:0|max:9999',
            'paper'    => 'required|integer|min:0|max:9999',
        ]);

        $config = $request->only(['plastic', 'aluminum', 'glass', 'paper']);
        $rewardConfig->save($config);
        $this->log($request->user(), 'update_reward_config', 'system', 0, 'Updated reward points configuration');

        return response()->json(['success' => true, 'config' => $config]);
    }

    public function rewardItems(Request $request): JsonResponse
    {
        $items = RewardItem::latest()->paginate($this->resolvePerPage($request));
        return response()->json(['success' => true, 'reward_items' => $items]);
    }

    public function createRewardItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string',
            'category'     => 'nullable|string|max:50',
            'points_cost'  => 'required|integer|min:1',
            'stock'        => 'nullable|integer|min:0',
            'valid_from'   => 'nullable|date',
            'valid_until'  => 'nullable|date|after_or_equal:valid_from',
            'is_active'    => 'nullable|boolean',
            'image'        => 'nullable|file|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('reward-images', 'public')
            : null;

        $item = RewardItem::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category'    => $validated['category'] ?? null,
            'image_path'  => $imagePath,
            'points_cost' => $validated['points_cost'],
            'stock'       => $validated['stock'] ?? null,
            'valid_from'  => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $this->log($request->user(), 'create_reward_item', 'reward_item', $item->id, "Created reward item: {$item->name}");
        return response()->json(['success' => true, 'reward_item' => $item], 201);
    }

    public function updateRewardItem(Request $request, int $id): JsonResponse
    {
        $item = RewardItem::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Reward item not found.'], 404);

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:150',
            'description'  => 'nullable|string',
            'category'     => 'nullable|string|max:50',
            'points_cost'  => 'sometimes|required|integer|min:1',
            'stock'        => 'nullable|integer|min:0',
            'valid_from'   => 'nullable|date',
            'valid_until'  => 'nullable|date|after_or_equal:valid_from',
            'is_active'    => 'nullable|boolean',
            'image'        => 'nullable|file|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $oldImagePath = $item->image_path;
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('reward-images', 'public');
        }
        unset($validated['image']);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $item->update($validated);

        // Replacing the image leaves the old file with nothing referencing it —
        // delete it after the update succeeds, not before (a failed validate()
        // above must never orphan the still-in-use original).
        if ($request->hasFile('image') && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }
        $this->log($request->user(), 'update_reward_item', 'reward_item', $id, "Updated reward item: {$item->name}");
        return response()->json(['success' => true, 'reward_item' => $item->fresh()]);
    }

    public function deleteRewardItem(Request $request, int $id): JsonResponse
    {
        $item = RewardItem::find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Reward item not found.'], 404);

        $this->log($request->user(), 'delete_reward_item', 'reward_item', $id, "Deleted reward item: {$item->name}");
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Reward item deleted.']);
    }

    public function redemptions(Request $request): JsonResponse
    {
        $redemptions = RewardRedemption::with('user')
            ->orderByDesc('created_at')
            ->paginate($this->resolvePerPage($request));

        return response()->json(['success' => true, 'redemptions' => $redemptions]);
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

        if ($machines->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No bins are at or above 90%.',
                'affected' => 0,
            ], 422);
        }

        $this->log($request->user(), 'request_bin_collection', 'system', 0, "Collection requested for {$machines->count()} machine(s)");

        // Best-effort — a flaky mail provider shouldn't block the admin's request
        // from being logged and acknowledged, since that's this endpoint's real
        // job; the audit-log entry above is the source of truth either way.
        $notificationEmail = config('services.pbt.notification_email');
        if ($machines->isNotEmpty() && $notificationEmail) {
            try {
                Mail::to($notificationEmail)->send(new BinCollectionRequested($machines));
            } catch (\Throwable $e) {
                Log::warning('Bin collection notification email failed to send', ['error' => $e->getMessage()]);
            }
        }

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

    // Export a formal report (header, per-machine summary, transaction
    // details — see FormalReportService) as an Excel workbook, optionally
    // filtered to a date range. Shares its report-building logic with
    // emailExcelReport() below so both always produce identical reports.
    public function exportExcel(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $spreadsheet = (new FormalReportService())->build($dateFrom, $dateTo);
        $filename = 'rvm_report_' . now()->format('Y-m-d') . '.xlsx';
        $this->log($request->user(), 'export_csv', 'transactions', 0, "Exported formal report ({$dateFrom} to {$dateTo})");

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // Builds the same formal report as exportExcel() but delivers it as an
    // email attachment instead of a browser download — for sending straight
    // to an external recipient (e.g. SWCorp) without a manual download/
    // re-upload step.
    public function emailExcelReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        $spreadsheet = (new FormalReportService())->build($dateFrom, $dateTo);
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $xlsxContents = ob_get_clean();

        $filename = 'rvm_report_' . now()->format('Y-m-d') . '.xlsx';
        $periodLabel = ($dateFrom ?: 'earliest') . ' to ' . ($dateTo ?: 'latest');

        // Unlike requestBinCollection()'s best-effort notification (where the
        // logged request is the real deliverable), sending the report IS this
        // endpoint's entire job — a swallowed failure here would leave an
        // admin believing SWCorp received a report that never sent.
        try {
            Mail::to($validated['email'])->send(new FormalReportGenerated($periodLabel, $xlsxContents, $filename));
        } catch (\Throwable $e) {
            Log::warning('Formal report email failed to send', ['error' => $e->getMessage(), 'to' => $validated['email']]);
            return response()->json(['success' => false, 'message' => 'Failed to send the report email. Please check the address and try again.'], 502);
        }

        $this->log($request->user(), 'email_formal_report', 'transactions', 0, "Emailed formal report ({$periodLabel}) to {$validated['email']}");

        return response()->json(['success' => true, 'message' => 'Report emailed to ' . $validated['email'] . '.']);
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
