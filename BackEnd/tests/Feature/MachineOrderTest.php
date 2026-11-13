<?php

namespace Tests\Feature;

use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MachineOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_is_used_by_the_recycler_machine_list(): void
    {
        $admin = User::create(['name' => 'Order Admin', 'email' => 'order-admin@example.test', 'role' => 'admin', 'is_verified' => true]);
        Sanctum::actingAs($admin);
        $dsme = RvmMachine::create(['name' => 'RVM DSME', 'machine_code' => 'ORDER-DSME', 'status' => 'active']);
        $inactive = RvmMachine::create(['name' => 'Inactive', 'machine_code' => 'ORDER-INACTIVE', 'status' => 'inactive']);
        $otherIds = RvmMachine::whereNotIn('id', [$dsme->id, $inactive->id])->orderBy('id')->pluck('id')->all();
        $order = [$dsme->id, $inactive->id, ...$otherIds];

        $this->putJson('/api/admin/machines/order', ['order' => [$dsme->id]])->assertConflict();
        $this->putJson('/api/admin/machines/order', ['order' => $order])->assertOk();
        $this->getJson('/api/admin/machines')->assertJsonPath('machines.0.id', $dsme->id);
        $this->getJson('/api/machines')
            ->assertJsonPath('machines.0.id', $dsme->id)
            ->assertJsonMissing(['machine_code' => 'ORDER-INACTIVE']);

        $recycler = User::create(['name' => 'Recycler', 'email' => 'order-user@example.test', 'role' => 'user', 'is_verified' => true]);
        Sanctum::actingAs($recycler);
        $this->putJson('/api/admin/machines/order', ['order' => array_reverse($order)])->assertForbidden();
    }
}
