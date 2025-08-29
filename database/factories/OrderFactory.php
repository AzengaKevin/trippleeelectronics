<?php

namespace Database\Factories;

use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\RefundStatus;
use App\Models\Enums\ShippingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $amount = $this->faker->randomFloat(2, 0, 100000),
            'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 500),
            'total_amount' => $amount + $shippingAmount,
            'order_status' => $this->faker->randomElement(OrderStatus::options()),
            'payment_status' => $this->faker->randomElement(PaymentStatus::options()),
            'fulfillment_status' => $this->faker->randomElement(FulfillmentStatus::options()),
            'shipping_status' => $this->faker->randomElement(ShippingStatus::options()),
            'refund_status' => $this->faker->randomElement(RefundStatus::options()),
            'channel' => $this->faker->randomElement(['online', 'store-front', 'mobile']),
            'notes' => $this->faker->text(100),
        ];
    }
}
