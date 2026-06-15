<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index()
    {
        $organization = app('current_organization');
        $team = $organization->users;
        return view('admin.organization.team', compact('organization', 'team'));
    }

    public function store(Request $request)
    {
        $organization = app('current_organization');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,event_manager,finance,checkin_staff,marketing'
        ]);

        // In a real app we'd send an invite email. For MVP we'll just create the user if they don't exist
        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'password' => Hash::make(Str::random(12)) // random password, they can reset it
            ]
        );

        if (!$organization->users()->where('user_id', $user->id)->exists()) {
            $organization->users()->attach($user->id, ['role' => $validated['role']]);
        }

        return redirect()->back()->with('status', 'team-member-added');
    }
}
