<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        if ($user->role === 'admin' || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return redirect($user->homePath());
    }
}
