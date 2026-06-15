<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@eventora.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_super_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password')
        ]);

        $org = \App\Models\Organization::create([
            'name' => 'Tech Conference Inc',
            'slug' => 'tech-conf',
            'primary_color' => '#4f46e5'
        ]);
        
        $org->users()->attach($user->id, ['role' => 'owner']);

        $event = $org->events()->create([
            'title' => 'Laravel Super Summit 2026',
            'slug' => 'laravel-super-summit-2026',
            'subtitle' => 'The ultimate gathering for artisan developers.',
            'description' => 'Join us for the most anticipated Laravel event of the year! Over three days, you will learn from industry experts, network with top developers, and dive deep into advanced topics like scalable architecture, serverless deployments, and the future of PHP. <br><br><b>What to expect:</b><ul><li>Keynote by Taylor Otwell</li><li>30+ Technical Sessions</li><li>Exclusive Networking Party</li><li>Swag Bag & Catering</li></ul><br>Whether you are a junior developer or a seasoned architect, the Super Summit has something to take your skills to the next level.',
            'short_description' => 'Join thousands of developers worldwide for the biggest Laravel conference.',
            'type' => 'hybrid',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(12),
            'timezone' => 'UTC',
            'status' => 'published',
            'venue_name' => 'Grand Convention Center',
            'venue_address' => '123 Tech Boulevard, Silicon Valley, CA 94000',
            'online_url' => 'https://zoom.us/j/1234567890',
            'hero_image' => 'events/hero.png',
            'refund_policy' => 'Tickets are fully refundable up to 7 days before the event. After that, no refunds will be issued.',
            'terms' => 'By purchasing a ticket, you agree to our Code of Conduct and event rules.',
        ]);

        $event->tickets()->create([
            'organization_id' => $org->id,
            'name' => 'General Admission',
            'type' => 'free',
            'price' => 0,
            'is_active' => true,
        ]);

        $event->tickets()->create([
            'organization_id' => $org->id,
            'name' => 'VIP Access',
            'type' => 'paid',
            'price' => 150.00,
            'quantity' => 50,
            'is_active' => true,
        ]);
    }
}
