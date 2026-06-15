<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Order;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $expiredOrders = Order::with('attendees.ticket')
        ->where('status', 'pending')
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->get();

    foreach ($expiredOrders as $order) {
        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $ticketCounts = [];
            foreach ($order->attendees as $attendee) {
                if ($attendee->ticket_id) {
                    if (!isset($ticketCounts[$attendee->ticket_id])) {
                        $ticketCounts[$attendee->ticket_id] = [
                            'ticket' => $attendee->ticket,
                            'count' => 0
                        ];
                    }
                    $ticketCounts[$attendee->ticket_id]['count']++;
                }
            }

            foreach ($ticketCounts as $data) {
                if ($data['ticket']) {
                    $data['ticket']->decrement('quantity_sold', $data['count']);
                }
            }

            $order->update(['status' => 'failed']);
            foreach ($order->attendees as $attendee) {
                $attendee->update(['status' => 'cancelled']);
            }
        });
    }
})->everyMinute();

// Send Daily Sales Summary at 08:00 AM
Schedule::command('app:send-daily-sales')->dailyAt('08:00');
