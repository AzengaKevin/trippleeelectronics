<?php

namespace Tests\Feature\Models;

use App\Models\Individual;
use App\Models\Item;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_purchase_record(): void
    {
        $organization = Organization::factory()->create();

        $attributes = [
            'supplier_id' => $organization->id,
            'supplier_type' => $organization->getMorphClass(),
            'amount' => $amount = $this->faker->randomFloat(2, 10_000, 100_000),
            'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000),
            'total_amount' => $amount + $shippingAmount,
        ];

        $purchase = Purchase::query()->create($attributes);

        $this->assertNotNull($purchase);

        $this->assertDatabaseHas('purchases', $attributes);
    }

    public function test_creating_a_new_purchase_from_factory(): void
    {
        $organization = Organization::factory()->create();

        $purchase = Purchase::factory()->for($organization, 'supplier')->create();

        $this->assertNotNull($purchase->supplier);

        $this->assertEquals($organization->id, $purchase->supplier_id);

        $this->assertEquals($organization->getMorphClass(), $purchase->supplier_type);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $organization->id,
            'supplier_type' => $organization->getMorphClass(),
            'amount' => $purchase->amount,
            'shipping_amount' => $purchase->shipping_amount,
            'total_amount' => $purchase->total_amount,
        ]);
    }

    public function test_creating_a_new_purchase_with_store(): void
    {
        $organization = Organization::factory()->create();

        $purchase = Purchase::factory()->for($organization, 'supplier')->for(Store::factory())->create();

        $this->assertNotNull($purchase->supplier);

        $this->assertNotNull($purchase->store);

        $this->assertEquals($organization->id, $purchase->supplier_id);

        $this->assertEquals($organization->getMorphClass(), $purchase->supplier_type);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $organization->id,
            'supplier_type' => $organization->getMorphClass(),
            'amount' => $purchase->amount,
            'shipping_amount' => $purchase->shipping_amount,
            'total_amount' => $purchase->total_amount,
        ]);
    }

    public function test_purchase_items_relationship_method(): void
    {
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->count($itemsCount = 2)->for(Item::factory()), 'items')->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $purchase->items());

        $this->assertEquals('purchase_id', $purchase->items()->getForeignKeyName());

        $this->assertCount($itemsCount, $purchase->items);
    }

    public function test_purchase_payments_relationship_method(): void
    {
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $individual = Individual::factory()->create();

        Payment::factory()->count($paymentsCount = 2)->for($purchase, 'payable')->for($individual, 'payee')->create();

        $this->assertEquals($paymentsCount, $purchase->payments()->count());
    }

    public function test_purchase_author_relationship_method(): void
    {
        $purchase = Purchase::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(\App\Models\User::class, $purchase->author);

        $this->assertEquals($purchase->author_user_id, $purchase->author->id);
    }
}
