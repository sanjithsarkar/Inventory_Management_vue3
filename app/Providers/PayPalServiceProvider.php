<?php

namespace App\Providers;

use App\Services\PayPal\PayPalClient;
use App\Services\PayPal\PayPalService;
use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__.'/../../config/paypal.php', 'paypal'
        );
        
        // Register the PayPal client
        $this->app->singleton(PayPalClient::class, function ($app) {
            return new PayPalClient();
        });
        
        // Register the PayPal service
        $this->app->singleton(PayPalService::class, function ($app) {
            return new PayPalService(
                $app->make(PayPalClient::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../../config/paypal.php' => config_path('paypal.php'),
        ], 'paypal-config');
    }
}