<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The activity log shows every user's requests and can be cleared, so only
        // an Admin may open it. The package applies rolesMiddlware when enabled.
        config([
            'LaravelLogger.rolesEnabled' => true,
            'LaravelLogger.rolesMiddlware' => 'admin',
        ]);

        //
    }
}
