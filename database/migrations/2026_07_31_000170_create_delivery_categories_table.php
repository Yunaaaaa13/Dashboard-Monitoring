<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('delivery_categories')) {
            Schema::create('delivery_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('currency', 10)->default('IDR');
                $table->timestamps();
            });

            // Seed data awal kategori pengantaran/asal barang
            DB::table('delivery_categories')->insert([
                [
                    'code'        => 'IMP',
                    'name'        => 'Impor (Import Parts)',
                    'description' => 'Pengadaan Impor dari luar negeri (Mata Uang USD)',
                    'currency'    => 'USD',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'code'        => 'LOC',
                    'name'        => 'Lokal (Local Parts)',
                    'description' => 'Pengadaan Lokal Dalam Negeri (Mata Uang IDR)',
                    'currency'    => 'IDR',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'code'        => 'CON',
                    'name'        => 'Consumable & Sub-Material',
                    'description' => 'Bahan Penolong & Perlengkapan Pabrik (IDR/USD)',
                    'currency'    => 'IDR',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_categories');
    }
};
