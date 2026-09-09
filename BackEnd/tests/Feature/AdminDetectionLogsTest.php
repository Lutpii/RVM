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

    /**
     * created_at is not mass-assignable (DetectionLog only lists the detection
     * fields in $fillable), so backdating has to bypass fill() and set the
     * attribute directly. save() on an existing row only touches updated_at.
     */
    private function makeLogDated(string $date, array $overrides = []): DetectionLog
    {
        $log = DetectionLog::create(array_merge([
            'image_path' => 'captures/dated.jpg', 'ai_detected_type' => 'plastic', 'is_guest' => true,
        ], $overrides));
        $log->created_at = $date;
        $log->save();

        return $log;
    }

    public function test_listing_detection_logs_respects_the_date_filter(): void
    {
        $this->actingAsAdmin();
        $this->makeLogDated('2026-09-01 10:00:00', ['image_path' => 'captures/old.jpg']);
        $this->makeLogDated('2026-09-08 10:00:00', ['image_path' => 'captures/recent.jpg']);

        $response = $this->getJson('/api/admin/detection-logs?date_from=2026-09-05&date_to=2026-09-09')->assertOk();

        $response->assertJsonCount(1, 'detection_logs.data');
        $response->assertJsonPath('detection_logs.data.0.image_path', 'captures/recent.jpg');
    }

    public function test_export_respects_the_date_filter(): void
    {
        $this->actingAsAdmin();
        $this->makeLogDated('2026-09-01 10:00:00', ['image_path' => 'captures/old.jpg']);
        $this->makeLogDated('2026-09-08 10:00:00', ['image_path' => 'captures/recent.jpg']);

        $response = $this->get('/api/admin/detection-logs/export?date_from=2026-09-05&date_to=2026-09-09')->assertOk();

        $lines = array_filter(explode("\n", trim($response->streamedContent())));
        $this->assertCount(2, $lines, 'Expected a header row plus exactly one in-range data row');
        $this->assertSame('captures/recent.jpg', str_getcsv($lines[1])[0]);
    }

    /**
     * The review gallery fires one image request per row, so the generic 60/min
     * 'api' limit would 429 an admin halfway through a single page. Asserting
     * the effective header (not just that the route carries the middleware)
     * catches the easy mistake of adding throttle:detection-review while the
     * api middleware group's tighter throttle:api still wraps it.
     */
    public function test_detection_log_routes_use_the_generous_review_throttle(): void
    {
        $this->actingAsAdmin();
        $log = DetectionLog::create(['image_path' => 'captures/a.jpg', 'is_guest' => true]);

        $this->getJson('/api/admin/detection-logs')->assertHeader('X-RateLimit-Limit', 600);
        $this->get('/api/admin/detection-logs/export')->assertHeader('X-RateLimit-Limit', 600);
        $this->patchJson("/api/admin/detection-logs/{$log->id}", ['ground_truth_correct' => true])
            ->assertHeader('X-RateLimit-Limit', 600);
    }

    public function test_non_admin_cannot_list_detection_logs(): void
    {
        $user = $this->makeUser(); // default role is 'user', not 'admin'
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/admin/detection-logs')->assertStatus(403);
    }

    public function test_admin_can_mark_a_detection_log_correct(): void
    {
        $this->actingAsAdmin();
        $log = DetectionLog::create(['image_path' => 'captures/a.jpg', 'ai_detected_type' => 'plastic', 'is_guest' => true]);

        $response = $this->patchJson("/api/admin/detection-logs/{$log->id}", ['ground_truth_correct' => true])
            ->assertOk();

        $response->assertJsonPath('detection_log.ground_truth_correct', true);
        $this->assertNotNull($log->fresh()->reviewed_at);
    }

    public function test_reviewing_a_missing_detection_log_returns_404(): void
    {
        $this->actingAsAdmin();

        $this->patchJson('/api/admin/detection-logs/999999', ['ground_truth_correct' => false])
            ->assertStatus(404);
    }

    public function test_admin_can_fetch_a_detection_log_image(): void
    {
        $this->actingAsAdmin();
        // detectionLogImage() reads via storage_path() directly (matching
        // AiService::classify()'s existing convention), not the Storage
        // facade's fake-able path resolution — so this writes a real file
        // and must clean it up itself rather than relying on Storage::fake().
        \Illuminate\Support\Facades\Storage::disk('public')->put('captures/review-test.jpg', 'fake-jpeg-bytes');
        $log = DetectionLog::create(['image_path' => 'captures/review-test.jpg', 'is_guest' => true]);

        try {
            $response = $this->get("/api/admin/detection-logs/{$log->id}/image")->assertOk();
            $this->assertSame('fake-jpeg-bytes', $response->streamedContent());
        } finally {
            \Illuminate\Support\Facades\Storage::disk('public')->delete('captures/review-test.jpg');
        }
    }

    public function test_missing_image_file_returns_404(): void
    {
        $this->actingAsAdmin();
        $log = DetectionLog::create(['image_path' => 'captures/does-not-exist.jpg', 'is_guest' => true]);

        $this->get("/api/admin/detection-logs/{$log->id}/image")->assertStatus(404);
    }

    public function test_export_produces_a_csv_with_expected_columns(): void
    {
        $this->actingAsAdmin();
        DetectionLog::create([
            'image_path' => 'captures/a.jpg', 'ai_detected_type' => 'plastic',
            'ai_confidence' => 0.8, 'is_guest' => true, 'ground_truth_correct' => true,
        ]);

        $response = $this->get('/api/admin/detection-logs/export')->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $lines = array_filter(explode("\n", trim($response->streamedContent())));
        $header = str_getcsv($lines[0]);
        $row    = str_getcsv($lines[1]);

        $this->assertSame(
            ['image_path', 'ai_detected_type', 'ai_confidence', 'is_guest', 'is_mock', 'ground_truth_correct', 'created_at'],
            $header
        );
        $this->assertSame('captures/a.jpg', $row[0]);
        $this->assertSame('plastic', $row[1]);
        $this->assertSame('1', $row[3]); // is_guest
        $this->assertSame('1', $row[5]); // ground_truth_correct
    }
}
