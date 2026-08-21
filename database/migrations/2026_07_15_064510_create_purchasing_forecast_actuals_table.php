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
        Schema::create('purchasing_forecast_actuals', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->index();
            $table->string('description')->nullable();
            $table->string('periode')->index(); // e.g. 2026-07
            $table->bigInteger('po')->default(0);
            $table->bigInteger('forecast_actual')->default(0); // Dihitung otomatis: po - outstanding
            $table->timestamps();

            $table->unique(['part_number', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_forecast_actuals');
    }
};
