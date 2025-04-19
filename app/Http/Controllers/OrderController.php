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
            $query->where('order_number', 'like', '%' . $searchQuery . '%');
        })
        ->when(!empty($startDate), function ($query) use ($startDate, $endDate) {
            $start = date('Y-m-d 00:00:00', strtotime($startDate));
            if (!empty($endDate)) {
                $end = date('Y-m-d 23:59:59', strtotime($endDate));
                $query->whereBetween('created_at', [$start, $end]);
            } else {
                $query->whereBetween('created_at', [$start, Carbon::now()]);
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

    public function todaySell()
    {
        $date = date('d/m/Y');
        $sell = Order::where('date', $date)->sum('total');
        return response()->json($sell);
    }

    // -------------- Today Income ----------------

    public function todayIncome()
    {
        $date = date('d/m/Y');
        $income = Order::where('date', $date)->sum('paid');
        return response()->json($income);
    }

    // -------------- todayDue -----------

    public function todayDue()
    {
        $date = date('d/m/Y');
        $due = Order::where('date', $date)->sum('due');
        return response()->json($due);
    }
}
