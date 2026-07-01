<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Event;
use App\Models\Attendee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/organizer/{orgId}/dashboard — Dashboard stats
     */
    public function index(Request $request, $orgId)
    {
        $organization = app('current_organization');
        $orgId = $organization->id;

        // Summary Cards
        $grossRevenue = Order::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->sum('total');

        $ticketsSold = Attendee::where('organization_id', $orgId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        $activeEventsCount = Event::where('organization_id', $orgId)
            ->where('status', 'published')
            ->where('end_date', '>=', now())
            ->count();

        $totalCheckins = Attendee::where('organization_id', $orgId)
            ->where('status', 'checked_in')
            ->count();

        // 30-Day Revenue Trend
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $dates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        $salesData = Order::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        $trendData = [];
        foreach ($dates as $date) {
            $trendData[] = [
                'date' => $date,
                'revenue' => (float) ($salesData[$date] ?? 0),
            ];
        }

        // Sales by Event (Top 5)
        $salesByEvent = Order::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->select('event_id', DB::raw('SUM(total) as revenue'))
            ->groupBy('event_id')
            ->with('event:id,title')
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'event_id' => $item->event_id,
                'event_title' => $item->event?->title ?? 'Unknown',
                'revenue' => (float) $item->revenue,
            ]);

        // Recent Orders
        $recentOrders = Order::with('event:id,title')
            ->where('organization_id', $orgId)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_name' => $order->buyer_name,
                'total' => (float) $order->total,
                'status' => $order->status,
                'event_title' => $order->event?->title,
                'created_at' => $order->created_at,
            ]);

        return response()->json([
            'data' => [
                'summary' => [
                    'gross_revenue' => (float) $grossRevenue,
                    'tickets_sold' => $ticketsSold,
                    'active_events' => $activeEventsCount,
                    'total_checkins' => $totalCheckins,
                ],
                'revenue_trend' => $trendData,
                'sales_by_event' => $salesByEvent,
                'recent_orders' => $recentOrders,
            ]
        ]);
    }
}
