<?php

namespace Tests\Feature;

use App\Http\Controllers\QrController;
use App\Models\QrSession;
use App\Models\RvmMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QrLifetimeTest extends TestCase
{
    use RefreshDatabase;

    // Must match QR_LIFETIME_SECONDS in FrontEnd/src/utils/qrToken.js: the kiosk
    // shows each QR for exactly this long before replacing it.
    public function test_a_kiosk_qr_is_valid_for_100_seconds(): void
    {
        Carbon::setTestNow('2026-10-03 10:00:00');
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-QR-' . uniqid(), 'name' => 'QR Machine',
            'location_name' => 'Lobby', 'status' => 'active',
        ]);

        $this->getJson('/api/qr/generate/' . $machine->machine_code)->assertOk();

        $this->assertSame(100, QrController::QR_LIFETIME_SECONDS);
        $qr = QrSession::where('machine_id', $machine->id)->latest('id')->firstOrFail();
        $this->assertEquals(Carbon::parse('2026-10-03 10:01:40'), $qr->expires_at);
        Carbon::setTestNow();
    }
}
