<?php

namespace Tests\Feature;

use App\Mail\DailySalesSummaryMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_confirmation_mail_is_sent(): void
    {
        Mail::fake();

        $org = Organization::create([
            'name' => 'Test Organization',
            'slug' => 'test-organization',
        ]);

        $event = Event::create([
            'organization_id' => $org->id,
            'title' => 'Awesome Tech Conference',
            'slug' => 'awesome-tech-conference',
            'type' => 'offline',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(6),
            'timezone' => 'UTC',
            'status' => 'published',
        ]);

        $order = Order::create([
            'organization_id' => $org->id,
            'event_id' => $event->id,
            'order_number' => 'ORD-123456',
            'buyer_name' => 'John Doe',
            'buyer_email' => 'john@example.com',
            'subtotal' => 100.00,
            'total' => 100.00,
            'status' => 'paid',
        ]);

        Mail::to($order->buyer_email)->queue(new OrderConfirmationMail($order));

        Mail::assertQueued(OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($order->buyer_email);
        });
    }

    public function test_daily_sales_summary_command_dispatches_email_to_owner(): void
    {
        Mail::fake();

        // 1. Create User/Owner
        $owner = User::factory()->create([
            'email' => 'owner@eventora.test',
        ]);

        // 2. Create Organization and attach owner
        $org = Organization::create([
            'name' => 'Daily Sales Org',
            'slug' => 'daily-sales-org',
        ]);
        $org->users()->attach($owner->id, ['role' => 'owner']);

        // 3. Create Event
        $event = Event::create([
            'organization_id' => $org->id,
            'title' => 'Daily Test Event',
            'slug' => 'daily-test-event',
            'type' => 'offline',
            'start_date' => Carbon::now()->addDays(2),
            'end_date' => Carbon::now()->addDays(3),
            'timezone' => 'UTC',
            'status' => 'published',
        ]);

        // Set yesterday's date
        $yesterday = Carbon::yesterday();

        // Ensure settings opt-in for daily sales
        $org->settings = ['notify_daily_sales' => true];
        $org->save();

        // 4. Create Paid Order from yesterday
        $order = Order::create([
            'organization_id' => $org->id,
            'event_id' => $event->id,
            'order_number' => 'ORD-YESTERDAY',
            'buyer_name' => 'Jane Smith',
            'buyer_email' => 'jane@example.com',
            'subtotal' => 150.00,
            'total' => 150.00,
            'status' => 'paid',
            'created_at' => $yesterday,
        ]);

        // Create Ticket
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'organization_id' => $org->id,
            'name' => 'General Admission',
            'type' => 'paid',
            'price' => 150.00,
        ]);

        // 5. Create an Attendee (ticket) for the order to make totalTickets calculation correct
        Attendee::create([
            'organization_id' => $org->id,
            'event_id' => $event->id,
            'order_id' => $order->id,
            'ticket_id' => $ticket->id,
            'ticket_number' => 'TCK-12345',
            'qr_code' => 'TEST-QR-123',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'status' => 'confirmed',
        ]);

        // 6. Execute the daily summary command
        \Carbon\Carbon::setTestNow($yesterday->copy()->addDay());
        $this->artisan('app:send-daily-sales')
            ->assertExitCode(0);

        // 7. Verify that the DailySalesSummaryMail was sent to the owner
        Mail::assertQueued(DailySalesSummaryMail::class, function ($mail) use ($owner, $org) {
            return $mail->organization->id === $org->id &&
                   $mail->hasTo($owner->email) &&
                   $mail->totalSales == 150.00 &&
                   $mail->totalTickets == 1;
        });
    }
}
