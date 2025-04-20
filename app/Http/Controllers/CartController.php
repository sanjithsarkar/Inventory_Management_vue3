<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\orderProduct;
use App\Models\Pos;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use function PHPUnit\Framework\isEmpty;

class CartController extends Controller
{
    public function AddToCart($id, Request $request)
    {
        $product = Product::where('id', $id)->first();
        $exits = Pos::where('pro_id', $id)->first();
        // dd($product);

        if (!$exits) {
            if ($product->quantity >= 1) {
                $data = new Pos();
                $data->pro_id = $id;
                $data->name = $product->name;
                $data->quantity = 1;
                $data->price = $product->selling_price;
                $data->sub_total = $product->selling_price;
                $data->save();
                return response()->json($data);
            } else {
                return response()->json("This product is stock out!");
            }
        } else {
            $increment = Pos::where('pro_id', $id)->increment('quantity');

            $posProduct = Pos::where('pro_id', $id)->first();
            $sub_total = $posProduct->quantity * $posProduct->price;
            Pos::where('pro_id', $id)->update(['sub_total' => $sub_total]);
            return response()->json($sub_total);
        }
    }


    public function getAllCart()
    {
        #get all cart data with product
        $carts = Pos::with('product')->get();
        return response()->json($carts);
    }

    public function increaseCart($id, Request $request)
    {
        $dynamicParam = $request->input('dynamic');
        $quantity = $request->input('quantity');
        $posQuantity = Pos::where('id', $id)->select('quantity')->first();
        $productQuantity = Pos::where('id', $id)
            ->with(['product:id,quantity'])
            ->first();
        // dd($posQuantity);
        // dd($productQuantity->product->quantity);
        if($quantity >= $productQuantity->product->quantity || $posQuantity->quantity >= $productQuantity->product->quantity) {
            return response()->json("You can't add more than available quantity!");
        }

        if ($dynamicParam === 'dynamic') {
            // dd($quantity, 'quantity');
            $posQtyUpdate = Pos::where('id', $id)->update(['quantity' => $quantity]);
            $posData = Pos::where('id', $id)->first();
            $sub_total = $posData->quantity * $posData->price;
            Pos::where('id', $id)->update(['sub_total' => $sub_total]);
            // return response()->json($posData);
        } else {
            // dd($quantity);
            $product_increment = Pos::where('id', $id)->increment('quantity');
            $posData = Pos::where('id', $id)->first();
            $sub_total = $posData->quantity * $posData->price;
            Pos::where('id', $id)->update(['sub_total' => $sub_total]);
        }
    }


    public function decreaseCart($id)
    {
        $posQty = Pos::where('id', $id)->first();
        if ($posQty->quantity <= 1) {
            return response()->json('Quantity must be greater than 0');
        }
        $product_decrement = Pos::where('id', $id)->decrement('quantity');

        $posData = Pos::where('id', $id)->first();
        $sub_total = $posData->quantity * $posData->price;
        Pos::Where('id', $id)->update(['sub_total' => $sub_total]);
    }

    public function deleteCart($id)
    {

        $deletePos = Pos::where('id', $id)->first();
        $deletePos->delete();

        return response()->json($deletePos);
    }

    public function orderDone(Request $request)
    {
        // dd($request->all);

        $order = Order::create([
            'order_number' => generateUniqueNumber(),
            'customer_id' => $request->customer_id,
            'quantity' => $request->quantity,
            'subTotal' => $request->subTotal,
            'discount' => $request->discount,
            'discount_payment' => $request->discountPayment,
            'total' => $request->totalAmount,
            'paid' => $request->paymentReceive,
            'due' => $request->duePayment,
            'payby' => $request->payby,
            'date' => date('d/m/Y'),
            'month' => date('F'),
            'year' => date('Y'),
        ]);

        // $orderProducts = Pos::all()->map(function ($product) use ($order) {

        //     $updateProduct = Product::where('id', $product->pro_id)->first();
        //     $update = $updateProduct->quantity - $product->quantity;
        //     DB::table('products')->where('id', $product->pro_id)->update(['quantity'=> $update]);

        //     return [
        //         'order_id' => $order->id,
        //         'pro_id' => $product->pro_id,
        //         'name' => $product->name,
        //         'quantity' => $product->quantity,
        //         'price' => $product->price,
        //         'created_at' => Carbon::now(),
        //     ];
        // });

        // $orderProduct = OrderProduct::insert($orderProducts->toArray());

        $data = [];
        $pos = Pos::all();

        foreach ($pos as $product) {

            $data[] = [
                'order_id' => $order->id,
                'pro_id' => $product->pro_id,
                'name' => $product->name,
                'quantity' => $product->quantity,
                'price' => $product->price,
                'created_at' => Carbon::now(),
            ];


            // ---------- update product also stock --------------

            $updateProduct = Product::where('id', $product->pro_id)->first();
            $update = $updateProduct->quantity - $product->quantity;
            FacadesDB::table('products')->where('id', $product->pro_id)->update(['quantity' => $update]);
        }

        $orderProduct = OrderProduct::insert($data);


        // After insert Order & OrderProduct, delete POS data from database

        if ($order && $orderProduct) {
            $posData = Pos::all();
            $posData->each->delete();
        }
    }
}
