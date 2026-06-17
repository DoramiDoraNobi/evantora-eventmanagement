<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiOrganization
{
    /**
     * Resolve the organization from a route parameter and verify user membership.
     * This middleware is designed for API routes where sessions are not used.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $orgId = $request->route('orgId');

        if (!$orgId) {
            return response()->json(['message' => 'Organization ID is required.'], 400);
        }

        $organization = Organization::find($orgId);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found.'], 404);
        }

        if (!$organization->is_active) {
            return response()->json(['message' => 'This organization has been suspended.'], 403);
        }

        // Check if the user belongs to this organization
        $userRole = $user->getRoleInOrganization($organization->id);

        if (!$userRole) {
            return response()->json(['message' => 'You do not belong to this organization.'], 403);
        }

        // If specific roles are required, check them
        if (!empty($roles) && !in_array($userRole, $roles)) {
            return response()->json(['message' => 'You do not have the required role for this action.'], 403);
        }

        // Bind organization to container for downstream use
        app()->instance('current_organization', $organization);

        return $next($request);
    }
}
