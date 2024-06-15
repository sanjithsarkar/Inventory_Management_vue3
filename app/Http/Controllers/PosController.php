<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PosController extends Controller
{
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

        dd($pos);

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
        try {

            // dd($request->all());
            // Set the Stripe API key
            Stripe::setApiKey(env('STRIPE_SK'));
            // Stripe::setApiKey(config('stripe.sk'));
    
            // Create a new Stripe Checkout Session
            $response = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => ($request->totalAmount) * 100, // Convert to cents
                            'product_data' => [
                                'name' => "test",
                            ],
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('stripe.success'), // Set your success URL
                'cancel_url' => route('stripe.cancel'), // Set your cancel URL
            ]);
    
            // Return the session ID to the frontend
            return response()->json(['url' => $response->url]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle the error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success()
    {
        return view('stripe.success'); // You can create a success blade template
    }

    public function cancel()
    {
        return view('stripe.cancel'); // You can create a cancel blade template
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
