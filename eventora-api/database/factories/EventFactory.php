<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->catchPhrase() . ' ' . $this->faker->randomElement(['Conference', 'Summit', 'Festival', 'Workshop', 'Meetup']);
        $startDate = $this->faker->dateTimeBetween('-1 month', '+3 months');
        $endDate = (clone $startDate)->modify('+' . rand(1, 3) . ' days');
        
        $types = ['offline', 'online', 'hybrid'];
        $type = $types[array_rand($types)];
        
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(4),
            'subtitle' => $this->faker->sentence(),
            'description' => '<p>' . implode('</p><p>', $this->faker->paragraphs(4)) . '</p>',
            'short_description' => $this->faker->paragraph(1),
            'type' => $type,
            'category_id' => \App\Models\Category::inRandomOrder()->first()?->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'timezone' => $this->faker->timezone(),
            'status' => $this->faker->randomElement(['draft', 'published', 'published', 'published']), // More likely to be published
            'venue_name' => $type !== 'online' ? $this->faker->company() . ' Center' : null,
            'venue_address' => $type !== 'online' ? $this->faker->address() : null,
            'online_url' => $type !== 'offline' ? $this->faker->url() : null,
            'hero_image' => 'events/hero.png', // Fallback image
            'refund_policy' => $this->faker->paragraph(),
            'terms' => $this->faker->paragraph(),
        ];
    }
}
