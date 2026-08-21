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
        Schema::create('production_lines', function (Blueprint $table) {
            $table->id();
            $table->string('line_code')->unique();
            $table->string('line_name');
            $table->string('product_category');
            $table->string('supervisor');
            $table->integer('daily_target_capacity')->default(2000);
            $table->enum('status', ['Running', 'Idle', 'Maintenance', 'Alert'])->default('Running');
            $table->timestamps();
        });

        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained('production_lines')->onDelete('cascade');
            $table->string('ezrunner_batch_id')->nullable();
            $table->dateTime('log_time');
            $table->integer('target_output')->default(0);
            $table->integer('actual_output')->default(0);
            $table->integer('defect_count')->default(0);
            $table->string('status_note')->default('Normal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_logs');
        Schema::dropIfExists('production_lines');
    }
};
