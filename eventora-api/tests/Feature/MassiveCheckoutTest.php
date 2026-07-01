<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassiveCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_massive_checkout_limit_validation()
    {
        $organization = Organization::factory()->create(['is_active' => true]);
        $event = Event::factory()->create(['organization_id' => $organization->id, 'status' => 'published']);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'price' => 10,
            'quantity' => 100,
            'max_per_order' => 10,
            'min_per_order' => 1,
            'sales_start' => now()->subDay(),
            'sales_end' => now()->addDay(),
        ]);

        $user = User::factory()->create();

        $attendees = [];
        for ($i = 0; $i < 25; $i++) {
            $attendees[] = [
                'name' => "Attendee $i",
                'email' => "attendee$i@test.com"
            ];
        }

        $response = $this->actingAs($user)->postJson("/api/v1/events/{$event->slug}/checkout", [
            'buyer_name' => 'John Doe',
            'buyer_email' => 'john@test.com',
            'tickets' => [
                [
                    'ticket_id' => $ticket->id,
                    'quantity' => 25
                ]
            ],
            'attendees' => $attendees
        ]);

        // Expect it to fail because 25 > max_per_order (10)
        $response->assertStatus(422);
        $this->assertStringContainsString('must be between 1 and 10', $response->json('message'));
        
        // Also test the overall hard limit of 100
        $ticket2 = Ticket::factory()->create([
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'price' => 10,
            'quantity' => 200,
            'max_per_order' => 150, // Individual limit is 150
            'min_per_order' => 1,
            'sales_start' => now()->subDay(),
            'sales_end' => now()->addDay(),
        ]);
        
        $attendeesMassive = [];
        for ($i = 0; $i < 105; $i++) {
            $attendeesMassive[] = [
                'name' => "Attendee $i",
                'email' => "attendee$i@test.com"
            ];
        }

        $response2 = $this->actingAs($user)->postJson("/api/v1/events/{$event->slug}/checkout", [
            'buyer_name' => 'John Doe',
            'buyer_email' => 'john@test.com',
            'tickets' => [
                [
                    'ticket_id' => $ticket2->id,
                    'quantity' => 105
                ]
            ],
            'attendees' => $attendeesMassive
        ]);

        // Expect it to fail overall limit (100)
        $response2->assertStatus(422);
        $this->assertStringContainsString('limit exceeded', $response2->json('message'));
    }
}
