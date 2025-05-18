<?php

namespace App\Http\Controllers;

use App\Models\ExpenseMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $methods = ExpenseMethod::orderBy('name')->get();
        return response()->json($methods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_methods',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $method = ExpenseMethod::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($method, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseMethod $expenseMethod)
    {
        return response()->json($expenseMethod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseMethod $expenseMethod)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_methods,name,' . $expenseMethod->method_id . ',method_id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expenseMethod->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($expenseMethod);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseMethod $expenseMethod)
    {
        // Check if method is in use
        if ($expenseMethod->expenses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete payment method because it is associated with expenses'
            ], 422);
        }

        $expenseMethod->delete();
        return response()->json(['message' => 'Payment method deleted successfully']);
    }
}