<?php

namespace App\Http\Controllers;

use App\Models\QrSession;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    // Generate QR code for RVM machine display
    public function generate(string $machineCode): JsonResponse
    {
        $machine = RvmMachine::where('machine_code', $machineCode)->first();

        if (!$machine) {
            return response()->json(['success' => false, 'message' => 'Machine not found.'], 404);
        }

        if ($machine->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Machine is not active.'], 400);
        }

        // Expire only genuinely time-expired pending QRs for this machine
        QrSession::where('machine_id', $machine->id)
            ->where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        // Generate new token — 8 uppercase chars, easy to type manually
        $token = strtoupper(Str::random(4));

        $qrSession = QrSession::create([
            'machine_id' => $machine->id,
            'qr_token'   => $token,
            'status'     => 'pending',
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Build scan URL — points to the Vue frontend /scan page
        $frontendUrl = rtrim(config('services.frontend_url', config('app.url')), '/');
        $scanUrl = "{$frontendUrl}/#/scan?token={$token}&machine={$machineCode}";

        // Generate QR image as SVG
        $qrSvg = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($scanUrl);

        return response()->json([
            'success'    => true,
            'token'      => $token,
            'scan_url'   => $scanUrl,
            'qr_svg'     => base64_encode($qrSvg),
            'machine'    => [
                'id'   => $machine->id,
                'name' => $machine->name,
                'code' => $machine->machine_code,
                'bins' => [
                    'aluminum' => $machine->aluminum_level,
                    'plastic'  => $machine->plastic_level,
                    'glass'    => $machine->glass_level,
                    'paper'    => $machine->paper_level,
                ],
            ],
            'expires_at' => $qrSession->expires_at,
        ]);
    }

    // Check QR status (polling from RVM display)
    public function status(string $token): JsonResponse
    {
        $qrSession = QrSession::with(['machine', 'scannedUser'])->where('qr_token', $token)->first();

        if (!$qrSession) {
            return response()->json(['success' => false, 'message' => 'QR token not found.'], 404);
        }

        // Only expire pending QRs — a scanned session keeps its kiosk_token valid
        if ($qrSession->status === 'pending' && Carbon::now()->isAfter($qrSession->expires_at)) {
            $qrSession->update(['status' => 'expired']);
        }

        $sessionData = null;
        if ($qrSession->status === 'scanned' && $qrSession->scanned_by) {
            $recyclingSession = RecyclingSession::with(['user', 'machine'])
                ->where('user_id', $qrSession->scanned_by)
                ->where('status', 'active')
                ->first();

            if ($recyclingSession) {
                $u = $recyclingSession->user;
                $m = $recyclingSession->machine;
                $sessionData = [
                    'session_code'   => $recyclingSession->session_code,
                    'user_name'      => $u->name,
                    'current_points' => $u->total_points,
                    'start_points'   => $recyclingSession->start_points,
                    'points_earned'  => $recyclingSession->points_earned,
                    'total_items'    => $recyclingSession->total_items,
                    'machine'        => [
                        'id'             => $m->id,
                        'name'           => $m->name,
                        'location'       => $m->location_name,
                        'aluminum_level' => $m->aluminum_level,
                        'plastic_level'  => $m->plastic_level,
                        'glass_level'    => $m->glass_level,
                        'paper_level'    => $m->paper_level,
                    ],
                    'started_at' => $recyclingSession->started_at,
                ];
            }
        }

        return response()->json([
            'success'          => true,
            'status'           => $qrSession->status,
            'user_name'        => $qrSession->scannedUser?->name,
            'theme_preference' => $qrSession->scannedUser?->theme_preference,
            'kiosk_token'      => $qrSession->kiosk_token,
            'session'          => $sessionData,
        ]);
    }

    // User scans QR code → link session
    public function scan(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        $qrSession = QrSession::with('machine')
            ->where('qr_token', $request->token)
            ->where('status', 'pending')
            ->first();

        if (!$qrSession) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR code.'], 400);
        }

        if (Carbon::now()->isAfter($qrSession->expires_at)) {
            $qrSession->update(['status' => 'expired']);
            return response()->json(['success' => false, 'message' => 'QR code has expired. Please scan a new one.'], 400);
        }

        $user = $request->user();
        $kioskToken = Str::random(64);
        $qrSession->update(['status' => 'scanned', 'scanned_by' => $user->id, 'kiosk_token' => $kioskToken]);

        $machine = $qrSession->machine;

        return response()->json([
            'success'    => true,
            'message'    => 'QR scanned successfully. Session ready.',
            'machine_id' => $machine->id,
            'machine'    => [
                'id'            => $machine->id,
                'name'          => $machine->name,
                'location'      => $machine->location_name,
                'aluminum_level'=> $machine->aluminum_level,
                'plastic_level' => $machine->plastic_level,
                'glass_level'   => $machine->glass_level,
                'paper_level'   => $machine->paper_level,
            ],
            'user'       => [
                'id'           => $user->id,
                'name'         => $user->name,
                'total_points' => $user->total_points,
            ],
        ]);
    }
}
