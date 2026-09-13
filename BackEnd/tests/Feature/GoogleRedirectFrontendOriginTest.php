<?php
// BackEnd/tests/Feature/GoogleRedirectFrontendOriginTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleRedirectFrontendOriginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_private_ip_frontend_origin_is_carried_through(): void
    {
        $res = $this->getJson('/api/auth/google/redirect?frontend=' . urlencode('https://192.168.137.5:5173'));

        $res->assertOk();
        $this->assertStringContainsString('frontend=' . urlencode('https://192.168.137.5:5173'), $res->json('url'));
    }

    public function test_localhost_frontend_origin_is_carried_through(): void
    {
        $res = $this->getJson('/api/auth/google/redirect?frontend=' . urlencode('https://localhost:5173'));

        $res->assertOk();
        $this->assertStringContainsString('frontend=', $res->json('url'));
    }

    public function test_a_public_frontend_origin_is_dropped(): void
    {
        $res = $this->getJson('/api/auth/google/redirect?frontend=' . urlencode('https://evil.example.com'));

        $res->assertOk();
        $this->assertStringNotContainsString('frontend=', $res->json('url'));
    }

    public function test_missing_frontend_origin_is_fine(): void
    {
        $res = $this->getJson('/api/auth/google/redirect');

        $res->assertOk();
        $this->assertStringNotContainsString('frontend=', $res->json('url'));
    }
}
