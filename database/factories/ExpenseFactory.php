<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isRecurring = $this->faker->boolean(20); // 20% chance of being recurring
        $expenseDate = $this->faker->dateTimeBetween('-3 months', 'now');
        
        return [
            'user_id' => function () {
                return User::inRandomOrder()->first()->id ?? User::factory()->create()->id;
            },
            'category_id' => function () {
                return ExpenseCategory::inRandomOrder()->first()->category_id ?? 
                       ExpenseCategory::factory()->create()->category_id;
            },
            'method_id' => function () {
                return ExpenseMethod::inRandomOrder()->first()->method_id ?? 
                       ExpenseMethod::factory()->create()->method_id;
            },
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence(),
            'expense_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'location' => $this->faker->city(),
            'is_recurring' => $isRecurring,
            'recurrence_pattern' => $isRecurring ? $this->faker->randomElement(['daily', 'weekly', 'monthly', 'yearly']) : null,
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }
}
