<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isStaffOrAbove()) {
            abort(403, 'Anda tidak memiliki akses ke area ini.');
        }

        return $next($request);
    }
}
