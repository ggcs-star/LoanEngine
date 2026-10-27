
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use LoanEngine\Auth\Http\Middleware\EnsureUserHasRole;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (
        TooManyRequestsHttpException $exception,
        Request $request
    ) {
        $routes = [
            'auth.login.submit' => [
                'auth.login',
                'Too many login attempts. Please wait before trying again.',
            ],
            'auth.register.submit' => [
                'auth.register',
                'Too many registration attempts. Please wait before trying again.',
            ],
            'password.email' => [
                'password.request',
                'Too many OTP requests. Please wait before trying again.',
            ],
            'password.otp.verify' => [
                'password.otp.form',
                'Too many OTP verification attempts. Please wait before trying again.',
            ],
            'password.reset.update' => [
                'password.reset.form',
                'Too many password reset attempts. Please wait before trying again.',
            ],
        ];

        foreach ($routes as $routeName => [$redirectRoute, $message]) {
            if ($request->routeIs($routeName)) {
                return redirect()
                    ->route($redirectRoute)
                    ->withInput(
                        $request->except([
                            'password',
                            'password_confirmation',
                            'otp',
                        ])
                    )
                    ->withErrors([
                        'throttle' => $message,
                    ]);
            }
        }

        return null;
    });
     $exceptions->render(function (
        \Illuminate\Session\TokenMismatchException $exception,
        Request $request
    ) {
        if (
            $request->isMethod('POST') &&
            $request->routeIs('auth.logout')
        ) {
            return redirect()
                ->route('auth.login')
                ->with('status', 'Your session expired. Please sign in again.');
        }

        return null;
    });
})
->create();
