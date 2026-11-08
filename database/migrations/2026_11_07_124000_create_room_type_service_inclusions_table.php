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
        Schema::create('room_type_service_inclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->enum('category', ['meal', 'beverage', 'amenity', 'rental', 'laundry', 'any'])->default('meal');
            $table->string('session_time', 50)->nullable(); // 'breakfast', 'lunch', 'dinner', etc.
            $table->decimal('allowance_limit', 12, 2)->default(0.00); // 0.00 = full cover tanpa batas nominal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type_service_inclusions');
    }
};

