<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 0, 1000);
        return [
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'buyer_name' => $this->faker->name(),
            'buyer_email' => $this->faker->safeEmail(),
            'buyer_phone' => $this->faker->phoneNumber(),
            'subtotal' => $amount,
            'discount' => 0,
            'total' => $amount,
            'status' => $amount > 0 ? $this->faker->randomElement(['pending', 'paid', 'paid', 'paid']) : 'paid',
            'currency' => 'USD',
            'paid_at' => $amount > 0 ? $this->faker->dateTimeThisYear() : null,
        ];
    }
}
