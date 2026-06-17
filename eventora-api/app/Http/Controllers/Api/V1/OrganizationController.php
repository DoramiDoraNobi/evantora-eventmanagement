<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * GET /api/v1/organizer/organizations — List user's organizations
     */
    public function index(Request $request)
    {
        $organizations = $request->user()
            ->organizations()
            ->withPivot('role')
            ->withCount(['users', 'events'])
            ->get();

        return OrganizationResource::collection($organizations);
    }

    /**
     * GET /api/v1/organizer/organizations/{id} — Org detail
     */
    public function show(Request $request, $id)
    {
        $organization = $request->user()
            ->organizations()
            ->withPivot('role')
            ->withCount(['users', 'events'])
            ->findOrFail($id);

        return new OrganizationResource($organization);
    }

    /**
     * PATCH /api/v1/organizer/organizations/{id} — Update org settings
     */
    public function update(Request $request, $id)
    {
        $organization = $request->user()
            ->organizations()
            ->withPivot('role')
            ->findOrFail($id);

        // Only owner/admin can update
        if (!in_array($organization->pivot->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Insufficient permissions.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'currency' => 'nullable|string|max:3',
            'timezone' => 'nullable|string|max:50',
        ]);

        $organization->update($validated);

        return new OrganizationResource($organization->fresh());
    }
}
