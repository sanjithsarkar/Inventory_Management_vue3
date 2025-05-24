<?php

namespace App\Services\Stripe;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Service
 * 
 * Provides methods for interacting with Stripe API
 */
class StripeService
{
    /**
     * Initialize the Stripe API
     */
    public function __construct()
    {
        $stripeSecretKey = config('stripe.sk');
        
        if (empty($stripeSecretKey)) {
            Log::error('Stripe secret key is missing');
            throw new \Exception('Stripe configuration error: Missing API key');
        }
        
        ApiRequest::setSecretKey($stripeSecretKey);
    }
    
    /**
     * Create a Stripe Checkout Session
     *
     * @param float $amount Total amount to charge
     * @param array $items Cart items
     * @param string|null $customerId Customer ID (optional)
     * @return array Response with checkout URL
     * @throws \Exception
     */
    public function createCheckoutSession($amount, $items, $customerId = null)
    {
        try {
            // Validate inputs
            if ($amount <= 0) {
                throw new \Exception('Invalid amount: must be greater than zero');
            }
            
            if (empty($items)) {
                throw new \Exception('Invalid items: cart cannot be empty');
            }
            
            // Create line items description
            $itemCount = count($items);
            $description = $itemCount . " " . ($itemCount === 1 ? "item" : "items");
            
            // Prepare request data
            $requestData = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => round($amount * 100), // Convert to cents
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
            ];
            
            // Add customer if provided
            if ($customerId) {
                $requestData['customer'] = $customerId;
            }
            
            // Create checkout session
            $session = ApiRequest::create($requestData, 'checkout/sessions');
            
            return ['url' => $session->url];
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Error: ' . $e->getMessage());
            throw new \Exception('Stripe API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a customer in Stripe
     *
     * @param string $email Customer email
     * @param string $name Customer name
     * @param string|null $paymentMethodId Payment method ID (optional)
     * @return object Stripe customer object
     * @throws \Exception
     */
    public function createCustomer($email, $name, $paymentMethodId = null)
    {
        try {
            $requestData = [
                'email' => $email,
                'name' => $name,
            ];
            
            if ($paymentMethodId) {
                $requestData['payment_method'] = $paymentMethodId;
            }
            
            return ApiRequest::create($requestData, 'customers');
        } catch (\Exception $e) {
            Log::error('Stripe Create Customer Error: ' . $e->getMessage());
            throw new \Exception('Failed to create customer: ' . $e->getMessage());
        }
    }
    
    /**
     * Retrieve a customer from Stripe
     *
     * @param string $customerId Customer ID
     * @return object Stripe customer object
     * @throws \Exception
     */
    public function getCustomer($customerId)
    {
        try {
            return ApiRequest::retrieve("customers/{$customerId}");
        } catch (\Exception $e) {
            Log::error('Stripe Get Customer Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve customer: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a payment intent
     *
     * @param float $amount Amount to charge
     * @param string $currency Currency code
     * @param string|null $customerId Customer ID (optional)
     * @param array $metadata Additional metadata (optional)
     * @return object Payment intent object
     * @throws \Exception
     */
    public function createPaymentIntent($amount, $currency = 'usd', $customerId = null, $metadata = [])
    {
        try {
            $requestData = [
                'amount' => round($amount * 100), // Convert to cents
                'currency' => $currency,
                'payment_method_types' => ['card'],
                'metadata' => $metadata,
            ];
            
            if ($customerId) {
                $requestData['customer'] = $customerId;
            }
            
            return ApiRequest::create($requestData, 'payment_intents');
        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Error: ' . $e->getMessage());
            throw new \Exception('Failed to create payment intent: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle Stripe webhook events
     *
     * @param Request $request
     * @return array Response for the webhook
     * @throws \Exception
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');
        
        if (empty($webhookSecret)) {
            Log::error('Stripe webhook secret is missing');
            throw new \Exception('Stripe configuration error: Missing webhook secret');
        }
        
        try {
            // Verify webhook signature
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );
            
            // Handle different event types
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    Log::info('PaymentIntent was successful: ' . $paymentIntent->id);
                    // Process successful payment
                    break;
                    
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    Log::info('Checkout session completed: ' . $session->id);
                    // Process completed checkout
                    break;
                    
                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }
            
            return ['status' => 'success'];
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            throw new \Exception('Invalid signature');
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            throw new \Exception('Webhook error: ' . $e->getMessage());
        }
    }
}