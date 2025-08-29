<?php

namespace Tests\Feature\Models;

use App\Models\Enums\PaymentMethodFieldOption;
use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_store_record(): void
    {
        $attributes = [
            'name' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'location' => $this->faker->city(),
        ];

        $store = Store::query()->create($attributes);

        $this->assertNotNull($store);

        $this->assertDatabaseHas('stores', $attributes);
    }

    public function test_creating_a_new_store_record_with_email_phone_paybill_an_more(): void
    {
        $store = Store::factory()->create();

        $this->assertNotNull($store);

        $attributes = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till' => $this->faker->numerify('######'),
            'kra_pin' => $this->faker->numerify('KRA-######'),
            'address' => $this->faker->streetAddress(),
            'location' => $this->faker->city(),
        ];

        $store->update($attributes);

        $this->assertDatabaseHas('stores', $attributes);
    }

    public function test_create_a_new_store_with_short_name(): void
    {
        $attributes = [
            'name' => $this->faker->name(),
            'short_name' => str($this->faker->lexify('???'))->upper()->value(),
            'address' => $this->faker->streetAddress(),
            'location' => $this->faker->city(),
        ];

        $store = Store::query()->create($attributes);

        $this->assertNotNull($store);

        $this->assertDatabaseHas('stores', $attributes);
    }

    public function test_creating_a_store_from_factory(): void
    {
        $store = Store::factory()->create();

        $this->assertNotNull($store);
    }

    public function test_store_author_relationship_method(): void
    {
        $store = Store::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(\App\Models\User::class, $store->author);

        $this->assertEquals($store->author_user_id, $store->author->id);
    }

    public function test_store_payment_methods_relationship_method(): void
    {
        /** @var Store $store */
        $store = Store::factory()->create();

        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Lipa Na Mpesa Paybill',
            'description' => $this->faker->sentence(),
            'required_fields' => $requiredFields = [
                PaymentMethodFieldOption::PAYBILL_NUMBER->value,
                PaymentMethodFieldOption::ACCOUNT_NUMBER->value,
                PaymentMethodFieldOption::ACCOUNT_NAME->value,
            ],
            'default_payment_status' => PaymentStatus::UNPAID->value,
            'properties' => $properties = [
                PaymentMethodFieldOption::PAYBILL_NUMBER->value => '247247',
                PaymentMethodFieldOption::ACCOUNT_NUMBER->value => '838888',
                PaymentMethodFieldOption::ACCOUNT_NAME->value => 'Kisii Computer Store',
            ],
            ...$properties,
        ]);

        $store->paymentMethods()->attach($paymentMethod, $attributes = [
            'phone_number' => $this->faker->phoneNumber(),
            'paybill_number' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till_number' => $this->faker->numerify('######'),
            'account_name' => $this->faker->name(),
        ]);

        $this->assertCount(1, $store->paymentMethods);

        $this->assertDatabaseHas('payment_method_store', [
            'store_id' => $store->id,
            'payment_method_id' => $paymentMethod->id,
            ...$attributes,
        ]);
    }
}
