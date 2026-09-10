<?php
// BackEnd/tests/Feature/AdminEmailFormalReportTest.php
namespace Tests\Feature;

use App\Mail\FormalReportGenerated;
use App\Models\AdminLog;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEmailFormalReportTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'admin',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    public function test_emails_a_formal_report_with_an_xlsx_attachment_to_the_given_address(): void
    {
        Mail::fake();
        $admin = $this->actingAsAdmin();

        $machine = RvmMachine::create(['machine_code' => 'RVM-' . uniqid(), 'name' => 'Test Machine', 'location_name' => 'Lobby', 'status' => 'active']);
        $user = User::create(['name' => 'U', 'email' => 'u@test.local', 'phone' => '0199999999', 'password_hash' => bcrypt('x'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0]);
        $session = RecyclingSession::create(['session_code' => 'S' . uniqid(), 'user_id' => $user->id, 'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0]);
        Transaction::create(['session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id, 'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum', 'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0]);

        $this->postJson('/api/admin/export-excel/email', [
            'email' => 'swcorp@example.com',
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
        ])->assertOk()->assertJson(['success' => true]);

        Mail::assertSent(FormalReportGenerated::class, function (FormalReportGenerated $mail) {
            return $mail->hasTo('swcorp@example.com')
                && count($mail->attachments()) === 1;
        });

        $this->assertEquals(1, AdminLog::where('action', 'email_formal_report')->count());
    }

    public function test_rejects_an_invalid_email_address(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $this->postJson('/api/admin/export-excel/email', ['email' => 'not-an-email'])
            ->assertStatus(422);

        Mail::assertNothingSent();
    }

    public function test_returns_a_clear_error_instead_of_a_bare_500_when_sending_fails(): void
    {
        $this->actingAsAdmin();

        // Simulates the exact failure this went live against during manual
        // verification: Resend's sandbox domain rejecting the recipient
        // (550 Invalid `to` field). Mail::to()->send() throws in that case;
        // unmocked this would previously surface as a bare 500.
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \Exception('550 Invalid `to` field.'));

        $this->postJson('/api/admin/export-excel/email', ['email' => 'swcorp@example.com'])
            ->assertStatus(502)
            ->assertJson(['success' => false]);
    }

    public function test_non_admin_cannot_email_the_report(): void
    {
        Mail::fake();
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0198765432',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/admin/export-excel/email', ['email' => 'swcorp@example.com'])
            ->assertStatus(403);

        Mail::assertNothingSent();
    }
}
