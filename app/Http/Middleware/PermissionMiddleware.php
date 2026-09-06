<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$perms): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        foreach ($perms as $perm) {
            if ($user->hasPermission($perm)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
