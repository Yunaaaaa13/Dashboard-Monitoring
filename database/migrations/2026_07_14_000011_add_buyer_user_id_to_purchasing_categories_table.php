<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchasing_categories', function (Blueprint $table) {
            $table->foreignId('buyer_user_id')
                ->nullable()
                ->after('pic_buyer')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Hubungkan data kategori yang sudah ada dengan akun user purchasing.
        $buyerEmailsByCategory = [
            'PUR-01' => 'leader@kawai.co.id',
            'PUR-02' => 'staff@kawai.co.id',
            'PUR-03' => 'bambang@kawai.co.id',
            'PUR-04' => 'siska@kawai.co.id',
        ];

        foreach ($buyerEmailsByCategory as $categoryCode => $email) {
            $buyerId = DB::table('users')->where('email', $email)->value('id');

            if ($buyerId) {
                DB::table('purchasing_categories')
                    ->where('category_code', $categoryCode)
                    ->update(['buyer_user_id' => $buyerId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('purchasing_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('buyer_user_id');
        });
    }
};
