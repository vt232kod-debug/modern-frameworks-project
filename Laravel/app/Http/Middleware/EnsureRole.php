<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware "role:manager" — allows the given role and higher ones (admin > manager > client).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(401, 'Unauthenticated.');
        }
        if (!$user->hasRole($role)) {
            abort(403, 'Access denied for your role.');
        }

        return $next($request);
    }
}
