<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending orders that have exceeded their expiry time and release their ticket stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredOrders = Order::with('attendees')
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired orders found.');
            return;
        }

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    // Group attendees by ticket_id to know how many to restore per ticket
                    $ticketCounts = $order->attendees->groupBy('ticket_id')->map->count();
                    
                    // Sort ticket IDs to prevent deadlocks when acquiring locks
                    $ticketIds = $ticketCounts->keys()->sort()->values();

                    foreach ($ticketIds as $ticketId) {
                        $ticket = Ticket::lockForUpdate()->find($ticketId);
                        if ($ticket) {
                            $ticket->decrement('quantity_sold', $ticketCounts[$ticketId]);
                        }
                    }

                    // Restore coupon usage if any
                    if ($order->coupon_id) {
                        $coupon = \App\Models\Coupon::lockForUpdate()->find($order->coupon_id);
                        if ($coupon && $coupon->used_count > 0) {
                            $coupon->decrement('used_count');
                        }
                    }

                    // Cancel the order and attendees
                    $order->update(['status' => 'cancelled']);
                    $order->attendees()->update(['status' => 'cancelled']);
                });

                $this->info("Released stock for expired order: {$order->order_number}");
                $releasedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to release order {$order->order_number}: " . $e->getMessage());
                Log::error("Failed to release order {$order->order_number}", ['error' => $e->getMessage()]);
            }
        }

        $this->info("Successfully released {$releasedCount} expired orders.");
    }
}
