<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
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

    /**
     * Process payment with Stripe
     */
    public function payment(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'totalAmount' => 'required|numeric|min:0.01',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.name' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0'
            ]);
            
            // Use the Stripe service to create a checkout session
            $response = $this->stripeService->createCheckoutSession(
                $request->totalAmount,
                $request->items,
                $request->customer_id ?? null
            );
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle successful payment
     */
    public function success()
    {
        return redirect(route('products.index'));
    }
    
    /**
     * Handle cancelled payment
     */
    public function cancel()
    {
        return redirect(route('orders.index'));
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
}
