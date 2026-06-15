<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Event;
use App\Models\Attendee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. If Super Admin (and not impersonating a tenant), redirect to Super Admin Dashboard
        if ($user->is_super_admin && !app()->bound('current_organization')) {
            return redirect()->route('super-admin.dashboard');
        }

        // 2. If the user does not belong to any organization, redirect to buyer tickets
        if (!$user->organizations()->exists()) {
            return redirect()->route('buyer.my-tickets');
        }

        // 3. Ensure organization is bound (EnsureTenant middleware handles binding)
        if (!app()->bound('current_organization')) {
            return redirect()->route('buyer.my-tickets');
        }

        $organization = app('current_organization');
        
        // 4. Ensure user has a role in this organization
        if (!$user->currentTenantRole()) {
            return redirect()->route('buyer.my-tickets');
        }

        $orgId = $organization->id;

        // --- Summary Cards Data ---
        
        // 1. Total Revenue (Paid orders)
        $grossRevenue = Order::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->sum('total');

        // 2. Total Tickets Sold (Confirmed attendees)
        $ticketsSold = Attendee::where('organization_id', $orgId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        // 3. Active Events
        $activeEventsCount = Event::where('organization_id', $orgId)
            ->where('status', 'published')
            ->where('end_date', '>=', now())
            ->count();

        // 4. Total Check-ins
        $totalCheckins = Attendee::where('organization_id', $orgId)
            ->where('status', 'checked_in')
            ->count();


        // --- Chart Data: 30-Day Revenue Trend ---
        
        // Generate an array of the last 30 days
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $dates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        // Fetch sales grouped by date for the last 30 days
        $salesData = Order::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        // Map the fetched data to the complete 30-day array (filling missing days with 0)
        $trendData = [];
        foreach ($dates as $date) {
            $trendData[] = $salesData[$date] ?? 0;
        }

        // --- Chart Data: Sales by Event (Doughnut Chart) ---
        $salesByEventRaw = Order::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->select('event_id', DB::raw('SUM(total) as revenue'))
            ->groupBy('event_id')
            ->with('event:id,title') // Eager load just what we need
            ->orderByDesc('revenue')
            ->take(5) // Top 5 events
            ->get();

        $pieLabels = [];
        $pieData = [];
        foreach ($salesByEventRaw as $data) {
            $pieLabels[] = $data->event ? $data->event->title : 'Unknown';
            $pieData[] = $data->revenue;
        }

        // --- Recent Orders Table ---
        $recentOrders = Order::with('event:id,title')
            ->where('organization_id', $orgId)
            ->latest()
            ->take(6)
            ->get();

        // Pass everything to view
        return view('dashboard', compact(
            'organization',
            'grossRevenue',
            'ticketsSold',
            'activeEventsCount',
            'totalCheckins',
            'dates',
            'trendData',
            'pieLabels',
            'pieData',
            'recentOrders'
        ));
    }
}
