<?php

namespace LoanEngine\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use LoanEngine\Auth\Http\Middleware\IdentifyWebDevice;
use LoanEngine\Auth\Support\ApiResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/loanengine-auth.php',
            'loanengine-auth'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'loanengine-auth'
        );

        // Register package web routes with device identification.
        Route::middleware(IdentifyWebDevice::class)->group(function () {
            $this->loadRoutesFrom(
                __DIR__ . '/../routes/web.php'
            );
        });

        // Register package API routes.
        $this->loadRoutesFrom(
            __DIR__ . '/../routes/api.php'
        );

        // Register package migrations.
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        // Standardize JSON API errors only.
        $this->app->afterResolving(
            ExceptionHandler::class,
            function (ExceptionHandler $handler): void {
                if (!method_exists($handler, 'renderable')) {
                    return;
                }

                $handler->renderable(
                    function (Throwable $exception, Request $request) {
                        if (!$request->is('api/*')) {
                            return null;
                        }

                        if ($exception instanceof ValidationException) {
                            return ApiResponse::validationError(
                                $exception->errors(),
                                $exception->getMessage()
                                    ?: 'The given data was invalid.'
                            );
                        }

                        if ($exception instanceof AuthenticationException) {
                            return ApiResponse::unauthorized();
                        }

                        if ($exception instanceof AuthorizationException) {
                            return ApiResponse::forbidden();
                        }

                        if ($exception instanceof ModelNotFoundException) {
                            return ApiResponse::notFound();
                        }

                        if ($exception instanceof HttpExceptionInterface) {
                            $status = $exception->getStatusCode();

                            $message = $status >= 500
                                ? 'An unexpected server error occurred.'
                                : (
                                    $exception->getMessage()
                                    ?: 'The request could not be completed.'
                                );

                            return ApiResponse::error(
                                $message,
                                $status
                            );
                        }

                        report($exception);

                        return ApiResponse::serverError();
                    }
                );
            }
        );

        // Publish configuration.
        $this->publishes([
            __DIR__ . '/../config/loanengine-auth.php'
                => config_path('loanengine-auth.php'),
        ], 'loanengine-auth-config');

        // Publish views.
        $this->publishes([
            __DIR__ . '/../resources/views'
                => resource_path('views/vendor/loanengine-auth'),
        ], 'loanengine-auth-views');
    }
}
