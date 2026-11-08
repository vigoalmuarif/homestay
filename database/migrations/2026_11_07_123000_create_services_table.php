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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('category', ['meal', 'beverage', 'amenity', 'rental', 'laundry', 'other']);
            $table->string('sub_category', 50)->nullable(); // 'breakfast', 'lunch', 'dinner', etc.
            $table->decimal('price', 12, 2);
            $table->boolean('has_stock')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

