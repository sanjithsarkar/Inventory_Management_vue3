<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
}
