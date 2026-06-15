<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Organization::withCount('users', 'events')->latest()->paginate(20);
        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function toggleStatus(Organization $tenant)
    {
        // Simple toggle for suspension/activation (Assuming we might add an 'is_active' column later, 
        // but for MVP, we just show the concept)
        // $tenant->update(['is_active' => !$tenant->is_active]);
        
        return back()->with('status', 'Tenant status updated (simulated).');
    }
}
