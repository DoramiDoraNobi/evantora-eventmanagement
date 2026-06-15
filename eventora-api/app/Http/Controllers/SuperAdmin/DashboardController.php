<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\Event;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_tenants' => Organization::count(),
            'total_events' => Event::count(),
            // 'total_revenue' => Order::where('status', 'paid')->sum('total_amount') // Add later if Order exists
        ];

        return view('super-admin.dashboard', compact('stats'));
    }
}
