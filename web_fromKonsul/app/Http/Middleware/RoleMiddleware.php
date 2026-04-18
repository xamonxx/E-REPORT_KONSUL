<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // God Mode Bypass: Super Admin can access all routes
        if ($user->role === UserRole::SuperAdmin || in_array($user->role->value, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. You do not have permission to access this page.');
    }
}
