<?php

namespace LoanEngine\Auth;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/loanengine-auth.php', 'loanengine-auth');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'loanengine-auth');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/loanengine-auth.php' => config_path('loanengine-auth.php'),
        ], 'loanengine-auth-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/loanengine-auth'),
        ], 'loanengine-auth-views');
    }
}
