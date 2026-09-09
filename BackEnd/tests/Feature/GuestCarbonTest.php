<?php

namespace Tests\Feature;

use App\Services\AiService;
use Tests\TestCase;

class GuestCarbonTest extends TestCase
{
    public function test_hardware_classify_includes_carbon_saved_for_the_detected_material(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('classify')->once()->andReturn([
                'material' => 'aluminum',
                'confidence' => 0.9,
                'all_predictions' => [],
            ]);
        });

        $response = $this->postJson('/api/hardware/classify', [])->assertOk();

        $response->assertJson([
            'success' => true,
            'ai_detected' => 'aluminum',
            'carbon_saved' => 0.226,
        ]);
    }

    public function test_hardware_classify_reports_zero_carbon_for_unknown_material(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('classify')->once()->andReturn([
                'material' => 'unknown',
                'confidence' => 0,
                'all_predictions' => [],
            ]);
        });

        $response = $this->postJson('/api/hardware/classify', [])->assertOk();

        $response->assertJson([
            'success' => true,
            'ai_detected' => 'unknown',
            'carbon_saved' => 0.0,
        ]);
    }
}
