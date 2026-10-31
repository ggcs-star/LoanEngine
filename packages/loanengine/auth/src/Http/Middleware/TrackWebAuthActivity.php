<?php

namespace LoanEngine\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compatibility middleware. Auth events are logged explicitly by the package
 * controller after authentication outcome is known, avoiding false positives.
 */
class TrackWebAuthActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
