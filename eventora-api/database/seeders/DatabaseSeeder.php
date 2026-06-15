<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\Attendee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Users
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@eventora.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
        ]);

        $testUser = User::factory()->create([
            'name' => 'Test Organizer',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
        ]);
        
        $buyerUser = User::factory()->create([
            'name' => 'Demo Buyer',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
        ]);

        // 2. Create the Primary Demo Organization for testUser
        $demoOrg = Organization::factory()->create([
            'name' => 'Eventora Demo Org',
            'slug' => 'eventora-demo',
            'primary_color' => '#4f46e5'
        ]);
        $demoOrg->users()->attach($testUser->id, ['role' => 'owner']);

        // Generate more random organizations
        $orgs = Organization::factory()->count(10)->create();
        $orgs->push($demoOrg); // Include demoOrg in the collection for seeding events

        // 3. For each Organization, generate Events, Tickets, Orders, and Attendees
        foreach ($orgs as $org) {
            
            // Add some random staff to each org (except the demo one which we already set up)
            if ($org->id !== $demoOrg->id) {
                $staffs = User::factory()->count(2)->create();
                foreach($staffs as $staff) {
                    $org->users()->attach($staff->id, ['role' => 'admin']);
                }
            }

            // Generate 5-10 Events per Organization
            $events = Event::factory()->count(rand(5, 10))->create([
                'organization_id' => $org->id
            ]);

            foreach ($events as $event) {
                // Generate 2-4 Tickets per Event
                $tickets = Ticket::factory()->count(rand(2, 4))->create([
                    'organization_id' => $org->id,
                    'event_id' => $event->id
                ]);

                // Only generate orders/attendees if the event is published
                if ($event->status === 'published') {
                    
                    // Generate 10-30 Orders for this event
                    $orders = Order::factory()->count(rand(10, 30))->create([
                        'organization_id' => $org->id,
                        'event_id' => $event->id,
                    ]);

                    foreach ($orders as $order) {
                        // For each order, create 1-3 Attendees
                        $numAttendees = rand(1, 3);
                        $ticketForOrder = $tickets->random();
                        
                        // Update order totals
                        $order->subtotal = $ticketForOrder->price * $numAttendees;
                        $order->total = $order->subtotal;
                        $order->status = $order->total > 0 ? 'paid' : 'pending';
                        
                        // Ensure an order is assigned to a realistic buyer 
                        $order->user_id = (rand(1, 10) > 8) ? $buyerUser->id : User::inRandomOrder()->first()->id;
                        $order->save();

                        $attendees = Attendee::factory()->count($numAttendees)->make([
                            'organization_id' => $org->id,
                            'event_id' => $event->id,
                            'order_id' => $order->id,
                            'ticket_id' => $ticketForOrder->id,
                        ]);

                        foreach($attendees as $attendee) {
                            if ($order->status !== 'paid' && $order->total > 0) {
                                $attendee->status = 'registered';
                                $attendee->checked_in_at = null;
                            }
                            $attendee->save();
                        }
                        
                        // Update ticket quantity sold
                        $ticketForOrder->increment('quantity_sold', $numAttendees);
                    }
                }
            }
        }
    }
}
