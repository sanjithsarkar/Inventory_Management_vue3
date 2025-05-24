<?php

namespace App\Services\PayPal;

use Illuminate\Support\Facades\Log;

class PayPalService
{
    /**
     * @var PayPalClient
     */
    protected $client;
    
    /**
     * Constructor
     * 
     * @param PayPalClient $client
     */
    public function __construct(PayPalClient $client)
    {
        $this->client = $client;
    }
    
    /**
     * Create an order for checkout
     * 
     * @param float $amount Order amount
     * @param string $currency Currency code
     * @param string $returnUrl Success URL
     * @param string $cancelUrl Cancel URL
     * @param array $metadata Additional metadata
     * @return object
     */
    public function createOrder($amount, $currency = 'USD', $returnUrl = null, $cancelUrl = null, $metadata = [])
    {
        $returnUrl = $returnUrl ?? route('paypal.success');
        $cancelUrl = $cancelUrl ?? route('paypal.cancel');
        
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => $metadata['description'] ?? 'Purchase from ' . config('app.name'),
                    'custom_id' => $metadata['order_id'] ?? uniqid('order_'),
                    'invoice_id' => $metadata['invoice_id'] ?? null,
                ]
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'landing_page' => 'BILLING',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl
            ]
        ];
        
        // Add reference_id if provided
        if (!empty($metadata['reference_id'])) {
            $payload['purchase_units'][0]['reference_id'] = $metadata['reference_id'];
        }
        
        // Add payer information if provided
        if (!empty($metadata['payer'])) {
            $payload['payer'] = $metadata['payer'];
        }
        
        return $this->client->post('/v2/checkout/orders', $payload);
    }
    
    /**
     * Capture payment for an approved order
     * 
     * @param string $orderId PayPal order ID
     * @return object
     */
    public function capturePayment($orderId)
    {
        return $this->client->post("/v2/checkout/orders/{$orderId}/capture");
    }
    
    /**
     * Get order details
     * 
     * @param string $orderId PayPal order ID
     * @return object
     */
    public function getOrder($orderId)
    {
        return $this->client->get("/v2/checkout/orders/{$orderId}");
    }
    
    /**
     * Create a subscription plan
     * 
     * @param array $planData Plan data
     * @return object
     */
    public function createPlan($planData)
    {
        return $this->client->post('/v1/billing/plans', $planData);
    }
    
    /**
     * Create a subscription
     * 
     * @param string $planId Plan ID
     * @param array $subscriptionData Subscription data
     * @return object
     */
    public function createSubscription($planId, $subscriptionData = [])
    {
        $payload = array_merge([
            'plan_id' => $planId,
            'application_context' => [
                'brand_name' => config('app.name'),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => route('paypal.subscription.success'),
                'cancel_url' => route('paypal.subscription.cancel')
            ]
        ], $subscriptionData);
        
        return $this->client->post('/v1/billing/subscriptions', $payload);
    }
    
    /**
     * Get subscription details
     * 
     * @param string $subscriptionId Subscription ID
     * @return object
     */
    public function getSubscription($subscriptionId)
    {
        return $this->client->get("/v1/billing/subscriptions/{$subscriptionId}");
    }
    
    /**
     * Cancel a subscription
     * 
     * @param string $subscriptionId Subscription ID
     * @param string $reason Cancellation reason
     * @return object
     */
    public function cancelSubscription($subscriptionId, $reason = 'Canceled by customer')
    {
        return $this->client->post("/v1/billing/subscriptions/{$subscriptionId}/cancel", [
            'reason' => $reason
        ]);
    }
    
    /**
     * Suspend a subscription
     * 
     * @param string $subscriptionId Subscription ID
     * @param string $reason Suspension reason
     * @return object
     */
    public function suspendSubscription($subscriptionId, $reason = 'Suspended by merchant')
    {
        return $this->client->post("/v1/billing/subscriptions/{$subscriptionId}/suspend", [
            'reason' => $reason
        ]);
    }
    
    /**
     * Reactivate a suspended subscription
     * 
     * @param string $subscriptionId Subscription ID
     * @param string $reason Reactivation reason
     * @return object
     */
    public function reactivateSubscription($subscriptionId, $reason = 'Reactivated by merchant')
    {
        return $this->client->post("/v1/billing/subscriptions/{$subscriptionId}/activate", [
            'reason' => $reason
        ]);
    }
    
    /**
     * Update subscription pricing
     * 
     * @param string $subscriptionId Subscription ID
     * @param array $pricingData New pricing data
     * @return object
     */
    public function updateSubscriptionPricing($subscriptionId, $pricingData)
    {
        return $this->client->post("/v1/billing/subscriptions/{$subscriptionId}/revise", $pricingData);
    }
    
    /**
     * Process webhook event
     * 
     * @param string $payload Webhook payload
     * @param array $headers Request headers
     * @return array Processed event data
     */
    public function processWebhook($payload, $headers)
    {
        // Verify webhook signature if needed
        // ...
        
        $data = json_decode($payload, true);
        
        Log::info('PayPal webhook received', [
            'event_type' => $data['event_type'] ?? 'unknown',
            'resource_type' => $data['resource_type'] ?? 'unknown',
            'resource_id' => $data['resource']['id'] ?? 'unknown'
        ]);
        
        return $data;
    }
}