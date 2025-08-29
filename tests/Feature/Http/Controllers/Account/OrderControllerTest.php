<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    public function test_account_orders_index_route(): void
    {
        $user = $this->createUserWithPermissions(permissions: []);

        Order::factory()->count($ordersCount = 2)->has(OrderItem::factory()->count(2)->for(Item::factory()), 'items')->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.orders.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('account/orders/IndexPage')
                ->has('orders')
                ->has('orders.data', $ordersCount)
                ->hasAll('settings', 'categories', 'treeCategories', 'services')
        );
    }

    public function test_orders_receipt_route(): void
    {
        $user = $this->createUserWithPermissions(permissions: []);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.orders.receipt', ['order' => $order]));

        $response->assertOk();
    }
}
