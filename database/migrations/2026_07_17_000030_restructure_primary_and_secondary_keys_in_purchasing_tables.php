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
         // 1. Tambahkan kolom receipt_date, item_code, item_name, supplier_name di purchasing_logs
         if (Schema::hasTable('purchasing_logs')) {
             Schema::table('purchasing_logs', function (Blueprint $table) {
                 if (!Schema::hasColumn('purchasing_logs', 'receipt_date')) {
                     $table->date('receipt_date')->nullable()->after('po_reference');
                 }
                 if (!Schema::hasColumn('purchasing_logs', 'item_code')) {
                     $table->string('item_code')->nullable()->after('receipt_date');
                 }
                 if (!Schema::hasColumn('purchasing_logs', 'item_name')) {
                     $table->string('item_name')->nullable()->after('item_code');
                 }
                 if (!Schema::hasColumn('purchasing_logs', 'supplier_name')) {
                     $table->string('supplier_name')->nullable()->after('item_name');
                 }
             });

             // Update index jika belum ada
             try {
                 Schema::table('purchasing_logs', function (Blueprint $table) {
                     $table->index('item_code');
                     $table->index('po_reference');
                 });
             } catch (\Exception $e) {
                 // Index mungkin sudah ada
             }
         }

         // 2. Sesuaikan indeks di purchasing_outstandings agar Item Code (drawing) bisa jadi Primary Key
         // dan No. PO (part_number/po_number) menjadi Secondary Key, tidak terbentur unique constraint tunggal lama
         if (Schema::hasTable('purchasing_outstandings')) {
             try {
                 Schema::table('purchasing_outstandings', function (Blueprint $table) {
                     // Drop unique constraint pada part_number jika ada agar 1 Item Code bisa memiliki beberapa No PO / sebaliknya
                     $table->dropUnique('purchasing_outstandings_part_number_unique');
                 });
             } catch (\Exception $e) {
                 // Unique index mungkin sudah tidak ada atau nama index berbeda
             }

             try {
                 Schema::table('purchasing_outstandings', function (Blueprint $table) {
                     $table->index('drawing');
                     $table->index('part_number');
                     $table->index('po_number');
                 });
             } catch (\Exception $e) {
                 // Index mungkin sudah ada
             }
         }

         // 3. Update data existing di purchasing_logs yang belum punya receipt_date atau item_code dari Master
         if (Schema::hasTable('purchasing_logs') && Schema::hasTable('purchasing_outstandings')) {
             if (DB::connection()->getDriverName() === 'sqlite') {
                 // SQLite tidak mendukung UPDATE ... LEFT JOIN maupun DATE_FORMAT.
                 // Gunakan pembaruan per baris agar migrasi juga dapat dipakai tes.
                 $logs = DB::table('purchasing_logs')
                     ->where(function ($query) {
                         $query->whereNull('item_code')->orWhere('item_code', '');
                     })->get();
                 foreach ($logs as $log) {
                     $master = DB::table('purchasing_outstandings')
                         ->where('part_number', $log->po_reference)
                         ->orWhere('po_number', $log->po_reference)
                         ->first();
                     if ($master) {
                         DB::table('purchasing_logs')->where('id', $log->id)->update([
                             'item_code' => $master->drawing,
                             'item_name' => $master->description,
                             'supplier_name' => $master->supplier_name,
                         ]);
                     }
                 }
                 DB::table('purchasing_logs')
                     ->whereNull('receipt_date')
                     ->whereNotNull('period_month')
                     ->where('period_month', '!=', '')
                     ->update(['receipt_date' => DB::raw("period_month || '-15'")]);
             } else {
                 DB::statement("
                     UPDATE purchasing_logs pl
                     LEFT JOIN purchasing_outstandings po ON po.part_number = pl.po_reference OR po.po_number = pl.po_reference
                     SET pl.item_code = po.drawing,
                         pl.item_name = po.description,
                         pl.supplier_name = po.supplier_name
                     WHERE (pl.item_code IS NULL OR pl.item_code = '') AND po.id IS NOT NULL
                 ");

                 DB::statement("
                     UPDATE purchasing_logs
                     SET receipt_date = DATE_FORMAT(CONCAT(period_month, '-15'), '%Y-%m-%d')
                     WHERE receipt_date IS NULL AND period_month IS NOT NULL AND period_month != ''
                 ");
             }
         }
     }

     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         if (Schema::hasTable('purchasing_logs')) {
             Schema::table('purchasing_logs', function (Blueprint $table) {
                 if (Schema::hasColumn('purchasing_logs', 'receipt_date')) {
                     $table->dropColumn('receipt_date');
                 }
                 if (Schema::hasColumn('purchasing_logs', 'item_code')) {
                     $table->dropColumn('item_code');
                 }
                 if (Schema::hasColumn('purchasing_logs', 'item_name')) {
                     $table->dropColumn('item_name');
                 }
                 if (Schema::hasColumn('purchasing_logs', 'supplier_name')) {
                     $table->dropColumn('supplier_name');
                 }
             });
         }
     }
};
