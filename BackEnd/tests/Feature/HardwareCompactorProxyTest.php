<?php

namespace Tests\Feature;

use App\Services\AiService;
use Tests\TestCase;

class HardwareCompactorProxyTest extends TestCase
{
    public function test_state_passes_the_compactor_state_through(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('state')->once()->with('abc')->andReturn([
                'status' => 200,
                'body'   => ['profile' => '2bin', 'chamber_material' => 'aluminum', 'chamber_count' => 2,
                             'job' => ['id' => 'abc', 'status' => 'dropped']],
            ]);
        });

        $this->getJson('/api/hardware/state?job=abc')->assertOk()->assertJson([
            'profile' => '2bin', 'chamber_material' => 'aluminum', 'job' => ['status' => 'dropped'],
        ]);
    }

    public function test_state_is_legacy_when_the_service_is_down_or_4bin(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('state')->twice()->andReturn(null, ['status' => 404, 'body' => []]);
        });

        $this->getJson('/api/hardware/state')->assertOk()->assertExactJson(['profile' => 'legacy']);
        $this->getJson('/api/hardware/state')->assertOk()->assertExactJson(['profile' => 'legacy']);
    }

    public function test_deposit_passes_accepted_and_rejected_results_through(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('deposit')->once()->with('aluminum', false)->andReturn([
                'status' => 200, 'body' => ['accepted' => true, 'job_id' => 'j1', 'will_flush' => false, 'eta_seconds' => 6],
            ]);
            $mock->shouldReceive('deposit')->once()->with('paper', true)->andReturn([
                'status' => 400, 'body' => ['accepted' => false, 'reason' => 'not_accepted'],
            ]);
        });

        $this->postJson('/api/hardware/deposit', ['material' => 'aluminum', 'allow_flush' => false])
            ->assertOk()->assertJson(['accepted' => true, 'job_id' => 'j1']);
        $this->postJson('/api/hardware/deposit', ['material' => 'paper', 'allow_flush' => true])
            ->assertStatus(400)->assertJson(['reason' => 'not_accepted']);
    }

    public function test_deposit_is_503_when_the_service_is_down_or_rejects_the_api_key(): void
    {
        // A 401 must never reach the browser: services/api.js treats 401 as
        // "logged out" and would clear the kiosk's auth.
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('deposit')->twice()->andReturn(null, ['status' => 401, 'body' => ['error' => 'Unauthorized']]);
        });

        foreach ([1, 2] as $_) {
            $this->postJson('/api/hardware/deposit', ['material' => 'plastic', 'allow_flush' => false])
                ->assertStatus(503)->assertExactJson(['success' => false, 'error' => 'machine_unavailable']);
        }
    }

    public function test_deposit_validates_its_input(): void
    {
        $this->mock(AiService::class, fn ($mock) => $mock->shouldNotReceive('deposit'));

        $this->postJson('/api/hardware/deposit', ['material' => 'plastic'])->assertStatus(422);
        $this->postJson('/api/hardware/deposit', ['allow_flush' => true])->assertStatus(422);
    }

    public function test_flush_and_flap_check_pass_through(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('flush')->once()->andReturn(['status' => 200, 'body' => ['job_id' => 'f1', 'eta_seconds' => 40]]);
            $mock->shouldReceive('flapCheck')->once()->andReturn(['status' => 200, 'body' => ['empty' => true]]);
        });

        $this->postJson('/api/hardware/flush')->assertOk()->assertJson(['job_id' => 'f1']);
        $this->getJson('/api/hardware/flap-check')->assertOk()->assertExactJson(['empty' => true]);
    }
}
