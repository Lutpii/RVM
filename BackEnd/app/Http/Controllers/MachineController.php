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
                      'aluminum_level','plastic_level','glass_level','paper_level'])
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
}
