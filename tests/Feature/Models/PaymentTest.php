<?php

namespace Tests\Feature\Models;

use App\Models\Enums\PaymentStatus;
use App\Models\Individual;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_payment_record_for_order(): void
    {

        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->id,
            'payer_type' => $individual->getMorphClass(),
            'payer_id' => $individual->id,
            'status' => $this->faker->randomElement(PaymentStatus::options()),
            'amount' => $individual->amount,
        ];

        $payment = Payment::query()->create($attributes);

        $this->assertNotNull($payment);

        $this->assertDatabaseHas('payments', $attributes);
    }

    public function test_creating_a_payment_with_payment_method(): void
    {
        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $individual = Individual::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create();

        $attributes = [
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->id,
            'payer_type' => $individual->getMorphClass(),
            'payer_id' => $individual->id,
            'payment_method_id' => $paymentMethod->id,
            'status' => PaymentStatus::PAID->value,
            'amount' => $individual->amount * 1,
        ];

        $payment = Payment::query()->create($attributes);

        $this->assertNotNull($payment);

        $this->assertDatabaseHas('payments', $attributes);
    }

    public function test_create_a_payment_with_phone_number(): void
    {
        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->id,
            'payer_type' => $individual->getMorphClass(),
            'payer_id' => $individual->id,
            'status' => PaymentStatus::UNPAID->value,
            'amount' => $individual->amount * 1,
            'phone_number' => str('+254')->append($this->faker->randomElement(['1', '7']), $this->faker->numerify('#########'))->value(),
        ];

        $payment = Payment::query()->create($attributes);

        $this->assertNotNull($payment);

        $this->assertDatabaseHas('payments', $attributes);
    }

    public function test_creating_a_new_payment_record_for_purchase(): void
    {
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->count(2)->for(Item::factory()), 'items')->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'payable_type' => $purchase->getMorphClass(),
            'payable_id' => $purchase->id,
            'payer_type' => $individual->getMorphClass(),
            'payer_id' => $individual->id,
            'status' => $this->faker->randomElement(PaymentStatus::options()),
            'amount' => $individual->amount * 1,
        ];

        $payment = Payment::query()->create($attributes);

        $this->assertNotNull($payment);

        $this->assertDatabaseHas('payments', $attributes);
    }

    public function test_creating_a_payment_from_factory(): void
    {
        $order = Order::factory()->has(OrderItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $individual = Individual::factory()->create();

        $payment = Payment::factory()->for($order, 'payable')->for($individual, 'payer')->create();

        $this->assertNotNull($payment->payable);
        $this->assertNotNull($payment->payer);

        $this->assertEquals($order->id, $payment->payable_id);
        $this->assertEquals($individual->id, $payment->payer_id);

        $this->assertDatabaseHas('payments', [
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->id,
            'payer_type' => $individual->getMorphClass(),
            'payer_id' => $individual->id,
            'amount' => $payment->amount,
        ]);
    }

    public function test_payment_author_relationship_method(): void
    {
        $payment = Payment::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(User::class, $payment->author);

        $this->assertEquals($payment->author->id, $payment->author_user_id);
    }
}
