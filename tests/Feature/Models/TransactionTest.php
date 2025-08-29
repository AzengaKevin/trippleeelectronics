<?php

namespace Tests\Feature\Models;

use App\Models\Enums\TransactionMethod;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_transaction_can_be_created()
    {
        $payment = Payment::factory()->create();

        $attributes = [
            'payment_id' => $payment->id,
            'transaction_method' => $this->faker->randomElement(TransactionMethod::options()),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'till' => $this->faker->numerify('######'),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('######'),
            'phone_number' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'reference' => null,
            'fee' => $this->faker->randomFloat(2, 0, 100),
        ];

        $transaction = Transaction::query()->create($attributes);

        $this->assertNotNull($transaction);

        $this->assertDatabaseHas('transactions', $attributes);

        $this->assertNotNull($transaction->local_reference);
    }

    public function test_transaction_author_relationship_method()
    {
        $transaction = Transaction::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(\App\Models\User::class, $transaction->author);

        $this->assertEquals($transaction->author_user_id, $transaction->author->id);
    }
}
