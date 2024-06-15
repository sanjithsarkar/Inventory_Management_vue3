<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    public function payment(Request $request)
    {
        // dd($request->all());
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
  
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->totalAmount,
                    ]
                ]
            ]
        ]);
  
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return response()->json(['paypalUrl' => $links['href']]);
                    // return response()->json([
                    //     'paypalUrl' => $links['href'],
                    //     'user_id' => $request->user()->id, // Example user ID
                    //     'order_id' => $request->order_id // Example order ID
                    // ]);
                }
            }
        } else {
            return response()->json(['error' => 'Unable to create PayPal order.'], 500);
        }
    
    }

    public function handleWebhook(Request $request)
    {
        // Log the webhook payload for debugging
        Log::info('PayPal Webhook Payload: ', $request->all());

        // Handle the webhook event
        switch ($request->event_type) {
            case 'CHECKOUT.ORDER.APPROVED':
                // Handle order approved event
                $this->handleOrderApproved($request);
                break;
            // Add more event types as needed
            default:
                Log::warning('Unhandled PayPal Webhook Event: ' . $request->event_type);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handleOrderApproved($request)
    {
        // Extract order details and process them
        $orderId = $request->resource['id'];
        $payer = $request->resource['payer'];

        // Perform necessary actions (e.g., update database, send notification)
        Log::info('Order Approved: ' . $orderId);
    }

    public function cancel() {
        return redirect()->route('home')->with('error', 'Payment has been canceled.');
    }

    public function success(Request $request) {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
  
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return redirect()
                ->route('paypal')
                ->with('success', 'Transaction complete.');
        } else {
            return redirect()
                ->route('paypal')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

}

