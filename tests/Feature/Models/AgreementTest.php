<?php

namespace Tests\Feature\Models;

use App\Models\Agreement;
use App\Models\Enums\AgreementStatus;
use App\Models\Enums\ClientType;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AgreementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_content_only_agreement()
    {

        $user = User::factory()->create();

        $store = Store::factory()->create();

        $client = app(ClientService::class)->upsert($this->createClientData());

        $attributes = [
            'author_user_id' => $user->id,
            'store_id' => $store->id,
            'client_id' => $client?->id,
            'client_type' => $client?->getMorphClass(),
            'content' => $this->faker->paragraph(),
            'status' => AgreementStatus::PENDING->value,
            'since' => now(),
            'until' => now()->addYear(),
        ];

        $agreement = Agreement::query()->create($attributes);

        $this->assertDatabaseHas('agreements', [
            'id' => $agreement->id,
            'author_user_id' => $attributes['author_user_id'],
            'store_id' => $attributes['store_id'],
            'client_id' => $attributes['client_id'],
            'client_type' => $attributes['client_type'],
            'content' => $attributes['content'],
            'status' => $attributes['status'],
            'since' => $attributes['since'],
            'until' => $attributes['until'],
        ]);

        $this->assertNotNull($agreement->author);
        $this->assertNotNull($agreement->store);
        $this->assertNotNull($agreement->client);
        $this->assertNull($agreement->agreementable);
    }

    public function test_creating_a_new_order_agreement(): void
    {

        $user = User::factory()->create();

        $store = Store::factory()->create();

        $client = app(ClientService::class)->upsert($this->createClientData());

        $order = Order::factory()->create();

        $attributes = [
            'author_user_id' => $user->id,
            'store_id' => $store->id,
            'client_id' => $client?->id,
            'agreementable_id' => $order->id,
            'agreementable_type' => $order->getMorphClass(),
            'client_type' => $client?->getMorphClass(),
            'content' => $this->faker->paragraph(),
            'status' => AgreementStatus::PENDING->value,
            'since' => now(),
            'until' => now()->addYear(),
        ];

        $agreement = Agreement::query()->create($attributes);

        $this->assertDatabaseHas('agreements', [
            'id' => $agreement->id,
            'author_user_id' => $attributes['author_user_id'],
            'store_id' => $attributes['store_id'],
            'client_id' => $attributes['client_id'],
            'client_type' => $attributes['client_type'],
            'agreementable_id' => $attributes['agreementable_id'],
            'agreementable_type' => $attributes['agreementable_type'],
            'content' => $attributes['content'],
            'status' => $attributes['status'],
            'since' => $attributes['since'],
            'until' => $attributes['until'],
        ]);

        $this->assertNotNull($agreement->author);

        $this->assertNotNull($agreement->store);

        $this->assertNotNull($agreement->client);

        $this->assertNotNull($agreement->agreementable);
    }

    public function test_createing_a_new_purchase_agreement(): void
    {

        $user = User::factory()->create();

        $store = Store::factory()->create();

        $client = app(ClientService::class)->upsert($this->createClientData());

        $purchase = Purchase::factory()->create();

        $attributes = [
            'author_user_id' => $user->id,
            'store_id' => $store->id,
            'client_id' => $client?->id,
            'agreementable_id' => $purchase->id,
            'agreementable_type' => $purchase->getMorphClass(),
            'client_type' => $client?->getMorphClass(),
            'content' => $this->faker->paragraph(),
            'status' => AgreementStatus::PENDING->value,
            'since' => now(),
            'until' => now()->addYear(),
        ];

        $agreement = Agreement::query()->create($attributes);

        $this->assertDatabaseHas('agreements', [
            'id' => $agreement->id,
            'author_user_id' => $attributes['author_user_id'],
            'store_id' => $attributes['store_id'],
            'client_id' => $attributes['client_id'],
            'client_type' => $attributes['client_type'],
            'agreementable_id' => $attributes['agreementable_id'],
            'agreementable_type' => $attributes['agreementable_type'],
            'content' => $attributes['content'],
            'status' => $attributes['status'],
            'since' => $attributes['since'],
            'until' => $attributes['until'],
        ]);

        $this->assertNotNull($agreement->author);

        $this->assertNotNull($agreement->store);

        $this->assertNotNull($agreement->client);

        $this->assertNotNull($agreement->agreementable);
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
