<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
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
}
