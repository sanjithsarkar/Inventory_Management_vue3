<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Stripe\StripeClient;
use App\Services\Stripe\StripeService;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__.'/../../config/stripe.php', 'stripe'
        );
        
        // Register the Stripe client
        $this->app->singleton(StripeClient::class, function ($app) {
            return new StripeClient();
        });
        
        // Register the Stripe service
        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService(
                $app->make(StripeClient::class)
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
            __DIR__.'/../../config/stripe.php' => config_path('stripe.php'),
        ], 'stripe-config');
    }
}