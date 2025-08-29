<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Store;
use App\Models\User;
use App\Services\POSStoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class POSStoreControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: ['access-backoffice', 'access-pos'], role: 'admin');
    }

    public function test_backoffice_pos_store_updated_route_happy()
    {
        $this->withoutExceptionHandling();

        Store::factory()->count(2)->create();

        $store = Store::factory()->create();

        $response = $this->actingAs($this->user)->post(route('backoffice.pos.store.update'), [
            'store' => $store->id,
        ]);

        $currentStore = app(POSStoreService::class)->getCurrentPOSStore();

        $this->assertEquals($store->id, $currentStore['id']);

        $response->assertStatus(302);
    }
}
