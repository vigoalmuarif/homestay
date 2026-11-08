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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->string('item_name', 150);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_or_allowance', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2);
            $table->enum('billing_type', ['charged_to_room', 'paid_instantly'])->default('charged_to_room');
            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('delivered');
            $table->boolean('is_free_inclusion')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['service_id']);
            $table->index('billing_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};

