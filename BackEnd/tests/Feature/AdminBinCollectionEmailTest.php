<?php

namespace Tests\Feature;

use App\Mail\BinCollectionRequested;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBinCollectionEmailTest extends TestCase
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

    public function test_sends_email_to_configured_address_when_a_bin_is_full(): void
    {
        config(['services.pbt.notification_email' => 'pbt@example.com']);
        Mail::fake();
        $this->actingAsAdmin();
        $machine = $this->makeMachine(['name' => 'Lobby Machine', 'plastic_level' => 95]);

        $this->postJson('/api/admin/request-bin-collection')
            ->assertOk()
            ->assertJsonPath('affected', 1);

        Mail::assertSent(BinCollectionRequested::class, function (BinCollectionRequested $mail) use ($machine) {
            return $mail->hasTo('pbt@example.com')
                && $mail->machines->pluck('id')->contains($machine->id);
        });
    }

    public function test_rejects_request_when_no_bins_are_full(): void
    {
        config(['services.pbt.notification_email' => 'pbt@example.com']);
        Mail::fake();
        $this->actingAsAdmin();
        $this->makeMachine(['plastic_level' => 50]);

        $this->postJson('/api/admin/request-bin-collection')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'No bins are at or above 90%.',
                'affected' => 0,
            ]);

        Mail::assertNothingSent();
    }

    public function test_does_not_send_email_when_no_recipient_is_configured(): void
    {
        config(['services.pbt.notification_email' => null]);
        Mail::fake();
        $this->actingAsAdmin();
        $this->makeMachine(['plastic_level' => 95]);

        $this->postJson('/api/admin/request-bin-collection')->assertOk();

        Mail::assertNothingSent();
    }
}
