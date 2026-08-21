<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('exch_year')->default(date('Y'));
            $table->tinyInteger('exch_month')->default(1);   // 1–12
            $table->tinyInteger('week_code')->default(1);    // 1–5 (minggu ke-N dalam bulan)
            $table->tinyInteger('currency_code')->default(2); // 2 = USD/IDR; bisa dikembangkan
            $table->unsignedBigInteger('tax_exchange_rate'); // nilai kurs, cth: 16777
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('last_update')->nullable();
            $table->string('last_user', 100)->nullable();
            $table->date('register_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            // Pastikan tidak ada duplikat tahun+bulan+minggu+currency
            $table->unique(['exch_year', 'exch_month', 'week_code', 'currency_code'], 'unique_exchange_week');
            $table->index(['exch_year', 'exch_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_exchange_rates');
    }
};
