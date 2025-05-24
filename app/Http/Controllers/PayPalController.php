<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PayPal\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{
    /**
     * @var PayPalService
     */
    protected $paypalService;
    
    /**
     * Constructor
     * 
     * @param PayPalService $paypalService
     */
    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }
    
    /**
     * Create a PayPal payment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'totalAmount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
        ]);
        
        try {
            // Get currency from request or use default
            $currency = strtoupper($request->currency ?? config('paypal.currency', 'USD'));
            
            // Create metadata for the order
            $metadata = [
                'description' => 'Purchase from ' . config('app.name'),
                'order_id' => $request->order_id ?? null,
                'user_id' => auth()->id(),
            ];
            
            // Create PayPal order
            $response = $this->paypalService->createOrder(
                $request->totalAmount,
                $currency,
                route('paypal.success'),
                route('paypal.cancel'),
                $metadata
            );
            
            // Store order information in session or database if needed
            if ($request->order_id) {
                // Update order with PayPal ID
                Order::where('id', $request->order_id)->update([
                    'payment_id' => $response->id,
                    'payment_provider' => 'paypal',
                    'payment_status' => 'pending'
                ]);
            }
            
            // Find the approval URL
            $approvalUrl = null;
            foreach ($response->links as $link) {
                if ($link->rel === 'approve') {
                    $approvalUrl = $link->href;
                    break;
                }
            }
            
            if (!$approvalUrl) {
                throw new \Exception('PayPal approval URL not found');
            }
            
            return response()->json([
                'success' => true,
                'paypalUrl' => $approvalUrl,
                'order_id' => $response->id
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal payment creation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle successful payment
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        try {
            $orderId = $request->token;
            
            if (!$orderId) {
                throw new \Exception('PayPal order ID not found');
            }
            
            // Capture the payment
            $captureResponse = $this->paypalService->capturePayment($orderId);
            
            // Check if payment was successful
            if ($captureResponse->status === 'COMPLETED') {
                // Get payment details
                $paymentDetails = $captureResponse->purchase_units[0]->payments->captures[0];
                
                // Create payment record
                $payment = new Payment();
                $payment->payment_id = $paymentDetails->id;
                $payment->payer_id = $captureResponse->payer->payer_id ?? null;
                $payment->amount = $paymentDetails->amount->value;
                $payment->currency = $paymentDetails->amount->currency_code;
                $payment->payment_method = 'paypal';
                $payment->status = 'completed';
                $payment->transaction_data = json_encode($captureResponse);
                
                // If we have an order ID in custom_id, link it
                if (!empty($captureResponse->purchase_units[0]->custom_id)) {
                    $localOrderId = $captureResponse->purchase_units[0]->custom_id;
                    $payment->order_id = $localOrderId;
                    
                    // Update order status
                    $order = Order::find($localOrderId);
                    if ($order) {
                        $order->payment_status
                            ->payment_id = $paymentDetails->id;
                        $order->payment_provider = 'paypal';
                        $order->payment_status = 'paid';
                        $order->save();
                    }
                }
                
                $payment->save();
                
                return redirect()->route('home')->with('success', 'Payment completed successfully!');
            } else {
                throw new \Exception('Payment not completed');
            }
        } catch (\Exception $e) {
            Log::error('PayPal payment capture error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('home')->with('error', 'Payment failed');
        }
    }
    
    /**
     * Handle cancelled payment
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        return redirect()->route('home')->with('info', 'Payment was cancelled.');
    }
    
    /**
     * Handle PayPal webhook
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        try {
            $data = $this->paypalService->processWebhook(
                $request->getContent(),
                $request->headers->all()
            );
            
            // Process the webhook data
            // For example, update order status, send confirmation email, etc.
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }
}
