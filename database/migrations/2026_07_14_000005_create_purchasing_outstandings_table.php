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
        Schema::create('purchasing_outstandings', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique();
            $table->string('description');
            $table->integer('order_qty')->default(0);
            $table->string('drawing')->nullable();
            $table->bigInteger('price')->default(0);
            $table->bigInteger('amount')->default(0);
            $table->integer('complete')->default(0);
            $table->string('status')->default('Pending'); // 'Pending', 'On Progress', 'Complete'
            $table->string('supplier_name')->nullable();
            $table->date('eta_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_outstandings');
    }
};
