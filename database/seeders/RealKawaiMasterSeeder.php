<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionLine;
use App\Models\ProductionLog;

class RealKawaiMasterSeeder extends Seeder
{
    /**
     * Run master data seeds HANYA untuk Line Produksi nyata PT Kawai Indonesia (tanpa dummy log)
     */
    public function run(): void
    {
        // Bersihkan data log & line lama
        ProductionLog::truncate();
        ProductionLine::truncate();

        // Daftar nyata Line Perakitan Manufaktur PT Kawai Indonesia (KIIC Karawang)
        $linesData = [
            [
                'line_code' => 'LINE-01',
                'line_name' => 'Grand Piano Assembly Line A',
                'product_category' => 'Grand Piano (GL & GX Series)',
                'supervisor' => 'Budi Santoso',
                'daily_target_capacity' => 1500,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-02',
                'line_name' => 'Upright Piano Assembly Line B',
                'product_category' => 'Upright Piano (K Series)',
                'supervisor' => 'Ahmad Hidayat',
                'daily_target_capacity' => 2000,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-03',
                'line_name' => 'Action & Keyboard Mechanism',
                'product_category' => 'Precision Action Components',
                'supervisor' => 'Siti Nurhaliza',
                'daily_target_capacity' => 2200,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-04',
                'line_name' => 'Acoustic Soundboard & Rib Fitting',
                'product_category' => 'Acoustic Assemblies',
                'supervisor' => 'Hendra Gunawan',
                'daily_target_capacity' => 1600,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-05',
                'line_name' => 'CNC Precision Wood Processing',
                'product_category' => 'Wood & Cabinet Parts',
                'supervisor' => 'Eko Prasetyo',
                'daily_target_capacity' => 1800,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-06',
                'line_name' => 'Iron Frame & Bridge Pin Installation',
                'product_category' => 'Frame & Stringing Unit',
                'supervisor' => 'Doni Kusuma',
                'daily_target_capacity' => 1400,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-07',
                'line_name' => 'Polyester Finishing & Buffing',
                'product_category' => 'Surface & Coating Finish',
                'supervisor' => 'Rina Wati',
                'daily_target_capacity' => 1300,
                'status' => 'Running',
            ],
            [
                'line_code' => 'LINE-08',
                'line_name' => 'Final Acoustic Quality & Tuning',
                'product_category' => 'Quality Control Inspection',
                'supervisor' => 'Rudi Kurniawan',
                'daily_target_capacity' => 1200,
                'status' => 'Running',
            ],
        ];

        foreach ($linesData as $data) {
            ProductionLine::create($data);
        }
    }
}
