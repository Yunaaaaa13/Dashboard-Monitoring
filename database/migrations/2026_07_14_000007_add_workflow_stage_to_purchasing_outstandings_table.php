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
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            $table->string('workflow_stage')->default('waiting_manager')->after('status');
            $table->text('approval_notes')->nullable()->after('workflow_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            $table->dropColumn(['workflow_stage', 'approval_notes']);
        });
    }
};
