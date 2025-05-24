<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | Your Stripe API keys. Use the publishable key in your frontend code
    | and the secret key for backend operations.
    |
    */
    'pk' => env('STRIPE_PUBLIC_KEY', ''),
    'sk' => env('STRIPE_SECRET_KEY', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Stripe API Version
    |--------------------------------------------------------------------------
    |
    | The Stripe API version to use. It's recommended to specify a version
    | to ensure API stability.
    |
    */
    'api_version' => env('STRIPE_API_VERSION', '2023-10-16'),
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This is used to verify that webhooks came from Stripe.
    |
    */
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to use for Stripe transactions.
    |
    */
    'currency' => env('STRIPE_CURRENCY', 'usd'),
    
    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for Stripe API requests in seconds.
    |
    */
    'timeout' => env('STRIPE_TIMEOUT', 60),
    
    /*
    |--------------------------------------------------------------------------
    | Automatic Tax
    |--------------------------------------------------------------------------
    |
    | Whether to enable Stripe Tax for automatic tax calculation.
    |
    */
    'automatic_tax' => env('STRIPE_AUTOMATIC_TAX', false),
];
