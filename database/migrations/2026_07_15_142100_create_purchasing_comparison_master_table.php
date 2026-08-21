<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchasing_comparison_master', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->index();
            $table->string('description')->nullable();
            $table->string('periode')->index(); // format: Y-m (e.g. 2026-07)

            // Data Outstanding (Target)
            $table->bigInteger('outstanding_qty')->default(0);

            // Data Forecast / Actual (Realisasi)
            $table->bigInteger('actual_po')->default(0);
            $table->bigInteger('actual_production')->default(0);

            // Kalkulasi otomatis (PT Kawai: Forecast Actual = PO - Outstanding)
            $table->bigInteger('forecast_actual')->default(0);

            // Analisis
            $table->bigInteger('selisih')->default(0);  // forecast_actual - outstanding_qty
            $table->decimal('coverage', 8, 2)->nullable();  // (forecast_actual / outstanding_qty) * 100

            // Status
            $table->string('status')->default('Menunggu Data');
            $table->string('status_badge')->default('bg-secondary text-white');

            // Kelengkapan data
            $table->boolean('has_outstanding')->default(false);
            $table->boolean('has_forecast')->default(false);

            // Timestamp sinkronisasi
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['part_number', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchasing_comparison_master');
    }
};
