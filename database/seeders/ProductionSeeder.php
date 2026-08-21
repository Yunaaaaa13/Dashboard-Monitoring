<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionLine;
use App\Models\ProductionLog;
use Carbon\Carbon;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 8 Line Produksi Utama PT Kawai Indonesia
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

        // Total daily target = 1500 + 2000 + 2200 + 1600 + 1800 + 1400 + 1300 + 1200 = 13000 pcs
        $createdLines = [];
        foreach ($linesData as $data) {
            $createdLines[] = ProductionLine::create($data);
        }

        // Simulasi log jam per jam hari ini (08:00 - 16:00) dari EZRunner
        // Total target harian = 13,000 pcs
        // Total aktual harian = 12,450 pcs (Achievement = 95.77%)
        $hours = [
            '08:00' => ['target_share' => 0.11, 'actual_share' => 0.112],
            '09:00' => ['target_share' => 0.13, 'actual_share' => 0.128],
            '10:00' => ['target_share' => 0.13, 'actual_share' => 0.125],
            '11:00' => ['target_share' => 0.12, 'actual_share' => 0.115],
            '13:00' => ['target_share' => 0.13, 'actual_share' => 0.124],
            '14:00' => ['target_share' => 0.13, 'actual_share' => 0.126],
            '15:00' => ['target_share' => 0.13, 'actual_share' => 0.120],
            '16:00' => ['target_share' => 0.12, 'actual_share' => 0.150], // Genap hingga total
        ];

        $today = Carbon::today();

        // Distribusi proporsional per line agar tepat target 13000 dan actual 12450
        $lineWeights = [
            0 => ['target' => 1500, 'actual' => 1440],
            1 => ['target' => 2000, 'actual' => 1920],
            2 => ['target' => 2200, 'actual' => 2110],
            3 => ['target' => 1600, 'actual' => 1530],
            4 => ['target' => 1800, 'actual' => 1720],
            5 => ['target' => 1400, 'actual' => 1340],
            6 => ['target' => 1300, 'actual' => 1240],
            7 => ['target' => 1200, 'actual' => 1150],
        ];

        $hourKeys = array_keys($hours);
        $numHours = count($hourKeys);

        foreach ($createdLines as $idx => $line) {
            $lineTargetTotal = $lineWeights[$idx]['target'];
            $lineActualTotal = $lineWeights[$idx]['actual'];

            $accumulatedTarget = 0;
            $accumulatedActual = 0;

            foreach ($hourKeys as $hIndex => $hourStr) {
                if ($hIndex === $numHours - 1) {
                    $targetOutput = $lineTargetTotal - $accumulatedTarget;
                    $actualOutput = $lineActualTotal - $accumulatedActual;
                } else {
                    $targetOutput = (int) round($lineTargetTotal * $hours[$hourStr]['target_share']);
                    $actualOutput = (int) round($lineActualTotal * $hours[$hourStr]['actual_share']);
                    $accumulatedTarget += $targetOutput;
                    $accumulatedActual += $actualOutput;
                }

                $defect = rand(0, 3);

                ProductionLog::create([
                    'production_line_id' => $line->id,
                    'ezrunner_batch_id' => 'EZR-' . $today->format('Ymd') . '-' . $line->line_code . '-H' . ($hIndex + 1),
                    'log_time' => Carbon::parse($today->format('Y-m-d') . ' ' . $hourStr . ':00'),
                    'target_output' => $targetOutput,
                    'actual_output' => $actualOutput,
                    'defect_count' => $defect,
                    'status_note' => $actualOutput >= ($targetOutput * 0.95) ? 'Normal' : 'Minor Delay',
                ]);
            }
        }
    }
}
