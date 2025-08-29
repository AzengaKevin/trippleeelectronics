<?php

namespace Tests\Feature\Models;

use App\Models\Enums\PaymentMethodFieldOption;
use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_payment_methods_works()
    {
        $attributes = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'properties' => ['key' => 'value'],
        ];

        $paymentMethod = PaymentMethod::create($attributes);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $paymentMethod->id,
            'name' => $paymentMethod->name,
            'description' => $paymentMethod->description,
        ]);

        $this->assertEquals($attributes['name'], $paymentMethod->name);

        $this->assertEquals($attributes['description'], $paymentMethod->description);

        $this->assertEquals($attributes['properties'], $paymentMethod->properties);
    }

    public function test_creating_paybill_payment_method()
    {

        $attributes = [
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
        ];

        $paymentMethod = PaymentMethod::query()->create($attributes);

        $this->assertNotNull($paymentMethod);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $paymentMethod->id,
            'name' => $paymentMethod->name,
            'description' => $paymentMethod->description,
            'default_payment_status' => $paymentMethod->default_payment_status,
        ]);

        $this->assertEquals($attributes['required_fields'], $paymentMethod->required_fields);

        $this->assertEquals($attributes['properties'], $paymentMethod->properties);

        collect($requiredFields)->each(fn ($field) => $this->assertNotNull($paymentMethod->{$field}));
    }

    public function test_creating_payment_methods_from_factory_works()
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $this->assertDatabaseHas('payment_methods', [
            'id' => $paymentMethod->id,
            'name' => $paymentMethod->name,
        ]);

        $this->assertNotEmpty($paymentMethod->name);
        $this->assertNotEmpty($paymentMethod->description);
        $this->assertIsArray($paymentMethod->properties);
    }
}
