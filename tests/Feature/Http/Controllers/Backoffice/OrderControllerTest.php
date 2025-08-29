<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\CustomItem;
use App\Models\Enums\ClientType;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\StockMovementType;
use App\Models\Individual;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderItemService;
use App\Services\OrderService;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    private ?Store $store = null;

    private PaymentMethod $cashPaymentMethod;

    private PaymentMethod $mpesaPaymentMethod;

    private PaymentMethod $paybillPaymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-orders',
            'create-orders',
            'read-orders',
            'update-orders',
            'delete-orders',
            'import-orders',
            'export-orders',
        ]);

        $this->store = Store::factory()->create();

        $this->user->stores()->attach($this->store);

        $this->cashPaymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $this->mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'Mpesa']);

        $this->paybillPaymentMethod = PaymentMethod::factory()->create(['name' => 'Paybill']);

        PaymentSettings::fake([
            'cash_payment_method' => $this->cashPaymentMethod->id,
            'mpesa_payment_method' => $this->mpesaPaymentMethod->id,
        ]);
    }

    public function test_backoffice_orders_index_route(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('orders'));
    }

    public function test_backoffice_orders_create_route(): void
    {
        Item::factory()->count(2)->create([
            'quantity' => $this->faker->numberBetween(20, 100),
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll('stores', 'primaryTax'));
    }

    public function test_backoffice_orders_show_route(): void
    {
        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.show', $order));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll('order', 'paymentMethods'));
    }

    public function test_backoffice_orders_edit_route(): void
    {
        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.edit', $order));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('order')->hasAll('order', 'stores', 'primaryTax'));
    }

    public function test_backoffice_orders_store_route(): void
    {
        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);

        $totalAmount = $amount + $shippingAmount;

        $payments = [
            [
                ...$this->createPaymentForMethod($this->cashPaymentMethod, $firstMethodAmount = $this->faker->randomFloat(2, 100, $totalAmount / 2)),
            ],
            [
                ...$this->createPaymentForMethod($this->paybillPaymentMethod, $amount - $firstMethodAmount),
            ],
        ];

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $this->store->id,
            'payments' => $payments,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.orders.store'), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store_id' => $this->store->id,
        ]);

        $order = Order::query()->first();

        collect($payments)->each(function ($paymentData) use ($order) {
            $this->assertDatabaseHas('payments', [
                'author_user_id' => $this->user->id,
                'payable_id' => $order->id,
                'payable_type' => $order->getMorphClass(),
                'payment_method_id' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'status' => PaymentStatus::PAID->value,
            ]);
        });
    }

    public function test_backoffice_orders_store_route_with_custom_item_item_variant_and_item(): void
    {
        $this->withoutExceptionHandling();

        $items = [];

        $item = Item::factory()->create(['quantity' => 0, 'cost' => 100, 'price' => 150]);

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $qty = $this->faker->numberBetween(20, 100),
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
        ]);

        $this->assertEquals($qty, $item->fresh()->quantity);

        $items[] = [
            'product' => $item->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $item->price,
        ];

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create(['quantity' => 0, 'cost' => 100, 'price' => 150]);

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $this->faker->numberBetween(20, 100),
            'stockable_id' => $variant->id,
            'stockable_type' => $variant->getMorphClass(),
        ]);

        $items[] = [
            'product' => $variant->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $variant->price,
        ];

        $customItem = 'Custom Item Name';

        $items[] = [
            'product' => $customItem,
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->numberBetween(100, 1000),
        ];

        $customerData = $this->createCustomerData();

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);
        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);
        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $this->store->id,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.orders.store'), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store_id' => $this->store->id,
        ]);

        $dbCustomItem = CustomItem::query()->where('name', $customItem)->first();

        $order = $customer->orders()->first();

        $this->assertNotNull($order);

        $this->assertNotNull($dbCustomItem);

        $this->assertEquals(3, $order->items()->count());

        $this->assertTrue($order->items()->where('item_id', $dbCustomItem->id)->exists());

        $this->assertTrue($order->items()->where('item_id', $variant->id)->exists());

        $this->assertTrue($order->items()->where('item_id', $item->id)->exists());
    }

    public function test_backoffice_orders_update_route(): void
    {
        $this->withoutExceptionHandling();

        $organization = Organization::factory()->create();

        $item = Item::factory()->create();

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $this->faker->numberBetween(20, 100),
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
        ]);

        $order = Order::factory()->has(OrderItem::factory()->for($item, 'item'), 'items')->for($organization, 'customer')->create();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000);

        $totalAmount = $amount + $shippingAmount;

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'store' => $this->store->id,
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.orders.update', $order), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertDatabaseHas('orders', [
            'store_id' => $this->store->id,
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);

        $this->assertEquals(count($items), OrderItem::where('order_id', $order->id)->count());

        foreach ($items as $item) {
            $this->assertDatabaseHas('order_items', [
                'order_id' => $order->id,
                'item_id' => $item['product'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
    }

    public function test_backoffice_orders_update_route_partial_for_order_status(): void
    {
        $this->withoutExceptionHandling();

        $item = Item::factory()->create();

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $this->faker->numberBetween(20, 100),
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
        ]);

        $order = Order::factory()->has(OrderItem::factory()->for($item, 'item'), 'items')->for(Organization::factory(), 'customer')->create();

        $payload = [
            'order_status' => OrderStatus::CANCELED->value,
            'notes' => $this->faker->paragraph(),
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.orders.update', $order), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => data_get($payload, 'order_status'),
            'notes' => data_get($payload, 'notes'),
        ]);
    }

    public function test_backoffice_orders_destroy_route(): void
    {

        $item = Item::factory()->create(['quantity' => 0, 'cost' => 100, 'price' => 150]);

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $qty = $this->faker->numberBetween(20, 100),
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
        ]);

        $this->assertEquals($qty, $item->fresh()->quantity);

        $individual = Individual::factory()->create();

        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'customer_id' => $individual->id,
            'customer_type' => $individual->getMorphClass(),
            'total_amount' => 300,
            'amount' => 300,
            'shipping_amount' => 0,
            'order_status' => OrderStatus::PENDING,
        ]);

        app(OrderItemService::class)->create([
            'order' => $order,
            'product' => $item->id,
            'quantity' => $subQty = 2,
            'price' => $item->price,
            'check_stock_availability' => true,
        ]);

        $this->assertEquals(1, $order->items()->count());

        $method = PaymentMethod::factory()->create();

        Payment::factory()->for($order, 'payable')->create([
            'payment_method_id' => $method->id,
            'status' => PaymentStatus::PAID,
            'amount' => $order->total_amount,
        ]);

        $this->assertTrue(StockMovement::query()->where('action_id', $order->id)->doesntExist());

        app(OrderService::class)->markComplete($order);

        $this->assertEquals(OrderStatus::COMPLETED, $order->order_status);

        $this->assertEquals($qty - $subQty, $item->fresh()->quantity);

        $this->assertTrue(StockMovement::query()->where('action_id', $order->id)->exists());

        $response = $this->actingAs($this->user)->delete(route('backoffice.orders.destroy', $order));

        $response->assertRedirect(route('backoffice.orders.index'));

        $this->assertTrue(StockMovement::query()->where('action_id', $order->id)->doesntExist());

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);

        $this->assertEquals($qty, $item->fresh()->quantity);
    }

    public function test_backoffice_orders_export_route(): void
    {
        $this->withoutExceptionHandling();

        Excel::fake();

        Order::factory()->count(3)->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.export'));

        $response->assertStatus(200);

        $filename = Order::getExportFilename();

        Excel::assertDownloaded($filename);
    }

    public function test_backoffice_orders_mark_complete(): void
    {

        $store = Store::factory()->create();

        $order = Order::factory()->for($store, 'store')->for($this->user, 'author')->create(['order_status' => OrderStatus::PENDING]);

        $item = Item::factory()->create();

        StockMovement::factory()->for($store)->for($item, 'stockable')->create(['quantity' => 10]);

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        StockMovement::factory()->for($store)->for($variant, 'stockable')->create(['quantity' => 10]);

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

        Payment::factory()->for($order, 'payable')->create(['status' => PaymentStatus::PAID, 'amount' => $order->total_amount]);

        $this->assertFalse(StockMovement::query()->where('action_id', $order->id)->exists());

        $response = $this->actingAs($this->user)->patch(route('backoffice.orders.mark-complete', $order));

        $response->assertRedirect();

        $this->assertTrue(StockMovement::query()->where('action_id', $order->id)->exists());

        $order->refresh();

        $order->items->each(function ($orderItem) use ($order) {

            if ($orderItem->item_type === 'custom-item') {
                return;
            }

            $this->assertDatabaseHas('stock_movements', [
                'author_user_id' => $this->user->id,
                'action_id' => $order->id,
                'action_type' => $order->getMorphClass(),
                'stockable_id' => $orderItem->item_id,
                'stockable_type' => $orderItem->item_type,
                'quantity' => $orderItem->quantity * -1,
            ]);
        });

        $this->assertEquals(OrderStatus::COMPLETED, $order->order_status);
    }

    public function test_backoffice_orders_reports_route_happy_path(): void
    {
        Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.reports'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('backoffice/orders/ReportsPage')->hasAll('stores', 'authors', 'orderStatuses', 'statistics', 'params'));
    }

    public function test_backoffice_orders_reports_detailed_report_route(): void
    {
        Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->count(2)->create([
            'order_status' => OrderStatus::COMPLETED,
        ]);

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.reports.detailed-report'));

        $response->assertOk();

        $response->assertSessionHasNoErrors();
    }

    private function createPaymentForMethod(PaymentMethod $paymentMethod, float $amount): array
    {
        return [
            'payment_method' => $paymentMethod->id,
            'amount' => $amount,
            'phone_number' => $paymentMethod->id === $this->mpesaPaymentMethod->id ? str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value() : null,
        ];
    }

    private function createItems(): array
    {
        $items = Item::factory(2)->create([
            'quantity' => $this->faker->numberBetween(20, 100),
        ]);

        $items->each(function ($item) {

            StockMovement::query()->create([
                'store_id' => $this->store->id,
                'type' => StockMovementType::INITIAL,
                'quantity' => $this->faker->numberBetween(20, 100),
                'stockable_id' => $item->id,
                'stockable_type' => $item->getMorphClass(),
            ]);
        });

        return $items->map(function ($item) {
            return [
                'product' => $item->id,
                'quantity' => $this->faker->numberBetween(1, 10),
                'price' => $this->faker->randomFloat(2, 100, 1_000),
            ];
        })->all();
    }

    private function createCustomerData(): array
    {
        $individual = $this->faker->boolean();

        return [
            'type' => $individual ? ClientType::INDIVIDUAL->value : ClientType::ORGANIZATION->value,
            'id' => $individual ? $this->faker->name() : $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
        ];
    }
}
