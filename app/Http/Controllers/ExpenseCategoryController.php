<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory)
    {
        return response()->json($expenseCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->category_id . ',category_id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expenseCategory->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($expenseCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Check if category is in use
        if ($expenseCategory->expenses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category because it is associated with expenses'
            ], 422);
        }

        $expenseCategory->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}