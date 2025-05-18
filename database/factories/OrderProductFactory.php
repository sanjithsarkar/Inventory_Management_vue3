<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct>
 */
class OrderProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();
        $quantity = fake()->numberBetween(1, 5);
        
        return [
            'order_id' => Order::factory(),
            'pro_id' => $product?->id ?? null,
            'name' => $product?->name ?? fake()->word(),
            'quantity' => $quantity,
            'price' => $product?->price ?? fake()->randomFloat(2, 10, 100),
            'created_at' => now(),
        ];
    }
}