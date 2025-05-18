<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        
        $query = Expense::with(['category', 'method', 'user']);
        
        // Search by description
        if ($request->has('search') && !empty($request->search)) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        
        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by method
        if ($request->has('method_id') && !empty($request->method_id)) {
            $query->where('method_id', $request->method_id);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }
        
        // Filter by user
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }
        
        // Sort by
        if ($request->has('sort_by') && !empty($request->sort_by)) {
            $sortField = $request->sort_by;
            $sortOrder = $request->has('sort_order') && $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest(); // Default sort by latest
        }
        
        $expenses = $query->paginate($perPage);
        
        // Calculate total amount for the current filtered set
        $totalAmount = $query->sum('amount');
        
        return response()->json([
            'data' => $expenses->items(),
            'total' => $expenses->total(),
            'total_amount' => $totalAmount,
            'current_page' => $expenses->currentPage(),
            'per_page' => $expenses->perPage(),
            'last_page' => $expenses->lastPage()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'details' => 'required|string|min:3',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date_format:d/m/Y',
            'category_id' => 'required|exists:expense_categories,category_id',
            'method_id' => 'required|exists:expense_methods,method_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Convert date format from d/m/Y to Y-m-d for database
        $expenseDate = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');

        // Get the user ID - use a default if Auth::id() is null
        $userId = Auth::id();
        if (!$userId) {
            // Get the first user from the database as a fallback
            $userId = \App\Models\User::first()->id ?? 1;
        }

        $expense = Expense::create([
            'user_id' => $userId,
            'category_id' => $request->category_id,
            'method_id' => $request->method_id,
            'description' => $request->details,
            'amount' => $request->amount,
            'expense_date' => $expenseDate,
            'location' => $request->location,
            'is_recurring' => $request->is_recurring,
            'recurrence_pattern' => $request->recurrence_pattern,
        ]);

        return response()->json($expense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load(['category', 'method', 'user']);
        
        // Format the date to d/m/Y for frontend
        $expense->date = Carbon::parse($expense->expense_date)->format('d/m/Y');
        $expense->details = $expense->description;
        
        return response()->json($expense);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'details' => 'required|string|min:3',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date_format:d/m/Y',
            'category_id' => 'required|exists:expense_categories,category_id',
            'method_id' => 'required|exists:expense_methods,method_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Convert date format from d/m/Y to Y-m-d for database
        $expenseDate = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');

        $expense->update([
            'category_id' => $request->category_id,
            'method_id' => $request->method_id,
            'description' => $request->details,
            'amount' => $request->amount,
            'expense_date' => $expenseDate,
        ]);

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully']);
    }

    // ------------ Today Expense -----------------
    public function todayExpense()
    {
        $today = Carbon::today()->format('Y-m-d');
        $expense = Expense::whereDate('expense_date', $today)->sum('amount');
        return response()->json($expense);
    }

    // ------------ Yesterday Expense -----------------
    public function yesterdayExpense()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $expense = Expense::whereDate('expense_date', $yesterday)->sum('amount');
        return response()->json($expense);
    }

    // ------------ Monthly Expense -----------------
    public function monthlyExpense()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $expense = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');
        return response()->json($expense);
    }
}
