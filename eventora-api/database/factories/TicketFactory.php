<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $isPaid = $this->faker->boolean(80); // 80% chance paid
        return [
            'name' => $this->faker->randomElement(['General Admission', 'VIP Access', 'Early Bird', 'Student Pass']),
            'description' => $this->faker->sentence(),
            'type' => $isPaid ? 'paid' : 'free',
            'price' => $isPaid ? $this->faker->randomFloat(2, 10, 500) : 0,
            'quantity' => $this->faker->randomElement([null, 50, 100, 200, 500]),
            'quantity_sold' => 0,
            'max_per_order' => $this->faker->randomElement([2, 4, 10]),
            'is_active' => true,
        ];
    }
}
