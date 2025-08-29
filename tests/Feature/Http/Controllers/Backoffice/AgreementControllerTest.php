<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Agreement;
use App\Models\Enums\ClientType;
use App\Models\Individual;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class AgreementControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-agreements',
            'create-agreements',
            'read-agreements',
            'update-agreements',
            'delete-agreements',
            'export-agreements',
            'import-agreements',
        ]);
    }

    public function test_backoffice_agreements_index_route(): void
    {
        Agreement::factory()->for(Individual::factory(), 'client')->count($agreementsCount = 3)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.agreements.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/agreements/IndexPage')
                ->has('agreements.data', $agreementsCount)
                ->has('params')
        );
    }

    public function test_backoffice_agreements_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.agreements.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/agreements/CreatePage')
                ->has('stores')
        );
    }

    public function test_backoffice_agreements_store_route(): void
    {

        $store = Store::factory()->create();

        $payload = [
            'client' => $this->createClientData(),
            'store' => $store->id,
            'content' => $this->faker->paragraph(),
            'since' => now()->toDateString(),
            'until' => now()->addYear()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.agreements.store'), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('agreements', [
            'store_id' => $store->id,
            'content' => $payload['content'],
            'since' => $payload['since'],
            'until' => $payload['until'],
            'author_user_id' => $this->user->id,
        ]);

        $agreement = Agreement::where('store_id', $store->id)->first();

        $this->assertNotNull($agreement);

        $this->assertNotNull($agreement->client);
    }

    public function test_backoffice_agreements_store_route_for_an_order(): void
    {
        $store = Store::factory()->create();

        $client = app(ClientService::class)->upsert($this->createClientData());

        $order = Order::factory()->for($store)->for($client, 'customer')->create();

        $payload = [
            'client' => $client->only('id', 'email', 'phone', 'address'),
            'store' => $store->id,
            'content' => $this->faker->paragraph(),
            'since' => now()->toDateString(),
            'until' => now()->addYear()->toDateString(),
            'deal' => $order->id,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.agreements.store'), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('agreements', [
            'store_id' => $store->id,
            'content' => $payload['content'],
            'agreementable_type' => $order->getMorphClass(),
            'agreementable_id' => $order->id,
            'since' => $payload['since'],
            'until' => $payload['until'],
            'author_user_id' => $this->user->id,
        ]);

        $agreement = Agreement::where('store_id', $store->id)->first();

        $this->assertNotNull($agreement);

        $this->assertNotNull($agreement->client);
    }

    public function test_backoffice_agreements_store_route_for_a_purchase(): void
    {
        $store = Store::factory()->create();

        $client = app(ClientService::class)->upsert($this->createClientData());

        $purchase = Purchase::factory()->for($store)->for($client, 'supplier')->create();

        $payload = [
            'client' => $client->only('id', 'email', 'phone', 'address'),
            'store' => $store->id,
            'content' => $this->faker->paragraph(),
            'deal' => $purchase->id,
            'since' => now()->toDateString(),
            'until' => now()->addYear()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.agreements.store'), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('agreements', [
            'store_id' => $store->id,
            'agreementable_type' => $purchase->getMorphClass(),
            'agreementable_id' => $purchase->id,
            'content' => $payload['content'],
            'since' => $payload['since'],
            'until' => $payload['until'],
            'author_user_id' => $this->user->id,
        ]);

        $agreement = Agreement::where('store_id', $store->id)->first();

        $this->assertNotNull($agreement);

        $this->assertNotNull($agreement->client);
    }

    public function test_backoffice_agreements_show_route(): void
    {
        $agreement = Agreement::factory()->for(Individual::factory(), 'client')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.agreements.show', $agreement));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/agreements/ShowPage')
                ->has('agreement')
        );
    }

    public function test_backoffice_agreements_edit_route(): void
    {
        $agreement = Agreement::factory()->for(Individual::factory(), 'client')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.agreements.edit', $agreement));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $assertableInertia) => $assertableInertia
                ->component('backoffice/agreements/EditPage')
                ->has('agreement')
                ->has('stores')
        );
    }

    public function test_backoffice_agreements_update_route(): void
    {
        $individual = app(ClientService::class)->upsert($this->createClientData());

        $agreement = Agreement::factory()->for($individual, 'client')->create();

        $payload = [
            'client' => [
                ...$individual->only('email', 'phone', 'address'),
                'id' => $individual->id,
            ],
            'store' => $agreement->store_id,
            'content' => $this->faker->paragraph(),
            'since' => now()->toDateString(),
            'until' => now()->addYear()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.agreements.update', $agreement), $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('agreements', [
            'id' => $agreement->id,
            'content' => $payload['content'],
            'since' => $payload['since'],
            'until' => $payload['until'],
        ]);
    }

    public function test_backoffice_agreements_destroy_route(): void
    {
        $agreement = Agreement::factory()->for(Individual::factory(), 'client')->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.agreements.destroy', $agreement));

        $response->assertStatus(302);

        $this->assertSoftDeleted('agreements', [
            'id' => $agreement->id,
        ]);
    }

    public function test_backoffice_agreements_print_route(): void
    {
        $agreement = Agreement::factory()->for(Individual::factory(), 'client')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.agreements.print', $agreement));

        $response->assertStatus(200);
    }

    private function createClientData(): array
    {
        $individual = $this->faker->boolean();

        return [
            'id' => $individual ? $this->faker->name() : $this->faker->company(),
            'type' => $individual ? ClientType::INDIVIDUAL->value : ClientType::ORGANIZATION->value,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
        ];
    }
}
