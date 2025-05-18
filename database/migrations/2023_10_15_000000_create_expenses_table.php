<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create expense categories table
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create expense payment methods table
        Schema::create('expense_methods', function (Blueprint $table) {
            $table->id('method_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create expenses table
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expense_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('method_id');
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->date('expense_date');
            $table->string('receipt_image')->nullable();
            $table->string('location', 100)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern', 50)->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('expense_categories');
            $table->foreign('method_id')->references('method_id')->on('expense_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_methods');
        Schema::dropIfExists('expense_categories');
    }
};