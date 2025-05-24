<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Stripe\StripeService;

/**
 * @method static object createCustomer(array $customerData)
 * @method static object getCustomer(string $customerId)
 * @method static object updateCustomer(string $customerId, array $data)
 * @method static object deleteCustomer(string $customerId)
 * @method static object createProduct(array $productData)
 * @method static object createPrice(array $priceData)
 * @method static object createPaymentIntent(int $amount, array $options = [])
 * @method static object createSetupIntent(array $options = [])
 * @method static object createCheckoutSession(array $sessionData)
 * @method static object createSubscription(string $customerId, array $subscriptionData)
 * @method static object createBillingPortalSession(string $customerId, string $returnUrl)
 * @method static object verifyWebhookSignature(string $payload, string $sigHeader)
 * @method static array createOneTimeCheckout(float $amount, array $items, array $options = [])
 * @method static array createSubscriptionCheckout(string $priceId, array $options = [])
 * 
 * @see \App\Services\Stripe\StripeService
 */
class Stripe extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return StripeService::class;
    }
}