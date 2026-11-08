<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Expand room status enum and add housekeeping tracking fields
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'dirty', 'cleaning', 'maintenance') DEFAULT 'available'");
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->timestamp('last_cleaned_at')->nullable()->after('notes');
            $table->foreignId('last_cleaned_by')->nullable()->after('last_cleaned_at')->constrained('users')->nullOnDelete();
            $table->text('housekeeping_notes')->nullable()->after('last_cleaned_by');
        });

        // 2. Create housekeeping tasks / work orders table
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Assigned housekeeper
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('task_type', 30)->default('checkout_clean'); // checkout_clean, stayover_clean, deep_clean, inspection, maintenance_repair
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->string('status', 20)->default('pending'); // pending, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['last_cleaned_by']);
            $table->dropColumn(['last_cleaned_at', 'last_cleaned_by', 'housekeeping_notes']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'dirty', 'maintenance') DEFAULT 'available'");
        }
    }
};
