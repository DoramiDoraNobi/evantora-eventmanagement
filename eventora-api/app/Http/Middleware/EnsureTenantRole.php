<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Super admins can bypass tenant role restrictions if they want,
        // but ONLY if they have an active organization set (e.g. via impersonation).
        if ($user->is_super_admin && app()->bound('current_organization')) {
            return $next($request);
        }

        if (empty($roles)) {
            if (! $user->currentTenantRole()) {
                abort(403, 'Unauthorized. You must belong to an organization.');
            }
            return $next($request);
        }

        if (! $user->hasTenantRole($roles)) {
            abort(403, 'Unauthorized. You do not have the required role to access this section.');
        }

        return $next($request);
    }
}
