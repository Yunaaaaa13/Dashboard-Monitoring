<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_pos')) {
            try {
                Schema::table('master_pos', function (Blueprint $table) {
                    $table->dropUnique('master_pos_po_item_unique');
                });
            } catch (\Throwable $e) {
                // Index already dropped or doesn't exist
            }
        }
    }

    public function down(): void
    {
        // No-op to allow multiple rows per PO and item code
    }
};
