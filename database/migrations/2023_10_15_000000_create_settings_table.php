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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Insert default currency settings
        DB::table('settings')->insert([
            [
                'key' => 'currency_code',
                'value' => 'USD',
                'group' => 'currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'group' => 'currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency_position',
                'value' => 'before',
                'group' => 'currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'decimal_separator',
                'value' => '.',
                'group' => 'currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'thousand_separator',
                'value' => ',',
                'group' => 'currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'decimal_places',
                'value' => '2',
                'group' => 'currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};