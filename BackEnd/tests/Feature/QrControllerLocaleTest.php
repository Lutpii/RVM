<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrControllerLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_machine_not_found_message_is_localized(): void
    {
        $this->withHeaders(['X-App-Locale' => 'en'])
            ->getJson('/api/qr/generate/NO-SUCH-MACHINE')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Machine not found.');

        $this->withHeaders(['X-App-Locale' => 'my'])
            ->getJson('/api/qr/generate/NO-SUCH-MACHINE')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Mesin tidak dijumpai.');
    }
}
