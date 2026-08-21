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
        if (!Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->string('part_number')->index();
                $table->string('drawing')->nullable()->index();
                $table->string('description')->nullable();
                $table->string('supplier_name')->nullable();
                $table->foreignId('category_id')->nullable()->index();
                $table->string('factory_code')->default('Plant 3')->index();

                // Pre-Month Inventory (Month 0 / Plan Inventory)
                $table->integer('m0_inventory')->default(0);

                // Monthly Running Inventory (Months 1 to 36)
                for ($i = 1; $i <= 36; $i++) {
                    $table->integer("m{$i}_inventory")->default(0);
                }

                // Inventory Master & Warehouse Attributes
                $table->integer('current_stock')->default(0);
                $table->integer('min_stock')->default(0);
                $table->integer('max_stock')->default(0);
                $table->string('unit_measure')->default('PCS');
                $table->decimal('unit_price', 15, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('warehouse_location')->nullable();
                $table->string('status')->default('OPTIMAL'); // OPTIMAL, DEFICIT, OVERSTOCK
                $table->foreignId('user_id')->nullable()->index();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
