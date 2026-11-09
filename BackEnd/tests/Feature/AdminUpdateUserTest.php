<?php
namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\PointsHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUpdateUserTest extends TestCase
{
    use RefreshDatabase;

    private function actors(): array
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'admin', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $user = User::create([
            'name' => 'Old', 'email' => 'u@test.local', 'phone' => '0111111111',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 100,
        ]);
        Sanctum::actingAs($admin, ['*']);
        return [$admin, $user];
    }

    public function test_points_change_writes_ledger_row_and_logs_before_after(): void
    {
        [, $user] = $this->actors();

        $this->putJson("/api/admin/users/{$user->id}", ['total_points' => 150])->assertOk();
        $row = PointsHistory::where('user_id', $user->id)->latest('id')->first();
        $this->assertSame(50, (int) $row->points_change);
        $this->assertSame('earned', $row->type);
        $this->assertSame(150, (int) $row->balance_after);
        $this->assertStringStartsWith('Admin adjustment', $row->description);
        $d = json_decode(AdminLog::latest('id')->first()->details, true);
        $this->assertEquals([100, 150], $d['changes']['total_points']);
        $this->assertArrayHasKey('message', $d);

        $this->putJson("/api/admin/users/{$user->id}", ['total_points' => 120])->assertOk();
        $row = PointsHistory::where('user_id', $user->id)->latest('id')->first();
        $this->assertSame(-30, (int) $row->points_change);
        $this->assertSame('deducted', $row->type);
    }

    public function test_update_without_points_change_writes_no_ledger_row(): void
    {
        [, $user] = $this->actors();
        $before = PointsHistory::count();

        $payload = $user->fresh()->toArray();
        $payload['name'] = 'New';
        $this->putJson("/api/admin/users/{$user->id}", $payload)->assertOk();

        $this->assertSame('New', $user->fresh()->name);
        $this->assertSame($before, PointsHistory::count());
    }

    public function test_invalid_payload_rejected(): void
    {
        [, $user] = $this->actors();
        $this->putJson("/api/admin/users/{$user->id}", ['total_points' => -5])->assertStatus(422);
        $this->putJson("/api/admin/users/{$user->id}", ['role' => 'superadmin'])->assertStatus(422);
        $this->putJson("/api/admin/users/{$user->id}", ['email' => 'not-an-email'])->assertStatus(422);
    }
}
