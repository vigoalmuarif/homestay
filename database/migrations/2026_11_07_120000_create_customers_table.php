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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->enum('identity_type', ['ktp', 'paspor', 'sim', 'other'])->default('ktp');
            $table->string('identity_number', 50)->nullable()->unique();
            $table->string('phone_number', 30)->index();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('country', 100)->default('Indonesia');
            $table->text('special_notes')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

