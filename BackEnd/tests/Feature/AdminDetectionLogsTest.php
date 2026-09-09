<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDetectionLogsTest extends TestCase
{
    use RefreshDatabase;

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

    private function actingAsAdmin(): User
    {
        $admin = $this->makeUser(['name' => 'Admin', 'role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    public function test_admin_can_list_detection_logs(): void
    {
        $this->actingAsAdmin();
        DetectionLog::create(['image_path' => 'captures/a.jpg', 'ai_detected_type' => 'plastic', 'is_guest' => true]);
        DetectionLog::create(['image_path' => 'captures/b.jpg', 'ai_detected_type' => 'glass', 'is_guest' => false]);

        $response = $this->getJson('/api/admin/detection-logs')->assertOk();

        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'detection_logs.data');
    }

    public function test_non_admin_cannot_list_detection_logs(): void
    {
        $user = $this->makeUser(); // default role is 'user', not 'admin'
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/admin/detection-logs')->assertStatus(403);
    }
}
