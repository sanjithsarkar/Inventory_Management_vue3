<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\Stripe\StripeService;
use App\Helpers\PaymentHelper;

class PosController extends Controller
{
    protected $stripeService;
    
    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'pro_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $customer = Pos::create([
            'name' => $request->name,
            'pro_id' => $request->pro_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'sub_total' => $request->sub_total,
        ]);

        return response()->json($customer);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'pro_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $customer = Pos::create([
            'name' => $request->name,
            'pro_id' => $request->pro_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'sub_total' => $request->sub_total,
        ]);

        return response()->json($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pos $pos, Request $request)
    {
        //     $product = Product::where('id', $pos->pro_id);
        //     dd($product);
        //    $pos->name = $product->name;
        //    $pos->pro_id = $pos->pro_id;
        //    $pos->price = $product->price;
        //    $pos->quantity = $product->quantity;
        //    $pos->save();

        return response()->json($pos);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pos $pos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pos $pos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pos $pos)
    {
        //
    }

    public function payment(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'totalAmount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Get currency from settings or config
            $currency = strtolower(Setting::get('currency_code', 'currency') ?? config('stripe.currency', 'usd'));
            
            // Convert amount to cents and ensure it's an integer
            $originalAmount = $request->totalAmount;
            $amountInCents = PaymentHelper::amountToCents($originalAmount);

            Log::debug('Stripe payment amount conversion', [
                'originalAmount' => $originalAmount,
                'amountInCents' => $amountInCents,
                'currency' => $currency
            ]);
            
            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'unit_amount' => $amountInCents,
                            'product_data' => [
                                'name' => "test",
                            ],
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('stripe.success'),
                'cancel_url' => route('stripe.cancel'),
            ];
            
            $session = $this->stripeService->createCheckoutSession($sessionData);
            
            // Return the session URL to the frontend
            return response()->json(['url' => $session->url]);
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Handle the error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success()
    {
        // return view('products.index'); // You can create a success blade template
        return redirect(route('products.index'));
    }

    public function cancel()
    {
        return redirect(route('orders.index')); // You can create a cancel blade template
    }

    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('stripe.sk'));

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                Log::info('PaymentIntent was successful!');
                // Handle the successful payment here
                break;
            // Add other event types here
            default:
                return response()->json(['error' => 'Unhandled event type'], 400);
        }

        return response()->json(['status' => 'success'], 200);
    }
}
