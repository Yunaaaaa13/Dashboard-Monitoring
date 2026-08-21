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
        Schema::create('purchasing_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code')->unique();
            $table->string('category_name');
            $table->string('pic_buyer');
            $table->integer('monthly_target_units')->default(5000);
            $table->enum('status', ['Active', 'Review', 'Hold'])->default('Active');
            $table->timestamps();
        });

        Schema::create('purchasing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchasing_category_id')->constrained('purchasing_categories')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('po_reference')->nullable();
            $table->string('period_month'); // Format: YYYY-MM (misal: 2026-07)
            $table->integer('target_order')->default(0);
            $table->integer('actual_received')->default(0);
            $table->integer('pending_order')->default(0);
            $table->string('status_note')->default('Normal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_logs');
        Schema::dropIfExists('purchasing_categories');
    }
};
