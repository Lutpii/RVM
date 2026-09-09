<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetectionLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_detection_log_can_be_created_with_minimal_fields(): void
    {
        $log = DetectionLog::create([
            'image_path'       => 'captures/abc123.jpg',
            'ai_detected_type' => 'plastic',
            'ai_confidence'    => 0.87,
            'is_guest'         => true,
        ]);

        $this->assertDatabaseHas('detection_logs', [
            'id'               => $log->id,
            'ai_detected_type' => 'plastic',
            'is_guest'         => 1,
            'is_mock'          => 0,
        ]);
        $this->assertNull($log->ground_truth_correct);
    }

    private static int $seq = 0;

    private function makeUser(array $overrides = []): User
    {
        self::$seq++;
        return User::create(array_merge([
            'name' => 'User ' . self::$seq,
            'email' => 'user' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'),
            'role' => 'user',
            'is_verified' => 1,
            'total_points' => 0,
        ], $overrides));
    }

    public function test_logged_in_classify_writes_one_detection_log(): void
    {
        $user = $this->makeUser();
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('classify')->once()->andReturn([
                'material' => 'aluminum', 'confidence' => 0.91, 'all_predictions' => [],
            ]);
        });

        $this->postJson('/api/transactions/classify', [
            'session_code' => $session->session_code,
            'image_path'   => 'captures/logged-in-test.jpg',
        ])->assertOk();

        $this->assertDatabaseHas('detection_logs', [
            'image_path'       => 'captures/logged-in-test.jpg',
            'ai_detected_type' => 'aluminum',
            'is_guest'         => 0,
            'is_mock'          => 0,
            'session_id'       => $session->id,
            'user_id'          => $user->id,
            'machine_id'       => $machine->id,
        ]);
    }

    public function test_guest_hardware_classify_writes_one_detection_log(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('classify')->once()->andReturn([
                'material' => 'glass', 'confidence' => 0.5, 'mock' => true,
            ]);
        });

        $this->postJson('/api/hardware/classify', [
            'image_path' => 'captures/guest-test.jpg',
        ])->assertOk();

        $this->assertDatabaseHas('detection_logs', [
            'image_path'       => 'captures/guest-test.jpg',
            'ai_detected_type' => 'glass',
            'is_guest'         => 1,
            'is_mock'          => 1,
            'session_id'       => null,
            'user_id'          => null,
        ]);
    }
}
