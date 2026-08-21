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
            for ($i = 13; $i <= 24; $i++) {
                if (!Schema::hasColumn('purchasing_outstandings', "m{$i}_po")) {
                    $table->integer("m{$i}_po")->default(0);
                }
                if (!Schema::hasColumn('purchasing_outstandings', "m{$i}_prod")) {
                    $table->integer("m{$i}_prod")->default(0);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            for ($i = 13; $i <= 24; $i++) {
                $cols = [];
                if (Schema::hasColumn('purchasing_outstandings', "m{$i}_po")) {
                    $cols[] = "m{$i}_po";
                }
                if (Schema::hasColumn('purchasing_outstandings', "m{$i}_prod")) {
                    $cols[] = "m{$i}_prod";
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            }
        });
    }
};
