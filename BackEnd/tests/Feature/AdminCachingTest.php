<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCachingTest extends TestCase
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

    public function test_dashboard_stats_second_call_is_served_from_cache(): void
    {
        $this->actingAsAdmin();
        $this->makeUser();

        DB::enableQueryLog();
        $this->getJson('/api/admin/stats')->assertOk();
        $firstCallQueries = count(DB::getQueryLog());
        $this->assertGreaterThan(0, $firstCallQueries, 'first call should hit the database');

        DB::flushQueryLog();
        $this->getJson('/api/admin/stats')->assertOk();
        $secondCallQueries = count(DB::getQueryLog());

        $this->assertSame(0, $secondCallQueries, 'second call within the TTL should be served entirely from cache');
    }

    public function test_dashboard_stats_cache_reflects_data_at_time_of_first_call(): void
    {
        $this->actingAsAdmin();
        $before = $this->getJson('/api/admin/stats')->json('stats.total_users');

        $this->makeUser(); // changes the real count, but cache should still hold the old value

        $after = $this->getJson('/api/admin/stats')->json('stats.total_users');

        $this->assertSame($before, $after);
    }

    public function test_chart_data_second_call_is_served_from_cache(): void
    {
        $this->actingAsAdmin();

        DB::enableQueryLog();
        $this->getJson('/api/admin/chart-data')->assertOk();
        $firstCallQueries = count(DB::getQueryLog());
        $this->assertGreaterThan(0, $firstCallQueries, 'first call should hit the database');

        DB::flushQueryLog();
        $this->getJson('/api/admin/chart-data')->assertOk();
        $secondCallQueries = count(DB::getQueryLog());

        $this->assertSame(0, $secondCallQueries, 'second call within the TTL should be served entirely from cache');
    }
}
