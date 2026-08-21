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
        // 1. Tabel Import Batches (Pelacakan Batch & Idempotency)
        if (!Schema::hasTable('import_batches')) {
            Schema::create('import_batches', function (Blueprint $table) {
                $table->id();
                $table->string('batch_id')->unique()->index(); // misal: IMP-20260819-0001
                $table->string('template_type')->index();     // forecast, master_po, incoming, actual_production, actual_inventory
                $table->string('template_version')->default('1.0');
                $table->string('file_name');
                $table->string('file_hash')->index();          // md5/sha256 untuk deteksi duplikasi unggah file
                $table->string('uploaded_by')->nullable()->index();
                
                $table->integer('total_rows')->default(0);
                $table->integer('valid_rows')->default(0);
                $table->integer('warning_rows')->default(0);
                $table->integer('rejected_rows')->default(0);
                $table->integer('duplicate_rows')->default(0);

                // Rekonsiliasi Integritas
                $table->decimal('total_qty_source', 18, 4)->default(0);
                $table->decimal('total_qty_imported', 18, 4)->default(0);
                $table->decimal('reconciliation_diff', 18, 4)->default(0);
                $table->string('reconciliation_status')->default('SUCCESS'); // SUCCESS, WARNING, FAILED

                $table->string('status')->default('COMPLETED'); // PENDING_PREVIEW, COMMITTED, ROLLED_BACK, FAILED
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Import Audit Logs (Row-Level Diagnostics)
        if (!Schema::hasTable('import_audit_logs')) {
            Schema::create('import_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('batch_id')->index();
                $table->integer('row_number');
                $table->string('field')->nullable();
                $table->text('input_value')->nullable();
                $table->string('error_type')->default('SCHEMA_ERROR'); // SCHEMA_ERROR, NUMERIC_ERROR, MASTER_MISMATCH, DUPLICATE, OUTLIER
                $table->string('severity')->default('ERROR');          // ERROR, WARNING, INFO
                $table->text('error_message');
                $table->text('suggestion')->nullable();
                $table->boolean('is_resolved')->default(false);
                $table->timestamps();

                $table->index(['batch_id', 'row_number']);
            });
        }

        // 3. Tabel Data Change Logs (Audit Trail Perubahan Manual)
        if (!Schema::hasTable('data_change_logs')) {
            Schema::create('data_change_logs', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type')->index(); // Forecast, MasterPo, PurchasingLog, ActualProduction, Inventory
                $table->unsignedBigInteger('entity_id')->index();
                $table->string('field')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->string('changed_by')->nullable()->index();
                $table->string('reason')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_change_logs');
        Schema::dropIfExists('import_audit_logs');
        Schema::dropIfExists('import_batches');
    }
};
