<?php

namespace Tests\Feature;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPlatformImpactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();
        $user = User::create(['name' => 'Impact', 'email' => 'platform-impact@example.test', 'is_verified' => true]);
        Sanctum::actingAs($user);
        $rvm = RvmMachine::rvmOnly()->firstOrFail();
        $session = RecyclingSession::create([
            'session_code' => 'IMPACT', 'user_id' => $user->id, 'machine_id' => $rvm->id, 'status' => 'completed',
        ]);
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $rvm->id,
            'material_selected' => 'aluminum', 'is_valid' => true,
        ]);

        $batch = CompactorBatch::create([
            'user_id' => $user->id, 'machine_id' => $this->machine('compactor')->id,
            'plastic_count' => 6, 'aluminum_count' => 4, 'points_earned' => 160, 'carbon_kg' => 0.976,
        ]);
        $deposit = ComposterDeposit::create([
            'user_id' => $user->id, 'machine_id' => $this->machine('composter')->id,
            'weight_kg' => 2.5, 'points_earned' => 25, 'carbon_kg' => 1.1,
        ]);
        foreach (['compactor' => $batch, 'composter' => $deposit] as $source => $activity) {
            PointsHistory::create([
                'user_id' => $user->id, 'source' => $source, 'source_id' => $activity->id,
                'points_change' => $activity->points_earned, 'balance_after' => 200, 'type' => 'earned',
            ]);
        }
    }

    public function test_me_includes_platform_carbon_only_on_enabled_opt_in(): void
    {
        config(['platform.enabled' => true]);
        $this->getJson('/api/auth/me?platform=1')->assertOk()->assertJsonPath('user.total_carbon_saved', 2.302);
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('user.total_carbon_saved', 0.226);

        config(['platform.enabled' => false]);
        $this->getJson('/api/auth/me?platform=1')->assertOk()->assertJsonPath('user.total_carbon_saved', 0.226);
    }

    public function test_x_platform_header_opts_in_like_the_query_param(): void
    {
        config(['platform.enabled' => true]);
        $this->getJson('/api/auth/me', ['X-Platform' => '1'])->assertOk()->assertJsonPath('user.total_carbon_saved', 2.302);
        $this->getJson('/api/user/account-summary', ['X-Platform' => '1'])->assertOk()
            ->assertJsonPath('summary.carbon_saved_kg', 2.302);
        $this->getJson('/api/user/points-history', ['X-Platform' => '1'])->assertOk()->assertJsonCount(2, 'history.data');
    }

    public function test_login_returns_platform_carbon_so_the_card_does_not_jump(): void
    {
        config(['platform.enabled' => true]);
        User::where('email', 'platform-impact@example.test')->update(['password_hash' => \Illuminate\Support\Facades\Hash::make('secret-pass-1')]);

        $this->postJson('/api/auth/login', ['email' => 'platform-impact@example.test', 'password' => 'secret-pass-1'], ['X-Platform' => '1'])
            ->assertOk()->assertJsonPath('user.total_carbon_saved', 2.302);
        $this->postJson('/api/auth/login', ['email' => 'platform-impact@example.test', 'password' => 'secret-pass-1'])
            ->assertOk()->assertJsonPath('user.total_carbon_saved', 0.226);
    }

    public function test_google_exchange_refreshes_platform_carbon(): void
    {
        config(['platform.enabled' => true]);
        $user = User::where('email', 'platform-impact@example.test')->firstOrFail();
        \Illuminate\Support\Facades\Cache::put('oauth_code:abc', ['token' => 't', 'user' => ['id' => $user->id, 'total_carbon_saved' => 0.226]], 60);

        $this->postJson('/api/auth/google/exchange', ['code' => 'abc'], ['X-Platform' => '1'])
            ->assertOk()->assertJsonPath('user.total_carbon_saved', 2.302);
    }

    public function test_account_summary_keeps_legacy_counts_for_a_legacy_client(): void
    {
        config(['platform.enabled' => true]);
        $this->getJson('/api/user/account-summary?platform=1')->assertOk()
            ->assertJsonPath('summary.items_recycled', 1)
            ->assertJsonPath('summary.carbon_saved_kg', 2.302)
            ->assertJsonPath('summary.earned_this_month', 185);
        $this->getJson('/api/user/account-summary')->assertOk()
            ->assertJsonPath('summary.carbon_saved_kg', 0.23)
            ->assertJsonPath('summary.earned_this_month', 0);
    }

    public function test_points_history_describes_simulated_credits_in_three_locales(): void
    {
        config(['platform.enabled' => true]);
        $expected = [
            'en' => ['Composter: 2.5 kg food waste', 'Compactor batch: 10 items'],
            'my' => ['Pengompos: 2.5 kg sisa makanan', 'Kelompok pemadat: 10 barang'],
            'id' => ['Composter: 2.5 kg sisa makanan', 'Batch compactor: 10 barang'],
        ];

        foreach ($expected as $locale => [$composter, $compactor]) {
            $this->getJson('/api/user/points-history?platform=1', ['X-App-Locale' => $locale])->assertOk()
                ->assertJsonPath('history.data.0.source', 'composter')
                ->assertJsonPath('history.data.0.is_demo', true)
                ->assertJsonPath('history.data.0.description', $composter)
                ->assertJsonPath('history.data.1.source', 'compactor')
                ->assertJsonPath('history.data.1.is_demo', true)
                ->assertJsonPath('history.data.1.description', $compactor);
        }

        $this->getJson('/api/user/points-history')->assertOk()->assertJsonCount(0, 'history.data');
        config(['platform.enabled' => false]);
        $this->getJson('/api/user/points-history?platform=1')->assertOk()->assertJsonCount(0, 'history.data');
    }

    private function machine(string $type): RvmMachine
    {
        return RvmMachine::create(['machine_code' => 'IMPACT-' . $type, 'name' => $type, 'machine_type' => $type, 'is_demo' => true]);
    }
}
