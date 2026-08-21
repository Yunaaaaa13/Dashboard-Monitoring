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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_filename')->nullable();
            $table->string('file_hash', 64)->nullable()->index();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('document_type', 30);
            $table->float('document_type_confidence')->nullable();
            $table->unsignedInteger('detected_header_row')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('total_columns')->default(0);
            $table->string('date_range_min', 7)->nullable();
            $table->string('date_range_max', 7)->nullable();
            $table->unsignedInteger('unique_items')->default(0);
            $table->unsignedInteger('unique_pos')->default(0);
            $table->unsignedInteger('unique_suppliers')->default(0);
            $table->json('currency_distribution')->nullable();
            $table->json('profile_data')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('status', 30)->default('UPLOADED')->index();
            $table->timestamps();
        });

        Schema::create('import_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('session_code')->unique();
            $table->string('status', 30)->default('ANALYZING');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_count')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('inserted_po_count')->default(0);
            $table->unsignedInteger('inserted_incoming_count')->default(0);
            $table->unsignedBigInteger('imported_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
        });

        Schema::create('column_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('source_column_letter', 5);
            $table->string('source_column_name')->nullable();
            $table->string('canonical_field', 50);
            $table->float('confidence')->default(0);
            $table->string('mapping_method', 30)->default('AUTO');
            $table->boolean('confirmed_by_user')->default(false);
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
        });

        Schema::create('column_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('raw_name');
            $table->string('canonical_field', 50);
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamps();
            
            $table->unique(['raw_name', 'canonical_field']);
        });

        Schema::create('data_quality_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('import_session_id')->nullable();
            $table->unsignedInteger('row_number');
            $table->string('column_name')->nullable();
            $table->string('issue_type', 30);
            $table->string('severity', 10);
            $table->text('message');
            $table->text('raw_value')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'severity']);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('import_session_id')->references('id')->on('import_sessions')->onDelete('cascade');
        });

        Schema::create('reconciliation_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('import_session_id')->nullable();
            $table->string('item_code');
            $table->string('po_number')->nullable();
            $table->string('supplier')->nullable();
            $table->string('period', 7);
            $table->integer('po_qty')->default(0);
            $table->integer('received_qty')->default(0);
            $table->integer('outstanding_qty')->default(0);
            $table->float('fulfillment_pct')->default(0);
            $table->decimal('po_amount_usd', 15, 2)->default(0);
            $table->decimal('received_amount_usd', 15, 2)->default(0);
            $table->unsignedTinyInteger('match_level')->default(0);
            $table->float('match_confidence')->default(0);
            $table->string('status', 20)->index();
            $table->timestamps();

            $table->index(['item_code', 'period']);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('import_session_id')->references('id')->on('import_sessions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_results');
        Schema::dropIfExists('data_quality_issues');
        Schema::dropIfExists('column_aliases');
        Schema::dropIfExists('column_mappings');
        Schema::dropIfExists('import_sessions');
        Schema::dropIfExists('documents');
    }
};
