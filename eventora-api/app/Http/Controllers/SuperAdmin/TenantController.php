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
        $tenant->update(['is_active' => !$tenant->is_active]);
        
        $statusMessage = $tenant->is_active 
            ? "Tenant '{$tenant->name}' has been activated successfully." 
            : "Tenant '{$tenant->name}' has been suspended successfully.";

        return back()->with('status', $statusMessage);
    }
}
