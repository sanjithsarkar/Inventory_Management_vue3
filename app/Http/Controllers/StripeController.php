<?php

namespace App\Http\Controllers;

use App\Facades\Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    /**
     * Create a one-time checkout session
     */
    public function createCheckout(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'items' => 'required|array',
                'items.*.id' => 'required',
                'items.*.name' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'customer_id' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);
            
            // Prepare options
            $options = [];
            
            // Add customer if provided
            if ($request->customer_id) {
                $options['customer'] = $request->customer_id;
            }
            
            // Add metadata if provided
            if ($request->metadata) {
                $options['metadata'] = $request->metadata;
            }
            
            // Create checkout session
            $response = Stripe::createOneTimeCheckout(
                $request->amount,
                $request->items,
                $options
            );
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a subscription checkout session
     */
    public function createSubscriptionCheckout(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'price_id' => 'required|string',
                'customer_id' => 'nullable|string',
                'metadata' => 'nullable|array',
                'trial_days' => 'nullable|integer|min:0',
            ]);
            
            // Prepare options
            $options = [];
            
            // Add customer if provided
            if ($request->customer_id) {
                $options['customer'] = $request->customer_id;
            }
            
            // Add metadata if provided
            if ($request->metadata) {
                $options['metadata'] = $request->metadata;
            }
            
            // Add trial period if provided
            if ($request->trial_days) {
                $options['subscription_data'] = [
                    'trial_period_days' => $request->trial_days,
                ];
            }
            
            // Create subscription checkout session
            $response = Stripe::createSubscriptionCheckout(
                $request->price_id,
                $options
            );
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Stripe Subscription Checkout Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a customer portal session
     */
    public function createCustomerPortal(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'customer_id' => 'required|string',
                'return_url' => 'required|url',
            ]);
            
            // Create customer portal session
            $session = Stripe::createBillingPortalSession(
                $request->customer_id,
                $request->return_url
            );
            
            return response()->json([
                'url' => $session->url
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Customer Portal Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'nullable|string|size:3',
                'customer_id' => 'nullable|string',
                'payment_method_types' => 'nullable|array',
                'metadata' => 'nullable|array',
            ]);
            
            // Prepare options
            $options = [
                'currency' => $request->currency ?? config('stripe.currency', 'usd'),
            ];
            
            // Add customer if provided
            if ($request->customer_id) {
                $options['customer'] = $request->customer_id;
            }
            
            // Add payment method types if provided
            if ($request->payment_method_types) {
                $options['payment_method_types'] = $request->payment_method_types;
            }
            
            // Add metadata if provided
            if ($request->metadata) {
                $options['metadata'] = $request->metadata;
            }
            
            // Create payment intent
            $paymentIntent = Stripe::createPaymentIntent(
                (int) round($request->amount * 100), // Convert to cents
                $options
            );
            
            return response()->json([
                'client_secret' => $paymentIntent->client_secret
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle successful payment
     */
    public function success()
    {
        return redirect()->route('home')->with('success', 'Payment completed successfully!');
    }
    
    /**
     * Handle cancelled payment
     */
    public function cancel()
    {
        return redirect()->route('home')->with('info', 'Payment was cancelled.');
    }
    
    /**
     * Handle successful subscription
     */
    public function subscriptionSuccess()
    {
        return redirect()->route('subscriptions.index')->with('success', 'Subscription created successfully!');
    }
    
    /**
     * Handle cancelled subscription
     */
    public function subscriptionCancel()
    {
        return redirect()->route('subscriptions.index')->with('info', 'Subscription was cancelled.');
    }
    
    /**
     * Handle Stripe webhook
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        try {
            $event = Stripe::verifyWebhookSignature($payload, $sigHeader);
            
            // Handle the event based on its type
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;
                    
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object);
                    break;
                    
                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($event->data->object);
                    break;
                    
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;
                    
                default:
                    // Unexpected event type
                    Log::info('Unhandled event type: ' . $event->type);
            }
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Handle payment intent succeeded event
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment intent succeeded', [
            'id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'customer' => $paymentIntent->customer,
            'metadata' => $paymentIntent->metadata,
        ]);
        
        // Process the successful payment
        // For example, update order status, send confirmation email, etc.
    }
    
    /**
     * Handle checkout session completed event
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        Log::info('Checkout session completed', [
            'id' => $session->id,
            'customer' => $session->customer,
            'mode' => $session->mode,
            'metadata' => $session->metadata,
        ]);
        
        // Process the completed checkout
        // For example, fulfill the order, send confirmation email, etc.
    }
    
    /**
     * Handle subscription created event
     */
    protected function handleSubscriptionCreated($subscription)
    {
        Log::info('Subscription created', [
            'id' => $subscription->id,
            'customer' => $subscription->customer,
            'status' => $subscription->status,
        ]);
        
        // Process the new subscription
        // For example, update user's subscription status, send welcome email, etc.
    }
    
    /**
     * Handle subscription updated event
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        Log::info('Subscription updated', [
            'id' => $subscription->id,
            'customer' => $subscription->customer,
            'status' => $subscription->status,
        ]);
        
        // Process the updated subscription
        // For example, update user's subscription status, send notification, etc.
    }
    
    /**
     * Handle subscription deleted event
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        Log::info('Subscription deleted', [
            'id' => $subscription->id,
            'customer' => $subscription->customer,
            'status' => $subscription->status,
        ]);
        
        // Process the deleted subscription
        // For example, update user's subscription status, send notification, etc.
    }
    
    /**
     * Handle invoice payment succeeded event
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Invoice payment succeeded', [
            'id' => $invoice->id,
            'customer' => $invoice->customer,
            'subscription' => $invoice->subscription,
            'amount_paid' => $invoice->amount_paid,
        ]);
        
        // Process the successful invoice payment
        // For example, update subscription status, send receipt, etc.
    }
    
    /**
     * Handle invoice payment failed event
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        Log::info('Invoice payment failed', [
            'id' => $invoice->id,
            'customer' => $invoice->customer,
            'subscription' => $invoice->subscription,
            'attempt_count' => $invoice->attempt_count,
        ]);
        
        // Process the failed invoice payment
        // For example, notify user, update subscription status, etc.
    }
}
