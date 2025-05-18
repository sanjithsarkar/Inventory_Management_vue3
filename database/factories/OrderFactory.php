<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $discount = fake()->randomElement([0, 5, 10, 15, 20]);
        $discountAmount = ($subtotal * $discount) / 100;
        $total = $subtotal - $discountAmount;
        $paid = fake()->randomElement([$total, $total * 0.5, $total * 0.75]);
        $due = $total - $paid;

        return [
            'order_number' => generateUniqueNumber(),
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? null,
            'quantity' => fake()->numberBetween(1, 10),
            'subTotal' => $subtotal,
            'discount' => $discount,
            'discount_payment' => $discountAmount,
            'vat' => 0,
            'total' => $total,
            'paid' => $paid,
            'due' => $due,
            'payby' => fake()->randomElement(['Cash', 'Card', 'Transfer']),
            'date' => fake()->date('d/m/Y'),
            'month' => fake()->monthName(),
            'year' => fake()->year(),
        ];
    }
}