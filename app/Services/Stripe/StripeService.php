<?php

namespace App\Services\Stripe;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class StripeService
{
    /**
     * Stripe API client
     */
    protected $client;
    
    /**
     * Default currency
     */
    protected $currency;
    
    /**
     * Constructor
     */
    public function __construct(StripeClient $client)
    {
        $this->client = $client;
        $this->currency = config('stripe.currency', 'usd');
    }
    
    /**
     * Create a customer
     * 
     * @param array $customerData Customer data
     * @return object
     */
    public function createCustomer(array $customerData)
    {
        return $this->client->post('customers', $customerData);
    }
    
    /**
     * Retrieve a customer
     * 
     * @param string $customerId Customer ID
     * @return object
     */
    public function getCustomer(string $customerId)
    {
        return $this->client->get("customers/{$customerId}");
    }
    
    /**
     * Update a customer
     * 
     * @param string $customerId Customer ID
     * @param array $data Update data
     * @return object
     */
    public function updateCustomer(string $customerId, array $data)
    {
        return $this->client->post("customers/{$customerId}", $data);
    }
    
    /**
     * Delete a customer
     * 
     * @param string $customerId Customer ID
     * @return object
     */
    public function deleteCustomer(string $customerId)
    {
        return $this->client->delete("customers/{$customerId}");
    }
    
    /**
     * Create a product
     * 
     * @param array $productData Product data
     * @return object
     */
    public function createProduct(array $productData)
    {
        return $this->client->post('products', $productData);
    }
    
    /**
     * Create a price
     * 
     * @param array $priceData Price data
     * @return object
     */
    public function createPrice(array $priceData)
    {
        return $this->client->post('prices', $priceData);
    }
    
    /**
     * Create a payment intent
     * 
     * @param int $amount Amount in cents
     * @param array $options Additional options
     * @return object
     */
    public function createPaymentIntent(int $amount, array $options = [])
    {
        $data = array_merge([
            'amount' => $amount,
            'currency' => $this->currency,
        ], $options);
        
        return $this->client->post('payment_intents', $data);
    }
    
    /**
     * Create a setup intent
     * 
     * @param array $options Options
     * @return object
     */
    public function createSetupIntent(array $options = [])
    {
        return $this->client->post('setup_intents', $options);
    }
    
    /**
     * Create a checkout session
     * 
     * @param array $sessionData Session data
     * @return object
     */
    public function createCheckoutSession(array $sessionData)
    {
        return $this->client->post('checkout/sessions', $sessionData);
    }
    
    /**
     * Create a subscription
     * 
     * @param string $customerId Customer ID
     * @param array $subscriptionData Subscription data
     * @return object
     */
    public function createSubscription(string $customerId, array $subscriptionData)
    {
        $data = array_merge([
            'customer' => $customerId,
        ], $subscriptionData);
        
        return $this->client->post('subscriptions', $data);
    }
    
    /**
     * Create a billing portal session
     * 
     * @param string $customerId Customer ID
     * @param string $returnUrl Return URL
     * @return object
     */
    public function createBillingPortalSession(string $customerId, string $returnUrl)
    {
        return $this->client->post('billing_portal/sessions', [
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
    }
    
    /**
     * Verify webhook signature
     * 
     * @param string $payload Request body
     * @param string $sigHeader Stripe-Signature header
     * @return object Event object
     * @throws \Exception
     */
    public function verifyWebhookSignature(string $payload, string $sigHeader)
    {
        $webhookSecret = config('stripe.webhook_secret');
        
        if (empty($webhookSecret)) {
            throw new \Exception('Stripe webhook secret is not configured');
        }
        
        try {
            // Use the Stripe PHP SDK for signature verification
            return \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid webhook signature', [
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Invalid webhook signature');
        }
    }
    
    /**
     * Create a one-time checkout session
     * 
     * @param float $amount Amount in dollars
     * @param array $items Line items
     * @param array $options Additional options
     * @return array Response with checkout URL
     */
    public function createOneTimeCheckout(float $amount, array $items, array $options = [])
    {
        // Convert amount to cents
        $amountInCents = (int) round($amount * 100);
        
        // Create line items description
        $itemCount = count($items);
        $description = $itemCount . " " . ($itemCount === 1 ? "item" : "items");
        
        // Prepare session data
        $sessionData = array_merge([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $this->currency,
                        'unit_amount' => $amountInCents,
                        'product_data' => [
                            'name' => "Purchase from " . config('app.name'),
                            'description' => $description,
                        ],
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('stripe.success'),
            'cancel_url' => route('stripe.cancel'),
        ], $options);
        
        // Enable automatic tax if configured
        if (config('stripe.automatic_tax', false)) {
            $sessionData['automatic_tax'] = ['enabled' => true];
        }
        
        $session = $this->createCheckoutSession($sessionData);
        
        return ['url' => $session->url];
    }
    
    /**
     * Create a subscription checkout session
     * 
     * @param string $priceId Stripe Price ID
     * @param array $options Additional options
     * @return array Response with checkout URL
     */
    public function createSubscriptionCheckout(string $priceId, array $options = [])
    {
        // Prepare session data
        $sessionData = array_merge([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ]
            ],
            'mode' => 'subscription',
            'success_url' => route('stripe.subscription.success'),
            'cancel_url' => route('stripe.subscription.cancel'),
        ], $options);
        
        // Enable automatic tax if configured
        if (config('stripe.automatic_tax', false)) {
            $sessionData['automatic_tax'] = ['enabled' => true];
        }
        
        $session = $this->createCheckoutSession($sessionData);
        
        return ['url' => $session->url];
    }
}
