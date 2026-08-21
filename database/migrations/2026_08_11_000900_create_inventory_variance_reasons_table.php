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
        Schema::create('inventory_variance_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->index();
            $table->string('variance_type')->nullable(); // SURPLUS, DEFICIT, OPTIMAL
            $table->string('reason_category')->nullable(); // E.g., Delay Supplier, Scrap/Reject, Overproduction
            $table->text('reason_notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_variance_reasons');
    }
};
