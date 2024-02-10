<?php

// use Illuminate\Support\Str;
use App\Models\Order;

function generateUniqueNumber($length = 8)
{
    $number = mt_rand(pow(10, $length - 1), pow(10, $length) - 1);

    if (Order::where('order_number', $number)->exists()) {
        return generateUniqueNumber($length);
    }

    return $number;
}
