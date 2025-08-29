<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Enums\PaymentStatus;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class PurchasePaymentControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    private PaymentMethod $cashPaymentMethod;

    private PaymentMethod $mpesaPaymentMethod;

    private PaymentMethod $paybillPaymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-purchases',
            'create-purchases',
            'update-orders',
            'create-payments',
            'browse-payments',
        ]);

        $this->cashPaymentMethod = PaymentMethod::factory()->create(['name' => 'Cash']);

        $this->mpesaPaymentMethod = PaymentMethod::factory()->create(['name' => 'Mpesa']);

        $this->paybillPaymentMethod = PaymentMethod::factory()->create(['name' => 'Paybill']);

        PaymentSettings::fake([
            'cash_payment_method' => $this->cashPaymentMethod->id,
            'mpesa_payment_method' => $this->mpesaPaymentMethod->id,
        ]);
    }

    public function test_backoffice_orders_payments_store_route()
    {

        /** @var Purchase $purchase */
        $purchase = Purchase::factory()->has(PurchaseItem::factory()->for(Item::factory(), 'item'), 'items')->create();

        $amount = $purchase->total_amount;

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

        $response = $this->actingAs($this->user)->post(route('backoffice.purchases.payments.store', $purchase), $payload);

        $response->assertRedirect();

        collect($payments)->each(function ($paymentData) use ($purchase) {
            $this->assertDatabaseHas('payments', [
                'author_user_id' => $this->user->id,
                'payable_id' => $purchase->id,
                'payable_type' => $purchase->getMorphClass(),
                'payment_method_id' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'status' => PaymentStatus::PAID->value,
            ]);
        });
    }

    private function createPaymentForMethod(PaymentMethod $paymentMethod, float $amount): array
    {
        return [
            'payment_method' => $paymentMethod->id,
            'amount' => $amount,
        ];
    }
}
