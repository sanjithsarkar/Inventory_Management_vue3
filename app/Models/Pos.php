<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pos extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pro_id',
        'quantity',
        'price',
        'sub_total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'pro_id', 'id');
    }
}
