<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['sku', 'category_id', 'name', 'code', 'root', 'buying_price', 'selling_price', 'supplier_id', 'buying_date', 'image', 'quantity', 'description'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function order()
    {
        return $this->hasMany(Order::class);
    }
}
