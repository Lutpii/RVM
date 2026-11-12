<?php

namespace App\Http\Controllers;

use App\Models\RvmMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MachineController extends Controller
{
    public function index(): JsonResponse
    {
        $machines = RvmMachine::where('status', 'active')
            ->select(['id','machine_code','name','location_name','latitude','longitude','status',
                      'aluminum_level','plastic_level','glass_level','paper_level',
                      // heartbeat state for the recycler view (U06); the key hash stays hidden
                      'last_seen_at','fault_active','is_demo'])
            ->get();

        return response()->json(['success' => true, 'machines' => $machines]);
    }

    public function show(int $id): JsonResponse
    {
        $machine = RvmMachine::find($id);
        if (!$machine) {
            return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);
        }
        return response()->json(['success' => true, 'machine' => $machine]);
    }

    // POST /hardware/heartbeat (machine.auth:required). The middleware already
    // stamped last_seen_at; this records the machine's fault state (A02/A05).
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fault'        => 'required|boolean',
            'fault_reason' => 'nullable|string|max:255',
        ]);

        /** @var RvmMachine $machine */
        $machine = $request->attributes->get('machine');
        $fault = (bool) $validated['fault'];

        if ($fault) {
            $machine->forceFill([
                'fault_active' => true,
                'fault_reason' => $validated['fault_reason'] ?? null,
                // keep the original start time while the same fault persists
                'fault_since'  => $machine->fault_active ? $machine->fault_since : now(),
            ]);
        } else {
            $machine->forceFill(['fault_active' => false, 'fault_reason' => null, 'fault_since' => null]);
        }
        $changed = $machine->isDirty('fault_active');
        $machine->saveQuietly();
        if ($changed) {
            \App\Support\SecurityLog::machineFault($machine, $fault, $fault ? ($validated['fault_reason'] ?? null) : null);
        }

        return response()->json(['success' => true, 'server_time' => now()->toIso8601String()]);
    }
}
