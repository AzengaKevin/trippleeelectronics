<?php

namespace Tests\Feature\Models;

use App\Models\CustomItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_create_a_custom_item(): void
    {
        $data = [
            'name' => $this->faker->unique()->word(),
            'pos_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'pos_description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0, 100),
        ];

        $customItem = CustomItem::factory()->create($data);

        $this->assertNotNull($customItem);
    }

    public function test_a_custom_item_can_be_part_of_order_items(): void
    {
        $customItem = CustomItem::factory()->create();

        /** @var Order */
        $order = Order::factory()->create();

        $order->items()->create($data = [
            'item_id' => $customItem->id,
            'item_type' => $customItem->getMorphClass(),
            'quantity' => 1,
            'price' => $customItem->price,
        ]);

        $this->assertDatabaseHas('order_items', [
            'item_id' => data_get($data, 'item_id'),
            'item_type' => data_get($data, 'item_type'),
            'quantity' => data_get($data, 'quantity'),
            'price' => data_get($data, 'price'),
        ]);
    }
}
