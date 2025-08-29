<?php

namespace Tests\Feature\Models;

use App\Models\CustomItem;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\RefundStatus;
use App\Models\Enums\ShippingStatus;
use App\Models\Individual;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_store_front_order_record(): void
    {

        $store = Store::factory()->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'store_id' => $store->id,
            'customer_id' => $individual->id,
            'customer_type' => $individual->getMorphClass(),
            'amount' => $amount = $this->faker->randomFloat(2, 0, 1000),
            'shipping_amount' => null,
            'tax_amount' => null,
            'discount_amount' => null,
            'total_amount' => $amount,
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => null,
            'shipping_status' => null,
            'refund_status' => null,
            'channel' => 'POS',
            'refferal_code' => null,
            'notes' => $this->faker->text(100),
        ];

        $order = Order::query()->create($attributes);

        $this->assertNotNull($order);

        $this->assertDatabaseHas('orders', $attributes);
    }

    public function test_create_a_new_pos_order_record(): void
    {
        $store = Store::factory()->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'store_id' => $store->id,
            'customer_id' => $individual->id,
            'customer_type' => $individual->getMorphClass(),
            'amount' => $amount = $this->faker->randomFloat(2, 0, 1000),
            'shipping_amount' => null,
            'tax_amount' => null,
            'discount_amount' => null,
            'total_amount' => $amount,
            'balance_amount' => $balance = $this->faker->numberBetween(100, 1000),
            'tendered_amount' => $amount + $balance,
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => null,
            'shipping_status' => null,
            'refund_status' => null,
            'channel' => 'pos',
            'refferal_code' => null,
            'notes' => $this->faker->text(100),
        ];

        $order = Order::query()->create($attributes);

        $this->assertNotNull($order);

        $this->assertDatabaseHas('orders', $attributes);
    }

    public function test_creating_a_new_online_order_record(): void
    {

        $store = Store::factory()->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'store_id' => $store->id,
            'customer_id' => $individual->id,
            'customer_type' => $individual->getMorphClass(),
            'amount' => $amount = $this->faker->randomFloat(2, 0, 100000),
            'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 500),
            'tax_amount' => null,
            'discount_amount' => null,
            'total_amount' => $amount + $shippingAmount,
            'order_status' => $this->faker->randomElement(OrderStatus::options()),
            'payment_status' => $this->faker->randomElement(PaymentStatus::options()),
            'fulfillment_status' => $this->faker->randomElement(FulfillmentStatus::options()),
            'shipping_status' => $this->faker->randomElement(ShippingStatus::options()),
            'refund_status' => $this->faker->randomElement(RefundStatus::options()),
            'channel' => 'online',
            'refferal_code' => null,
            'notes' => $this->faker->text(100),
        ];

        $order = Order::query()->create($attributes);

        $this->assertNotNull($order);

        $this->assertDatabaseHas('orders', $attributes);
    }

    public function test_creating_an_order_from_factory(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order);
    }

    public function test_order_items_relationship_method(): void
    {
        $order = Order::factory()->has(OrderItem::factory($itemsCount = 2)->for(Item::factory(), 'item'), 'items')->create();

        $this->assertCount($itemsCount, $order->items);

        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    public function test_order_payments_relationship_method(): void
    {

        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $individual = Individual::factory()->create();

        Payment::factory()->count($paymentsCount = 2)->for($order, 'payable')->for($individual, 'payer')->create();

        $this->assertEquals($paymentsCount, $order->payments()->count());
    }

    public function test_order_author_relationship_method(): void
    {
        $order = Order::factory()->for(User::factory(), 'author')->create();

        $this->assertNotNull($order->author);

        $this->assertEquals($order->author_user_id, $order->author->id);
    }

    public function test_order_user_relationship_method(): void
    {
        $order = Order::factory()->for(User::factory())->create();

        $this->assertNotNull($order->user);

        $this->assertEquals($order->user_id, $order->user->id);
    }

    public function test_order_varied_items_relationship_method(): void
    {
        $order = Order::factory()->create();

        $item = Item::factory()->create();

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $customItem = CustomItem::factory()->create();

        $items = collect([$item, $variant, $customItem]);

        $items->each(function ($item) use ($order) {
            $order->items()->create([
                'item_id' => $item->id,
                'item_type' => $item->getMorphClass(),
                'quantity' => 1,
                'price' => $item->price,
            ]);
        });

        $this->assertCount($items->count(), $order->fresh()->items);
    }

    public function test_that_two_orders_are_similar(): void
    {
        $author = User::factory()->create();

        $store = Store::factory()->create();

        $customer = Individual::factory()->create();

        $item = Item::factory()->create();

        $order1 = Order::factory()
            ->for($author, 'author')
            ->for($store)
            ->for($customer, 'customer')
            ->has(OrderItem::factory()->for($item, 'item'), 'items')
            ->create(['order_status' => OrderStatus::PENDING->value, 'total_amount' => 1000]);

        $order2 = Order::factory()
            ->for($author, 'author')
            ->for($store)
            ->for($customer, 'customer')
            ->has(OrderItem::factory()->for($item, 'item'), 'items')
            ->create(['order_status' => OrderStatus::PENDING->value, 'total_amount' => 1000]);

        $this->assertTrue($order1->fresh()->isSimilar($order2));
    }
}
