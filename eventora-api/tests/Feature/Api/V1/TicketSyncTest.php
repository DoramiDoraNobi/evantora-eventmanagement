<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketSyncTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $organization;
    protected $event;
    protected $ticket;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        
        // Attach user to organization
        $this->organization->users()->attach($this->user->id, ['role' => 'admin']);

        $this->event = Event::factory()->create(['organization_id' => $this->organization->id]);
        $this->ticket = Ticket::factory()->create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id
        ]);
        
        $this->order = Order::factory()->create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'status' => 'paid',
        ]);
    }

    public function test_can_download_tickets_for_offline_sync()
    {
        $attendees = Attendee::factory()->count(3)->create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'ticket_id' => $this->ticket->id,
            'order_id' => $this->order->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/organizer/{$this->organization->id}/events/{$this->event->id}/tickets-sync");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonCount(3, 'data');

        $data = $response->json('data');
        $this->assertEquals($attendees[0]->qr_code, $data[0]['qr_code']);
    }

    public function test_can_sync_offline_checkins_to_server()
    {
        $attendee = Attendee::factory()->create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'ticket_id' => $this->ticket->id,
            'order_id' => $this->order->id,
            'status' => 'confirmed'
        ]);

        $scannedAt = now()->toDateTimeString();

        $payload = [
            'scanned_tickets' => [
                [
                    'qr_code' => $attendee->qr_code,
                    'scanned_at' => $scannedAt,
                ]
            ]
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/organizer/{$this->organization->id}/events/{$this->event->id}/tickets-sync", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Successfully synced 1 tickets.',
                 ]);

        $this->assertDatabaseHas('attendees', [
            'id' => $attendee->id,
            'status' => 'checked_in',
        ]);

        $this->assertDatabaseHas('checkin_logs', [
            'attendee_id' => $attendee->id,
            'method' => 'qr_scan',
        ]);
    }

    public function test_sync_ignores_already_checked_in_tickets()
    {
        $attendee = Attendee::factory()->create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'ticket_id' => $this->ticket->id,
            'order_id' => $this->order->id,
            'status' => 'checked_in'
        ]);

        $scannedAt = now()->toDateTimeString();

        $payload = [
            'scanned_tickets' => [
                [
                    'qr_code' => $attendee->qr_code,
                    'scanned_at' => $scannedAt,
                ]
            ]
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/organizer/{$this->organization->id}/events/{$this->event->id}/tickets-sync", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Successfully synced 0 tickets.',
                 ])
                 ->assertJsonFragment([
                     'failed_tickets' => [$attendee->qr_code]
                 ]);
    }
}
