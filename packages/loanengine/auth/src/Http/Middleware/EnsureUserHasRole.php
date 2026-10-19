<?php

namespace LoanEngine\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('auth.login'));
        }

        abort_unless(in_array($user->role, $roles, true), 403, 'You are not authorized to access this page.');

        return $next($request);
    }
}
