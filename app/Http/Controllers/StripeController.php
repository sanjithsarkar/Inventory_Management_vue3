<?php

namespace App\Http\Controllers;

use App\Services\Stripe\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripeService;
    
    /**
     * Constructor with dependency injection
     */
    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }
    
    /**
     * Create a checkout session
     */
    public function createCheckout(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.name' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'customer_id' => 'nullable|string'
            ]);
            
            // Use the Stripe service to create a checkout session
            $response = $this->stripeService->createCheckoutSession(
                $request->amount,
                $request->items,
                $request->customer_id ?? null
            );
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle successful payment
     */
    public function success()
    {
        return redirect(route('products.index'))->with('success', 'Payment completed successfully!');
    }
    
    /**
     * Handle cancelled payment
     */
    public function cancel()
    {
        return redirect(route('orders.index'))->with('info', 'Payment was cancelled.');
    }
    
    /**
     * Handle Stripe webhook
     */
    public function handleWebhook(Request $request)
    {
        try {
            $response = $this->stripeService->handleWebhook($request);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Create a customer in Stripe
     */
    public function createCustomer(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'name' => 'required|string',
                'payment_method_id' => 'nullable|string'
            ]);
            
            $customer = $this->stripeService->createCustomer(
                $request->email,
                $request->name,
                $request->payment_method_id ?? null
            );
            
            return response()->json($customer);
        } catch (\Exception $e) {
            Log::error('Create Customer Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'nullable|string|size:3',
                'customer_id' => 'nullable|string',
                'metadata' => 'nullable|array'
            ]);
            
            $paymentIntent = $this->stripeService->createPaymentIntent(
                $request->amount,
                $request->currency ?? 'usd',
                $request->customer_id ?? null,
                $request->metadata ?? []
            );
            
            return response()->json($paymentIntent);
        } catch (\Exception $e) {
            Log::error('Payment Intent Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
