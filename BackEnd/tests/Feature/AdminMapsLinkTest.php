<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMapsLinkTest extends TestCase
{
    use RefreshDatabase;

    private const LONG = 'https://www.google.com/maps/place/Main+Lobby/@3.54,103.42,18z/data=!3d3.5397744!4d103.4301089';

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::create(['name' => 'Admin', 'email' => 'maps-admin@example.test', 'role' => 'admin', 'is_verified' => true]));
    }

    public function test_short_link_is_followed_to_the_google_maps_page(): void
    {
        Http::fake(['maps.app.goo.gl/*' => Http::response('', 302, ['Location' => self::LONG])]);

        $this->postJson('/api/admin/maps/resolve', ['url' => 'https://maps.app.goo.gl/Pq2MCK3qt7nH9YYeA'])
            ->assertOk()
            ->assertJsonPath('latitude', 3.5397744)
            ->assertJsonPath('longitude', 103.4301089)
            ->assertJsonPath('place', 'Main Lobby');
    }

    public function test_long_link_needs_no_network(): void
    {
        Http::fake();
        $this->postJson('/api/admin/maps/resolve', ['url' => self::LONG])->assertOk()->assertJsonPath('latitude', 3.5397744);
        Http::assertNothingSent();
    }

    public function test_non_google_links_and_redirects_away_from_google_are_refused(): void
    {
        Http::fake(['maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'http://169.254.169.254/latest'])]);

        $this->postJson('/api/admin/maps/resolve', ['url' => 'https://evil.example/maps/@3.5,103.4,17z'])->assertUnprocessable();
        $this->postJson('/api/admin/maps/resolve', ['url' => 'http://maps.app.goo.gl/abc'])->assertUnprocessable();
        $this->postJson('/api/admin/maps/resolve', ['url' => 'https://maps.app.goo.gl/abc'])->assertUnprocessable();
        Http::assertSentCount(1); // the internal address was never requested
    }

    public function test_non_admin_is_forbidden(): void
    {
        Sanctum::actingAs(User::create(['name' => 'U', 'email' => 'maps-user@example.test', 'role' => 'user', 'is_verified' => true]));
        $this->postJson('/api/admin/maps/resolve', ['url' => self::LONG])->assertForbidden();
    }
}
