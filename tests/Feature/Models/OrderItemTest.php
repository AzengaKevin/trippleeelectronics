<?php

namespace Tests\Feature\Models;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_order_item_record(): void
    {
        $order = Order::factory()->create();

        /** @var Item $item */
        $item = Item::factory()->create();

        $attributes = [
            'order_id' => $order->id,
            'item_id' => $item->id,
            'item_type' => $item->getMorphClass(),
            'quantity' => $this->faker->randomDigitNotZero(),
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];

        $orderItem = OrderItem::query()->create($attributes);

        $this->assertNotNull($orderItem);

        $this->assertDatabaseHas('order_items', $attributes);

        $this->assertEquals($orderItem->id, $item->orderItems->first()->id);

    }

    public function test_creating_a_new_order_item_from_factory(): void
    {
        $order = Order::factory()->create();

        $item = Item::factory()->create();

        $orderItem = OrderItem::factory()->for($order)->for($item, 'item')->create();

        $this->assertNotNull($orderItem);

        $this->assertEquals($orderItem->id, $item->orderItems->first()->id);
    }
}
