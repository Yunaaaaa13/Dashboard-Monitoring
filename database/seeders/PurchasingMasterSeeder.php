<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchasingCategory;
use App\Models\PurchasingLog;
use App\Models\User;

use Illuminate\Support\Facades\Schema;

class PurchasingMasterSeeder extends Seeder
{
    /**
     * Run master data seeds untuk divisi Purchasing PT Kawai Indonesia (Tanpa data dummy log)
     */
    public function run(): void
    {
        // Bersihkan data log & kategori lama
        Schema::disableForeignKeyConstraints();
        PurchasingLog::truncate();
        PurchasingCategory::truncate();
        Schema::enableForeignKeyConstraints();

        // 4 Kategori Pengadaan Material Utama PT Kawai Indonesia
        $categories = [
            [
                'category_code' => 'PUR-01',
                'category_name' => 'Kayu Akustik & Soundboard Spruce',
                'pic_buyer' => 'Ahmad Faisal (Senior Procurement)',
                'buyer_email' => 'leader@kawai.co.id',
                'monthly_target_units' => 4500,
                'status' => 'Active',
            ],
            [
                'category_code' => 'PUR-02',
                'category_name' => 'Action Mechanism & Hammer Felt',
                'pic_buyer' => 'Diana Permata (Precision Buyer)',
                'buyer_email' => 'staff@kawai.co.id',
                'monthly_target_units' => 6000,
                'status' => 'Active',
            ],
            [
                'category_code' => 'PUR-03',
                'category_name' => 'Cor Frame Besi & Senar Baja (Cast Iron)',
                'pic_buyer' => 'Bambang Widjanarko (Metal Specialist)',
                'buyer_email' => 'bambang@kawai.co.id',
                'monthly_target_units' => 3800,
                'status' => 'Active',
            ],
            [
                'category_code' => 'PUR-04',
                'category_name' => 'Finishing Polyester Resin & Chemical',
                'pic_buyer' => 'Siska Wulandari (Chemical Buyer)',
                'buyer_email' => 'siska@kawai.co.id',
                'monthly_target_units' => 2500,
                'status' => 'Active',
            ],
        ];

        foreach ($categories as $data) {
            $data['buyer_user_id'] = User::where('email', $data['buyer_email'])->value('id');
            unset($data['buyer_email']);
            PurchasingCategory::create($data);
        }
    }
}
