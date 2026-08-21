<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outstanding_records', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->string('period_month'); // YYYY-MM
            $table->integer('outstanding_qty')->default(0);
            $table->timestamps();

            $table->unique(['part_number', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outstanding_records');
    }
};
