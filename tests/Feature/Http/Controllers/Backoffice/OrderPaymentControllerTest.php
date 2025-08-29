<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Http\Integrations\Mpesa\Requests\MpesaAuthorization;
use App\Http\Integrations\Mpesa\Requests\MpesaExpressSimulateRequest;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\StockMovementType;
use App\Models\Enums\TransactionStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Saloon\Config as SaloonConfig;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\MockConfig;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class OrderPaymentControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    private PaymentMethod $cashPaymentMethod;

    private PaymentMethod $mpesaPaymentMethod;

    private PaymentMethod $paybillPaymentMethod;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-orders',
            'create-orders',
            'update-orders',
            'create-payments',
            'browse-payments',
        ]);

        $this->cashPaymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $this->mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'Mpesa']);

        $this->paybillPaymentMethod = PaymentMethod::factory()->create(['name' => 'Paybill']);

        $this->store = Store::factory()->create();

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

    public function test_backoffice_orders_payments_index_route()
    {
        $order = Order::factory()->create();

        PaymentMethod::factory()->count(2)->create();

        Payment::factory()->for($order, 'payable')->for(PaymentMethod::factory(), 'paymentMethod')->count($paymentsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.orders.payments.index', $order));

        $response->assertStatus(200);

        $response->assertInertia(
            fn ($inertia) => $inertia
                ->component('backoffice/orders/payments/IndexPage')
                ->has('order')
                ->has('order.payments')
                ->has('order.customer')
                ->has('paymentMethods')
        );
    }

    public function test_backoffice_orders_payments_store_route()
    {

        $item = Item::factory()->create();

        StockMovement::query()->create([
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'store_id' => $this->store->id,
            'quantity' => 100,
            'type' => StockMovementType::INITIAL,
            'author_user_id' => $this->user->id,
        ]);

        /** @var Order $order */
        $order = Order::factory()->for($this->store, 'store')->has(OrderItem::factory()->for($item, 'item'), 'items')
            ->create(['order_status' => OrderStatus::PENDING->value]);

        $amount = $order->total_amount;

        $payments = [
            [
                ...$this->createPaymentForMethod($this->cashPaymentMethod, $firstMethodAmount = $this->faker->randomFloat(2, 100, $amount / 2)),
            ],
            [
                ...$this->createPaymentForMethod($this->paybillPaymentMethod, $amount - $firstMethodAmount),
            ],
        ];

        $payload = [
            'payments' => $payments,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.orders.payments.store', $order), $payload);

        $response->assertRedirect();

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

    public function test_backoffice_orders_payments_store_route_with_mpesa()
    {

        $item = Item::factory()->create();

        StockMovement::query()->create([
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'store_id' => $this->store->id,
            'quantity' => 100,
            'type' => StockMovementType::INITIAL,
            'author_user_id' => $this->user->id,
        ]);

        /** @var Order $order */
        $order = Order::factory()->for($this->store, 'store')->has(OrderItem::factory()->for($item, 'item'), 'items')->create();

        $amount = $order->total_amount;

        $payments = [$this->createPaymentForMethod($this->mpesaPaymentMethod, $amount)];

        $payload = ['payments' => $payments];

        $response = $this->actingAs($this->user)->post(route('backoffice.orders.payments.store', $order), $payload);

        $response->assertRedirect();

        $response->assertSessionHasNoErrors();

        $payment = $order->payments()->first();

        $this->assertNotNull($payment);

        $this->assertEquals(PaymentStatus::UNPAID, $payment->status);

        $transaction = $payment->transaction;

        $this->assertNotNull($transaction);

        $this->assertEquals($transaction->status, TransactionStatus::INITIATED);

        $this->assertNotNull($transaction->data['CheckoutRequestID']);

        $this->assertEquals($order->id, $payment->payable_id);
    }

    private function createPaymentForMethod(PaymentMethod $paymentMethod, float $amount): array
    {
        return [
            'payment_method' => $paymentMethod->id,
            'amount' => $amount,
            'phone_number' => $paymentMethod->id === $this->mpesaPaymentMethod->id ? str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value() : null,
        ];
    }
}
