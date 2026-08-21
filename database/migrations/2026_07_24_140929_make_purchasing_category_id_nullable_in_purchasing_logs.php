<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ubah purchasing_category_id menjadi nullable agar realisasi PO dari
     * Master PO (Step 2) yang tidak memiliki kategori tetap bisa disimpan.
     */
    public function up(): void
    {
        Schema::table('purchasing_logs', function (Blueprint $table) {
            // Drop foreign key dulu sebelum mengubah kolom
            $table->dropForeign(['purchasing_category_id']);
            // Ubah kolom menjadi nullable
            $table->unsignedBigInteger('purchasing_category_id')->nullable()->change();
            // Tambah kembali foreign key constraint dengan nullable
            $table->foreign('purchasing_category_id')
                  ->references('id')
                  ->on('purchasing_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_logs', function (Blueprint $table) {
            $table->dropForeign(['purchasing_category_id']);
            $table->unsignedBigInteger('purchasing_category_id')->nullable(false)->change();
            $table->foreign('purchasing_category_id')
                  ->references('id')
                  ->on('purchasing_categories')
                  ->onDelete('cascade');
        });
    }
};
