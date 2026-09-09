<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
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

    private function makeMachine(array $overrides = []): RvmMachine
    {
        self::$seq++;
        return RvmMachine::create(array_merge([
            'machine_code' => 'RVM-TEST-' . self::$seq,
            'name' => 'Test Machine',
            'status' => 'active',
            'aluminum_level' => 0, 'plastic_level' => 0, 'glass_level' => 0, 'paper_level' => 0,
        ], $overrides));
    }

    private function makeSession(RvmMachine $machine, array $overrides = []): RecyclingSession
    {
        self::$seq++;
        return RecyclingSession::create(array_merge([
            'session_code' => 'SESS-' . self::$seq,
            'user_id' => $this->makeUser()->id,
            'machine_id' => $machine->id,
            'status' => 'completed',
            'total_items' => 1,
        ], $overrides));
    }

    private function makeTransaction(RecyclingSession $session, RvmMachine $machine, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'session_id' => $session->id,
            'user_id' => $session->user_id,
            'machine_id' => $machine->id,
            'material_selected' => 'plastic',
            'is_valid' => 1,
            'weight_grams' => 100,
            'points_earned' => 15,
        ], $overrides));
    }

    // ── Users ──────────────────────────────────────────────────────────

    public function test_users_default_per_page_is_15(): void
    {
        $this->actingAsAdmin();
        for ($i = 0; $i < 30; $i++) $this->makeUser();

        $res = $this->getJson('/api/admin/users');

        $res->assertOk();
        $this->assertCount(15, $res->json('users.data'));
        $this->assertSame(15, $res->json('users.per_page'));
    }

    public function test_users_respects_valid_per_page(): void
    {
        $this->actingAsAdmin();
        for ($i = 0; $i < 60; $i++) $this->makeUser();

        $res = $this->getJson('/api/admin/users?per_page=50');

        $res->assertOk();
        $this->assertCount(50, $res->json('users.data'));
    }

    public function test_users_falls_back_to_default_per_page_for_invalid_value(): void
    {
        $this->actingAsAdmin();
        for ($i = 0; $i < 30; $i++) $this->makeUser();

        $res = $this->getJson('/api/admin/users?per_page=999999');

        $res->assertOk();
        $this->assertSame(15, $res->json('users.per_page'));
    }

    public function test_users_accepts_15_as_a_valid_per_page(): void
    {
        $this->actingAsAdmin();
        for ($i = 0; $i < 20; $i++) $this->makeUser();

        $res = $this->getJson('/api/admin/users?per_page=15');

        $res->assertOk();
        $this->assertCount(15, $res->json('users.data'));
    }

    public function test_users_search_filters_by_name(): void
    {
        $this->actingAsAdmin();
        $this->makeUser(['name' => 'Budi Santoso']);
        $this->makeUser(['name' => 'Siti Aminah']);

        $res = $this->getJson('/api/admin/users?search=Budi');

        $res->assertOk();
        $names = collect($res->json('users.data'))->pluck('name');
        $this->assertTrue($names->contains('Budi Santoso'));
        $this->assertFalse($names->contains('Siti Aminah'));
    }

    public function test_users_search_with_no_matches_returns_empty(): void
    {
        $this->actingAsAdmin();
        $this->makeUser(['name' => 'Budi Santoso']);

        $res = $this->getJson('/api/admin/users?search=NoSuchPersonAtAll');

        $res->assertOk();
        $this->assertCount(0, $res->json('users.data'));
    }

    public function test_users_defaults_to_id_descending_without_a_sort_param(): void
    {
        $admin = $this->actingAsAdmin();
        $first = $this->makeUser();
        $second = $this->makeUser();

        $res = $this->getJson('/api/admin/users')->assertOk();

        $ids = collect($res->json('users.data'))->pluck('id');
        $this->assertSame([$second->id, $first->id, $admin->id], $ids->take(3)->all());
    }

    public function test_users_can_sort_by_name_ascending(): void
    {
        $this->actingAsAdmin();
        $this->makeUser(['name' => 'Zeta']);
        $this->makeUser(['name' => 'Alpha']);

        $res = $this->getJson('/api/admin/users?sort=name&direction=asc')->assertOk();

        $names = collect($res->json('users.data'))->pluck('name');
        $this->assertSame(['Admin', 'Alpha', 'Zeta'], $names->all());
    }

    public function test_users_can_sort_by_total_points_descending(): void
    {
        $this->actingAsAdmin();
        $this->makeUser(['total_points' => 5]);
        $this->makeUser(['total_points' => 50]);

        $res = $this->getJson('/api/admin/users?sort=total_points&direction=desc')->assertOk();

        $points = collect($res->json('users.data'))->pluck('total_points');
        $this->assertSame([50, 5, 0], $points->all());
    }

    public function test_users_sort_rejects_an_unknown_column(): void
    {
        $this->actingAsAdmin();
        $this->makeUser();

        // An unrecognized sort column must not error or leak SQL — it
        // should just fall back to the default (id desc).
        $res = $this->getJson('/api/admin/users?sort=password_hash&direction=asc');

        $res->assertOk();
    }

    // ── Transactions ───────────────────────────────────────────────────

    public function test_transactions_respects_per_page(): void
    {
        $this->actingAsAdmin();
        $machine = $this->makeMachine();
        $session = $this->makeSession($machine);
        for ($i = 0; $i < 30; $i++) $this->makeTransaction($session, $machine);

        $res = $this->getJson('/api/admin/transactions?per_page=25');

        $res->assertOk();
        $this->assertCount(25, $res->json('transactions.data'));
    }

    public function test_transactions_status_filter_valid(): void
    {
        $this->actingAsAdmin();
        $machine = $this->makeMachine();
        $session = $this->makeSession($machine);
        $this->makeTransaction($session, $machine, ['is_valid' => 1]);
        $this->makeTransaction($session, $machine, ['is_valid' => 0, 'points_earned' => 0]);

        $res = $this->getJson('/api/admin/transactions?status=valid');

        $res->assertOk();
        $rows = collect($res->json('transactions.data'));
        $this->assertCount(1, $rows);
        $this->assertTrue($rows->every(fn ($r) => $r['is_valid'] === true || $r['is_valid'] === 1));
    }

    public function test_transactions_search_filters_by_material(): void
    {
        $this->actingAsAdmin();
        $machine = $this->makeMachine();
        $session = $this->makeSession($machine);
        $this->makeTransaction($session, $machine, ['material_selected' => 'plastic']);
        $this->makeTransaction($session, $machine, ['material_selected' => 'glass']);

        $res = $this->getJson('/api/admin/transactions?search=plastic');

        $res->assertOk();
        $materials = collect($res->json('transactions.data'))->pluck('material_selected');
        $this->assertCount(1, $materials);
        $this->assertTrue($materials->every(fn ($m) => $m === 'plastic'));
    }

    // ── Sessions ───────────────────────────────────────────────────────

    public function test_sessions_respects_per_page(): void
    {
        $this->actingAsAdmin();
        $machine = $this->makeMachine();
        for ($i = 0; $i < 30; $i++) $this->makeSession($machine);

        $res = $this->getJson('/api/admin/sessions?per_page=25');

        $res->assertOk();
        $this->assertCount(25, $res->json('sessions.data'));
    }

    public function test_sessions_search_filters_by_machine_name(): void
    {
        $this->actingAsAdmin();
        $machineA = $this->makeMachine(['name' => 'Lobby Machine']);
        $machineB = $this->makeMachine(['name' => 'Cafeteria Machine']);
        $sessionA = $this->makeSession($machineA, ['session_code' => 'S1']);
        $this->makeSession($machineB, ['session_code' => 'S2']);

        $res = $this->getJson('/api/admin/sessions?search=Lobby');

        $res->assertOk();
        $this->assertCount(1, $res->json('sessions.data'));
        $this->assertSame('S1', $res->json('sessions.data.0.session_code'));
    }

    // ── Access control (sanity — must still hold with new query params) ──

    public function test_non_admin_cannot_access_paginated_users(): void
    {
        $user = $this->makeUser(['role' => 'user']);
        Sanctum::actingAs($user, ['*']);

        $res = $this->getJson('/api/admin/users?per_page=25');

        $res->assertStatus(403);
    }
}
