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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('restrict');
            $table->dateTime('check_in_date');
            $table->dateTime('check_out_date');
            $table->unsignedInteger('total_nights')->default(1);
            $table->decimal('room_price_snapshot', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamp('actual_check_in_at')->nullable();
            $table->timestamp('actual_check_out_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'check_in_date', 'check_out_date']);
            $table->index('status');
            $table->index('check_in_date');
            $table->index('check_out_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

