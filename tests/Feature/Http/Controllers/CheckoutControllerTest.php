<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Integrations\Mpesa\Requests\MpesaAuthorization;
use App\Http\Integrations\Mpesa\Requests\MpesaExpressSimulateRequest;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\TransactionStatus;
use App\Models\Individual;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\OnlineOrderReceivedNotification;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Saloon\Config as SaloonConfig;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\MockConfig;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private PaymentMethod $mpesaPaymentMethod;

    private Role $adminRole;

    private Role $staffRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'mpesa']);

        $this->adminRole = app(RoleService::class)->createOrUpdateAdminRole();

        $this->staffRole = app(RoleService::class)->createOrUpdateStaffRole();

        SaloonConfig::preventStrayRequests();

        MockConfig::setFixturePath(Storage::disk('local')->path('fixtures'));

        MockClient::global([
            MpesaAuthorization::class => MockResponse::fixture('mpesa_authorization'),
            MpesaExpressSimulateRequest::class => MockResponse::fixture('mpesa_express_simulate'),
        ]);
    }

    public function test_checkout_route(): void
    {
        $response = $this->get(route('checkout.show'));

        $response->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) {

            $page->component('checkout/ShowPage')->hasAll(['settings', 'categories', 'treeCategories', 'services']);
        });
    }

    public function test_checkout_process_route(): void
    {

        $this->withoutExceptionHandling();

        Notification::fake();

        User::factory()->count(2)->create()->each(function ($user) {
            $user->assignRole($this->staffRole);
        });

        User::factory()->count(1)->create()->each(function ($user) {
            $user->assignRole($this->adminRole);
        });

        $items = Item::factory(2)->create()->map(function ($item) {
            return [
                'product' => $item->id,
                'quantity' => $this->faker->numberBetween(1, 10),
                'price' => $this->faker->randomFloat(2, 100, 1_000),
            ];
        })->toArray();

        $customerData = [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
        ];

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
        ];

        $response = $this->post(route('checkout.process'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('individuals', [
            'name' => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'],
            'address' => $customerData['address'],
        ]);

        $customer = Individual::where('email', $customerData['email'])->first();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
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

        $users = User::role([
            $this->adminRole->name,
            $this->staffRole->name,
        ])->get();

        $users->each(function ($user) {
            Notification::assertSentTo($user, OnlineOrderReceivedNotification::class);
        });
    }

    public function test_checkout_process_route_with_authenticated_user(): void
    {

        $this->withoutExceptionHandling();

        Notification::fake();

        User::factory()->count(2)->create()->each(function ($user) {
            $user->assignRole($this->staffRole);
        });

        User::factory()->count(1)->create()->each(function ($user) {
            $user->assignRole($this->adminRole);
        });

        /** @var User $user */
        $user = User::factory()->create();

        $items = Item::factory(2)->create()->map(function ($item) {
            return [
                'product' => $item->id,
                'quantity' => $this->faker->numberBetween(1, 10),
                'price' => $this->faker->randomFloat(2, 100, 1_000),
            ];
        })->toArray();

        $customerData = [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
        ];

        $amount = $this->faker->randomFloat(2, 10_000, 100_000);

        $payload = [
            'items' => $items,
            'customer' => $customerData,
            'amount' => $amount,
        ];

        $response = $this->actingAs($user)->post(route('checkout.process'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('individuals', [
            'name' => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'],
            'address' => $customerData['address'],
        ]);

        $customer = Individual::where('email', $customerData['email'])->first();

        $this->assertDatabaseHas('orders', [
            'author_user_id' => $user->id,
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'customer_type' => $customer->getMorphClass(),
            'amount' => $amount,
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

        $users = User::role([
            $this->adminRole->name,
            $this->staffRole->name,
        ])->get();

        $users->each(function ($user) {
            Notification::assertSentTo($user, OnlineOrderReceivedNotification::class);
        });
    }

    public function test_checkout_order_received_route(): void
    {
        $order = Order::factory()->has(OrderItem::factory()->count(2)->for(Item::factory()), 'items')->create();

        $response = $this->get(route('checkout.order-received', ['order' => $order]));

        $response->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) use ($order) {
            $page->component('checkout/OrderReceivedPage')
                ->has('order', function (AssertableInertia $page) use ($order) {
                    $page->where('id', $order->id)
                        ->where('reference', $order->reference)
                        ->where('amount', $order->amount)
                        ->where('order_status', $order->order_status)
                        ->has('items')
                        ->etc();
                })->etc();
        });
    }
}
