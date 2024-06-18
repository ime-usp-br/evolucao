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
        if (env('APP_ENV') === 'production') {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        if ($this->app->environment('local') || $this->app->environment('development')) {
            Mail::alwaysTo(env('MAIL_DEV_TEST'));
        }

        if (env('FORCE_HTTPS', false)){
            URL::forceScheme('https');
        }
    }
}
