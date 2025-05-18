<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseMethod;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $this->call(UserTableSeeder::class);
        
        // Create additional users
        User::factory(5)->create();
        
        // Create categories
        Category::factory(10)->create();
        
        // Create suppliers
        Supplier::factory(15)->create();
        
        // Create customers
        Customer::factory(20)->create();
        
        // Create products
        Product::factory(30)->create();
        
        // Create orders with order products
        Order::factory(50)->create()->each(function ($order) {
            // Create 1-5 products for each order
            $numProducts = rand(1, 5);
            OrderProduct::factory($numProducts)->create([
                'order_id' => $order->id
            ]);
        });
        
        // Create expense categories
        ExpenseCategory::factory(10)->create();
        
        // Create expense payment methods
        ExpenseMethod::factory(5)->create();
        
        // Create expenses
        Expense::factory(100)->create();
    }
}
