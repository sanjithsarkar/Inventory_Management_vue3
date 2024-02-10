<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_number','customer_id', 'quantity', 'subTotal', 'discount', 'discount_payment', 'vat', 'total', 'paid', 'due', 'payby', 'date', 'month', 'year'];

    public function orderProducts(){
        return $this->belongsTo(orderProduct::class, 'order_id', 'id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
