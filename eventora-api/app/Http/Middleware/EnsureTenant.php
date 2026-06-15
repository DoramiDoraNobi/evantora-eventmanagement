<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $organizationId = session('current_organization_id');
            $organization = null;

            if ($organizationId) {
                $organization = $user->organizations()->find($organizationId);
            }

            if (!$organization && $user->organizations()->exists()) {
                $organization = $user->organizations()->first();
                session(['current_organization_id' => $organization->id]);
            }

            if ($organization) {
                app()->instance('current_organization', $organization);
                View::share('currentOrganization', $organization);
            }
        }

        return $next($request);
    }
}
