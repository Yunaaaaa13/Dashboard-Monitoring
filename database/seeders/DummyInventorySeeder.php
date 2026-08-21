<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\PurchasingOutstanding;
use Illuminate\Database\Seeder;

class DummyInventorySeeder extends Seeder
{
    /**
     * Run database seeds to populate realistic testing inventory data with surpluses, deficits, and optimal stock.
     */
    public function run(): void
    {
        // Fetch all existing part numbers & forecast items from Step 1
        $items = PurchasingOutstanding::all();

        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $idx => $item) {
            $partNo = strtoupper(trim($item->part_number ?: $item->drawing));
            if (!$partNo) continue;

            $planStock = (int) ($item->plan_stock > 0 ? $item->plan_stock : 100);
            $unitPrice = (float) ($item->price > 0 ? $item->price : 1.50);
            $currency  = strtoupper(trim($item->currency ?: 'USD'));
            $desc      = $item->description ?: 'Material Inventory Item';
            $supp      = $item->supplier_name ?: 'PT KAWAI SUPPLIER';
            $catId     = $item->category_id;
            $factory   = $item->factory_code ?: 'KIP 1';

            // 3 Realistic Scenarios:
            // Mod 0: Deficit (-15% to -40%)
            // Mod 1: Surplus (+15% to +45%)
            // Mod 2: Optimal / Match (Exact stock)
            $mod = $idx % 3;
            if ($mod === 0) {
                $varianceRatio = -rand(15, 40) / 100.0;
            } elseif ($mod === 1) {
                $varianceRatio = rand(15, 45) / 100.0;
            } else {
                $varianceRatio = 0.0;
            }

            $currentStock = max(10, (int) round($planStock * (1.0 + $varianceRatio)));
            $m0Stock      = $currentStock;

            $monthlyInv   = ['m0_inventory' => $m0Stock];
            $runningFc    = $planStock;
            $runningAct   = $m0Stock;

            for ($m = 1; $m <= 36; $m++) {
                $mPo   = (int) $item->getPoForMonth($m);
                $mDel  = (int) $item->getDeliveryForMonth($m);
                $mProd = (int) $item->getProdForMonth($m);

                $pIn  = max($mPo, $mDel);
                $pOut = $mProd;

                if ($pIn > 0 || $pOut > 0) {
                    $runningFc = max((int)round($planStock * 0.4), $runningFc + $pIn - $pOut);
                }

                // Physical stock tracks forecast stock with realistic ratio variance
                $noise = rand(-5, 10);
                $runningAct = max(5, (int) round($runningFc * (1.0 + $varianceRatio) + $noise));
                $monthlyInv["m{$m}_inventory"] = $runningAct;
            }

            // Min & Max Safety thresholds
            $minStock = (int) max(10, round($planStock * 0.3));
            $maxStock = (int) max(100, round($planStock * 2.0));

            // Status stock
            if ($currentStock < $minStock || $varianceRatio < -0.10) {
                $status = 'DEFICIT';
            } elseif ($currentStock > $maxStock || $varianceRatio > 0.10) {
                $status = 'OVERSTOCK';
            } else {
                $status = 'OPTIMAL';
            }

            $rackNum = sprintf('%02d', ($idx % 20) + 1);

            Inventory::updateOrCreate(
                ['part_number' => $partNo],
                array_merge([
                    'tanggal_inventory'  => date('Y-m-d'),
                    'drawing'            => $item->drawing ?: $partNo,
                    'description'        => $desc,
                    'supplier_name'      => $supp,
                    'category_id'        => $catId,
                    'factory_code'       => $factory,
                    'current_stock'      => $currentStock,
                    'min_stock'          => $minStock,
                    'max_stock'          => $maxStock,
                    'unit_measure'       => 'PCS',
                    'unit_price'         => $unitPrice,
                    'currency'           => $currency,
                    'warehouse_location' => "RAK-{$rackNum}",
                    'status'             => $status,
                    'user_id'            => auth()->id() ?: 1,
                ], $monthlyInv)
            );

            // Sync purchasing_outstandings m0..m36 inventory columns
            $updatePo = ['m0_inventory' => $m0Stock];
            for ($k = 1; $k <= 36; $k++) {
                $updatePo["m{$k}_inventory"] = $monthlyInv["m{$k}_inventory"];
            }
            $item->update($updatePo);
        }
    }
}
