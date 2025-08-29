<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Http\Integrations\Mpesa\Requests\MpesaAuthorization;
use App\Http\Integrations\Mpesa\Requests\MpesaExpressSimulateRequest;
use App\Models\Enums\ClientType;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\StockMovementType;
use App\Models\Enums\TransactionStatus;
use App\Models\Individual;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Saloon\Config as SaloonConfig;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\MockConfig;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class POSControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, RefreshDatabase;

    private ?User $user = null;

    private ?Store $store = null;

    private PaymentMethod $cashPaymentMethod;

    private PaymentMethod $mpesaPaymentMethod;

    private PaymentMethod $paybillPaymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: ['access-backoffice', 'access-pos', 'create-orders']);

        $this->store = Store::factory()->create();

        $this->user->stores()->attach($this->store);

        $this->cashPaymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $this->mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'Mpesa']);

        $this->paybillPaymentMethod = PaymentMethod::factory()->create(['name' => 'Paybill']);

        PaymentSettings::fake([
            'cash_payment_method' => $this->cashPaymentMethod->id,
            'mpesa_payment_method' => $this->mpesaPaymentMethod->id,
        ]);

        SaloonConfig::preventStrayRequests();

        MockConfig::setFixturePath(Storage::disk('local')->path('fixtures'));

        MockClient::global([
            MpesaAuthorization::class => MockResponse::fixture('mpesa_authorization'),
            MpesaExpressSimulateRequest::class => MockResponse::fixture('mpesa_express_simulate'),
        ]);
    }

    public function test_backoffice_pos_route_happy()
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.pos'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll(['orders', 'stores', 'orders', 'order', 'latestOrder', 'primaryTax']));
    }

    public function test_backoffice_pos_process_route_happy()
    {

        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
            'total_amount' => $amount,
            'pay_later' => true,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos'), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertNotNull($customer, 'Customer should be created or found.');

        $this->assertDatabaseHas('orders', [
            'author_user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
        ]);

        $order = $customer->orders()->first();

        $this->assertNotNull($order);

        $this->assertEquals(0, $order->payments()->count(), 'Order should not have any payments associated with it.');

        $this->assertFalse(StockMovement::query()->where([
            ['action_id', '=', $order->id],
            ['action_type', '=', $order->getMorphClass()],
        ])->exists(), 'Order should not have any stock movements associated with it.');
    }

    public function test_backoffice_pos_process_route_happy_without_customer()
    {

        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $payments = [
            [
                ...$this->createPaymentForMethod($this->cashPaymentMethod, $firstMethodAmount = $this->faker->randomFloat(2, 100, $amount / 2)),
            ],
            [
                ...$this->createPaymentForMethod($this->paybillPaymentMethod, $amount - $firstMethodAmount),
            ],
        ];

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'payments' => $payments,
            'amount' => $amount,
            'total_amount' => $amount,
            'tendered_amount' => $amount,
            'balance_amount' => 0,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'author_user_id' => $this->user->id,
            'amount' => $amount,
            'total_amount' => $amount,
            'tendered_amount' => $amount,
            'balance_amount' => 0,
        ]);

        /** @var Order $order */
        $order = Order::query()->first();

        $this->assertNotNull($order);

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

    public function test_backoffice_pos_process_route_happy_without_payment()
    {

        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 50, 300),
            'total_amount' => $totalAmount = $amount + $shippingAmount,
            'balance_amount' => $balanceAmount = $this->faker->randomFloat(2, 50, 500),
            'tendered_amount' => $totalAmount + $balanceAmount,
            'pay_later' => true,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos'), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertDatabaseHas('orders', [
            'author_user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
            'tendered_amount' => $totalAmount + $balanceAmount,
        ]);

        $order = $customer->orders()->first();

        $this->assertNotNull($order);

        $this->assertEquals(0, $order->payments()->count(), 'Order should not have any payments associated with it.');

        $this->assertFalse(StockMovement::query()->where([
            ['action_id', '=', $order->id],
            ['action_type', '=', $order->getMorphClass()],
        ])->exists());
    }

    public function test_backoffice_pos_process_route_happy_with_cash_payment()
    {

        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $shippingAmount = $this->faker->randomFloat(2, 50, 300);

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $totalAmount = $amount + $shippingAmount;

        $payments = [$this->createPaymentForMethod($this->cashPaymentMethod, $totalAmount)];

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'payments' => $payments,
            'customer' => $customerData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount = $this->faker->randomFloat(2, 50, 500),
            'tendered_amount' => $totalAmount + $balanceAmount,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos'), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertDatabaseHas('orders', [
            'author_user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
            'tendered_amount' => $totalAmount + $balanceAmount,
        ]);

        $order = $customer->orders()->first();

        $this->assertNotNull($order);

        $payment = $order->payments()->first();

        $this->assertEquals(PaymentStatus::PAID, $payment->status);

        $this->assertNotNull($payment);

        $this->assertFalse(StockMovement::query()->where([
            ['action_id', '=', $order->id],
            ['action_type', '=', $order->getMorphClass()],
        ])->doesntExist());
    }

    public function test_backoffice_pos_process_route_happy_with_mpesa_payment()
    {
        $this->withoutExceptionHandling();

        $items = $this->createItems();

        $customerData = $this->createCustomerData();

        $shippingAmount = $this->faker->randomFloat(2, 50, 300);

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $totalAmount = $amount + $shippingAmount;

        $payments = [$this->createPaymentForMethod($this->mpesaPaymentMethod, $totalAmount)];

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'payments' => $payments,
            'customer' => $customerData,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount = $this->faker->randomFloat(2, 50, 500),
            'tendered_amount' => $totalAmount + $balanceAmount,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos'), $payload);

        $response->assertRedirect();

        $customer = data_get($customerData, 'type') === ClientType::INDIVIDUAL->value
            ? Individual::where('name', $customerData['id'])->first()
            : Organization::where('name', $customerData['id'])->first();

        $this->assertDatabaseHas('orders', [
            'author_user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
            'tendered_amount' => $totalAmount + $balanceAmount,
        ]);

        $order = $customer->orders()->first();

        $this->assertNotNull($order);

        $payment = $order->payments()->first();

        $this->assertNotNull($payment);

        $this->assertEquals(PaymentStatus::UNPAID, $payment->status);

        $transaction = $payment->transaction;

        $this->assertNotNull($transaction);

        $this->assertEquals($transaction->status, TransactionStatus::INITIATED);

        $this->assertNotNull($transaction->data['CheckoutRequestID']);

        $this->assertEquals($order->id, $payment->payable_id);

        $this->assertFalse(StockMovement::query()->where([
            ['action_id', '=', $order->id],
            ['action_type', '=', $order->getMorphClass()],
        ])->exists());
    }

    public function test_backoffice_pos_process_route_updating_processing(): void
    {
        $item = Item::factory()->create(['cost' => 100, 'price' => 150, 'quantity' => 0]);

        $organization = Organization::factory()->create();

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $this->faker->numberBetween(20, 100),
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
        ]);

        $order = Order::factory()->for($organization, 'customer')->create(['order_status' => OrderStatus::PENDING, 'author_user_id' => $this->user->id]);

        OrderItem::factory()->for($order)->for($item)->create([
            'quantity' => 1,
            'price' => $item->price,
        ]);

        $items = [];

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
            'quantity' => 1,
            'price' => $variant->price,
        ];

        $amount = $item->price + $variant->price;

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'amount' => $amount,
            'customer' => $this->createCustomerData(),
            'shipping_amount' => $shippingAmount = round($this->faker->randomFloat(2, 50, 300), 2),
            'total_amount' => $totalAmount = round($amount + $shippingAmount, 2),
            'balance_amount' => $balanceAmount = round($this->faker->randomFloat(2, 50, 500), 2),
            'tendered_amount' => $totalAmount + $balanceAmount,
            'pay_later' => true,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos', ['reference' => $order->reference]), $payload);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'author_user_id' => $this->user->id,
            'amount' => $amount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
            'tendered_amount' => $totalAmount + $balanceAmount,
        ]);

        $this->assertEquals(count($items), $order->items()->count());

        $response->assertRedirect();
    }

    public function test_backoffice_pos_process_route_with_tax_and_discount(): void
    {

        $item = Item::factory()->create(['cost' => 100, 'price' => 150, 'quantity' => 0]);

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $this->faker->numberBetween(20, 100),
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
        ]);

        $item2 = Item::factory()->create(['cost' => 200, 'price' => 300, 'quantity' => 0]);

        StockMovement::query()->create([
            'store_id' => $this->store->id,
            'type' => StockMovementType::INITIAL,
            'quantity' => $this->faker->numberBetween(20, 100),
            'stockable_id' => $item2->id,
            'stockable_type' => $item2->getMorphClass(),
        ]);

        $items = [];

        $items[] = [
            'product' => $item->id,
            'quantity' => 1,
            'price' => $item->price,
            'taxable' => true,
            'tax_rate' => 16,
            'discount_rate' => 10,
        ];

        $items[] = [
            'product' => $item2->id,
            'quantity' => 1,
            'price' => $item2->price,
            'taxable' => true,
            'tax_rate' => 16,
            'discount_rate' => 10,
        ];

        $payload = [
            'store' => $this->store->id,
            'items' => $items,
            'customer' => $this->createCustomerData(),
            'amount' => $amount = array_sum(array_column($items, 'price')),
            'shipping_amount' => $shippingAmount = round($this->faker->randomFloat(2, 50, 300), 2),
            'total_amount' => $totalAmount = round($amount + $shippingAmount, 2),
            'balance_amount' => $balanceAmount = round($this->faker->randomFloat(2, 50, 500), 2),
            'tendered_amount' => $totalAmount + $balanceAmount,
            'pay_later' => true,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.pos'), $payload);

        $order = Order::query()->first();

        $this->assertNotNull($order);

        collect($items)->each(function ($itemData) use ($order) {
            $this->assertDatabaseHas('order_items', [
                'order_id' => $order->id,
                'item_id' => $itemData['product'],
                'price' => $itemData['price'],
                'tax_rate' => $itemData['tax_rate'],
                'discount_rate' => $itemData['discount_rate'],
            ]);
        });

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();
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
                'taxable' => $this->faker->boolean(),
                'tax_rate' => $this->faker->randomFloat(2, 0, 16),
                'discount_rate' => $this->faker->randomFloat(2, 0, 16),
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
