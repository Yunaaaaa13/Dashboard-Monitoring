<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecastings', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->string('description')->nullable();
            $table->string('period_month'); // YYYY-MM
            $table->integer('po_qty')->default(0);
            $table->integer('production_qty')->default(0);
            $table->integer('stock_qty')->default(0);
            $table->integer('actual_qty')->default(0);
            $table->timestamps();

            $table->unique(['part_number', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecastings');
    }
};
