<?php

namespace LoanEngine\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdentifyWebDevice
{
    private const COOKIE_NAME = 'loanengine_device_id';

    private const ATTRIBUTE_NAME = '_loanengine_device_id';

    private const COOKIE_LIFETIME = 60 * 24 * 365;

    public function handle(Request $request, Closure $next): Response
    {
        // Read existing device ID from browser cookie.
        $deviceId = $request->cookie(self::COOKIE_NAME);

        // Validate existing UUID; generate a new one if invalid.
        if (!is_string($deviceId) || !Str::isUuid($deviceId)) {
            $deviceId = (string) Str::uuid();
        }

        // Make device ID available to AuthActivityLogger.
        $request->attributes->set(
            self::ATTRIBUTE_NAME,
            $deviceId
        );

        // Continue request.
        $response = $next($request);

        // Store device ID in browser cookie.
        $response->headers->setCookie(
            cookie(
                self::COOKIE_NAME,
                $deviceId,
                self::COOKIE_LIFETIME,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            )
        );

        return $response;
    }
}
