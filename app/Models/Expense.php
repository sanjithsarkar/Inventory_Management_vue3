<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'expense_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'method_id',
        'amount',
        'description',
        'expense_date',
        'receipt_image',
        'location',
        'is_recurring',
        'recurrence_pattern',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user that owns the expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category that owns the expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id', 'category_id');
    }

    /**
     * Get the payment method that owns the expense.
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ExpenseMethod::class, 'method_id', 'method_id');
    }
}
