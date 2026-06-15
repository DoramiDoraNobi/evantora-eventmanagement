<?php

namespace Database\Factories;

use App\Models\Attendee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttendeeFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['registered', 'confirmed', 'checked_in']);
        return [
            'ticket_number' => 'TKT-' . strtoupper(Str::random(12)),
            'qr_code' => Str::uuid()->toString(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'status' => $status,
            'checked_in_at' => $status === 'checked_in' ? $this->faker->dateTimeThisMonth() : null,
        ];
    }
}
