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
            for ($i = 4; $i <= 12; $i++) {
                $table->integer("m{$i}_po")->default(0)->after("m" . ($i - 1) . "_prod");
                $table->integer("m{$i}_prod")->default(0)->after("m{$i}_po");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            $cols = [];
            for ($i = 4; $i <= 12; $i++) {
                $cols[] = "m{$i}_po";
                $cols[] = "m{$i}_prod";
            }
            $table->dropColumn($cols);
        });
    }
};
