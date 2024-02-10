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
        $searchQuery = $request->input('query');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');


        $orders = Order::query()
            ->when(request('query'), function ($query) use ($searchQuery) {
                $query->where('order_number', 'like', '%' . $searchQuery . '%');
            })
            ->when(request('startDate'), function ($query) use ($startDate, $endDate) {
                $firstdate = date_create($startDate);
                $firstDate = date_format($firstdate, "d/m/Y");

                if (request('endDate')) {
                    $lastdate = date_create($endDate);
                    $lastDate = date_format($lastdate, "d/m/Y");

                    $query->whereBetween('date', [$firstDate, $lastDate]);
                } else {

                    $query->where('date', $firstDate);
                }
            })
            ->with('customer')
            ->latest()->paginate(5);

        return response()->json($orders);
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
