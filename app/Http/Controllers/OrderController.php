<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\orderProduct;
use App\Models\Product;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $currentPage = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        $searchQuery = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $ordersQuery = Order::query()
            ->when(!empty($searchQuery), function ($query) use ($searchQuery) {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('order_number', 'like', '%' . $searchQuery . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($searchQuery) {
                            $customerQuery->where('name', 'like', '%' . $searchQuery . '%');
                        });
                });
            })
            ->when(!empty($startDate), function ($query) use ($startDate, $endDate) {
                $start = Carbon::parse($startDate)->format('Y/m/d');
                if (!empty($endDate)) {
                    $end = Carbon::parse($endDate)->format('Y/m/d');
                    $query->whereBetween('date', [$start, $end]);
                } else {
                    $query->whereDate('date', $start);
                }
            })
            ->with('customer')
            ->latest();

        $orders = $ordersQuery->paginate($perPage, ['*'], 'page', $currentPage);

        return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage()
            ]
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    public function searchByDate(Request $request)
    {
        $orderDate = $request->input('query');
        $newDate = new DateTime($orderDate);
        $date = $newDate->format('d/m/Y');

        $order = Order::where('date', $date)->get();

        return response()->json($order);
    }


    // ------------------- get order by id --------------------


    public function getOrder($id)
    {
        $order = Order::with('customer')->where('id', $id)->get();
        return response()->json($order);
    }


    // --------------------- order product ----------------

    public function getOrderProduct($id)
    {
        $product = orderProduct::where('order_id', $id)->get();
        // dd($product->toArray());
        return response()->json($product);
    }

    // -------------------- Today Sell ----------------

    public function todaySale()
    {
        $date = Carbon::now()->format('Y/m/d');
        $sell = Order::where('date', $date)->sum('total');
        return response()->json($sell);
    }

    // -------------- Today Income ----------------

    public function todayIncome()
    {
        $date = Carbon::now()->format('Y/m/d');
        $income = Order::where('date', $date)->sum('paid');
        return response()->json($income);
    }

    // -------------- todayDue -----------

    public function todayDue()
    {
        $today = Carbon::now()->format('Y/m/d');
        $due = Order::where('date', $today)->sum('due');
        return response()->json($due);
    }

    private function getYesterdayDate()
    {
        return Carbon::yesterday()->format('Y-m-d');
    }

    /**
     * Get yesterday's sales total
     */
    public function yesterdaySales()
    {
        $yesterday = $this->getYesterdayDate();
        
        $total = Order::where('date', $yesterday)
                    ->sum('total');
        return response()->json([
            'amount' => (float) $total,
            'date' => $yesterday
        ]);
    }

    /**
     * Get yesterday's income total
     */
    public function yesterdayIncome()
    {
        $yesterday = $this->getYesterdayDate();
        
        $total = Order::where('date', $yesterday)
                      ->sum('paid');
        
        return response()->json([
            'amount' => (float) $total,
            'date' => $yesterday
        ]);
    }

    /**
     * Get yesterday's due total
     */
    public function yesterdayDue()
    {
        $yesterday = $this->getYesterdayDate();
        
        $total = Order::where('date', $yesterday)
                   ->sum('due');
        
        return response()->json([
            'amount' => (float) $total,
            'date' => $yesterday
        ]);
    }

    /**
     * Get yesterday's expense total
     */
    public function yesterdayExpense()
    {
        $yesterday = $this->getYesterdayDate();
        
        $total = Order::where('date', $yesterday)
                       ->sum('expense');
        
        return response()->json([
            'amount' => (float) $total,
            'date' => $yesterday
        ]);
    }
}
