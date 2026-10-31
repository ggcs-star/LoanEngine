<?php

namespace LoanEngine\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LoanEngine\Auth\Support\AuthActivityLogger;
use Symfony\Component\HttpFoundation\Response;

class TrackAuthActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/v1/auth/*') && ! in_array($request->path(), [
            'api/v1/auth/activity',
        ], true)) {
            $user = $request->user();
            AuthActivityLogger::record(
                $request,
                'api_request',
                $user?->getAuthIdentifier(),
                null,
                $response->getStatusCode(),
                ['authenticated' => (bool) $user]
            );
        }

        return $response;
    }
}
