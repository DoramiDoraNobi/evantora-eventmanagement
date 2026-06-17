<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    /**
     * GET /api/v1/organizer/{orgId}/team — List team members
     */
    public function index(Request $request, $orgId)
    {
        $organization = app('current_organization');

        $members = $organization->users()->withPivot('role')->get()->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->pivot->role,
            'joined_at' => $user->pivot->created_at,
        ]);

        return response()->json(['members' => $members]);
    }

    /**
     * POST /api/v1/organizer/{orgId}/team — Invite/add team member
     */
    public function store(Request $request, $orgId)
    {
        $organization = app('current_organization');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,event_manager,finance_staff,checkin_staff,marketing_staff',
        ]);

        // Check if user already exists
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            // Create a new user with a temporary password
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password'),
            ]);
        }

        // Check if already a member
        if ($organization->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already a member of this organization.'], 422);
        }

        $organization->users()->attach($user->id, ['role' => $validated['role']]);

        return response()->json([
            'message' => 'Team member added successfully.',
            'member' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
            ],
        ], 201);
    }
}
