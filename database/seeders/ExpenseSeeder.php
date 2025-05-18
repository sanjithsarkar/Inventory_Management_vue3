<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\ExpenseMethod;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default expense categories
        $categories = [
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet, etc.'],
            ['name' => 'Rent', 'description' => 'Office or workspace rent'],
            ['name' => 'Salaries', 'description' => 'Employee salaries and benefits'],
            ['name' => 'Supplies', 'description' => 'Office supplies and materials'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotional expenses'],
            ['name' => 'Travel', 'description' => 'Business travel expenses'],
            ['name' => 'Maintenance', 'description' => 'Equipment and facility maintenance'],
            ['name' => 'Insurance', 'description' => 'Business insurance premiums'],
            ['name' => 'Taxes', 'description' => 'Business taxes and fees'],
            ['name' => 'Miscellaneous', 'description' => 'Other business expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }

        // Create default payment methods
        $methods = [
            ['name' => 'Cash', 'description' => 'Cash payment'],
            ['name' => 'Credit Card', 'description' => 'Credit card payment'],
            ['name' => 'Debit Card', 'description' => 'Debit card payment'],
            ['name' => 'Bank Transfer', 'description' => 'Direct bank transfer'],
            ['name' => 'Check', 'description' => 'Check payment'],
        ];

        foreach ($methods as $method) {
            ExpenseMethod::create($method);
        }
    }
}