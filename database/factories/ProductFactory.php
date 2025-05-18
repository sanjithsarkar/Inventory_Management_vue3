<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buyingPrice = fake()->randomFloat(2, 5, 50);
        $sellingPrice = $buyingPrice * fake()->randomFloat(1, 1.2, 2.0);
        
        // Generate SKU
        $datePart = Carbon::now()->format('Ymd');
        $randomSuffix = strtoupper(Str::random(4));
        $sku = "{$datePart}-{$randomSuffix}";
        
        return [
            'sku' => $sku,
            'category_id' => Category::inRandomOrder()->first()?->id ?? 1,
            'name' => fake()->words(3, true),
            'code' => fake()->unique()->ean13(),
            'root' => fake()->randomElement(['pcs', 'kg', 'liter', 'box']),
            'buying_price' => $buyingPrice,
            'selling_price' => $sellingPrice,
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? null,
            'buying_date' => fake()->date(),
            'image' => null,
            'quantity' => fake()->numberBetween(10, 100),
            'description' => fake()->paragraph(2),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
