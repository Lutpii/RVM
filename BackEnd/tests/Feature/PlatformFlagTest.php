<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlatformFlagTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('platform')->get('/platform/_ping', fn () => ['ok' => true]);
    }

    public function test_platform_route_is_404_when_disabled(): void
    {
        config(['platform.enabled' => false]);

        $this->getJson('/platform/_ping')
            ->assertNotFound()
            ->assertExactJson(['success' => false]);
    }

    public function test_platform_route_works_when_enabled(): void
    {
        config(['platform.enabled' => true]);

        $this->getJson('/platform/_ping')->assertOk()->assertExactJson(['ok' => true]);
    }
}
