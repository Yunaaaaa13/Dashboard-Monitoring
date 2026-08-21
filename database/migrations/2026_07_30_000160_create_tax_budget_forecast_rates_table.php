<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_budget_forecast_rates', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('exch_year')->default(date('Y'));
            $table->tinyInteger('exch_month'); // 1-12
            $table->tinyInteger('currency_code')->default(2); // 2 = USD/IDR, 1 = JPY/IDR, 3 = EUR/IDR
            $table->unsignedBigInteger('budget_rate'); // Nilai kurs budget forecast (cth: 16500)
            $table->string('remarks', 255)->nullable();
            $table->date('last_update')->nullable();
            $table->string('last_user', 100)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->unique(['exch_year', 'exch_month', 'currency_code'], 'unique_budget_forecast_month');
            $table->index(['exch_year', 'exch_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_budget_forecast_rates');
    }
};
