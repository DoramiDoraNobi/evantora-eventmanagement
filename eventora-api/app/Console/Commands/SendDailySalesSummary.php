<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\Order;
use App\Mail\DailySalesSummaryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDailySalesSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a daily sales summary to all organizers.';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();
        $dateString = $yesterday->format('d M Y');

        $organizations = Organization::all();

        foreach ($organizations as $org) {
            // Check if the organization has opted into daily sales summaries
            if (!($org->settings['notify_daily_sales'] ?? false)) {
                continue;
            }

            // Get all paid orders from yesterday for this organization
            $orders = Order::whereHas('event', function ($query) use ($org) {
                $query->where('organization_id', $org->id);
            })
            ->where('status', 'paid')
            ->whereDate('created_at', $yesterday)
            ->with(['event', 'attendees'])
            ->get();

            if ($orders->isEmpty()) {
                continue; // Skip if no sales
            }

            $totalSales = $orders->sum('total');
            
            // Calculate total tickets sold by summing up the attendees
            $totalTickets = 0;
            foreach ($orders as $order) {
                $totalTickets += $order->attendees->count();
            }

            $eventsSummary = [];
            foreach ($orders->groupBy('event_id') as $eventId => $eventOrders) {
                $event = $eventOrders->first()->event;
                
                $eventTickets = 0;
                foreach ($eventOrders as $eo) {
                    $eventTickets += $eo->attendees->count();
                }

                $eventsSummary[] = [
                    'title' => $event->title,
                    'tickets' => $eventTickets,
                    'revenue' => $eventOrders->sum('total'),
                ];
            }

            // Send to the organization creator/owner
            $owner = $org->users()->wherePivot('role', 'owner')->first() ?? $org->users()->first();
            $recipientEmail = $owner?->email ?? $org->email;
            
            if ($recipientEmail) {
                Mail::to($recipientEmail)->queue(new DailySalesSummaryMail(
                    $org,
                    $dateString,
                    $totalSales,
                    $totalTickets,
                    $eventsSummary
                ));
            }
        }
        
        $this->info("Daily sales summaries dispatched for " . $dateString);
    }
}
