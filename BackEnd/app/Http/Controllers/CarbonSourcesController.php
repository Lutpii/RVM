<?php

namespace App\Http\Controllers;

use App\Services\CarbonService;
use Illuminate\Http\JsonResponse;

class CarbonSourcesController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['success' => true, 'sources' => CarbonService::sources()]);
    }
}
