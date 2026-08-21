<?php

namespace Tests\Unit;

use App\Services\PurchasingCalculationService;
use PHPUnit\Framework\TestCase;

class PurchasingCalculationServiceTest extends TestCase
{
    /**
     * Test Rumus 1: Outstanding PO & Over Delivery
     */
    public function test_calculate_outstanding_po(): void
    {
        // Kasus 1: Belum ada pengiriman (PO = 100, Rec = 0)
        $res1 = PurchasingCalculationService::calculateOutstanding(100, 0);
        $this->assertEquals(100, $res1['outstanding']);
        $this->assertEquals(0, $res1['over_delivery']);
        $this->assertEquals(0.0, $res1['fulfillment_pct']);
        $this->assertFalse($res1['is_complete']);

        // Kasus 2: Sebagian terkirim (PO = 100, Rec = 60)
        $res2 = PurchasingCalculationService::calculateOutstanding(100, 60);
        $this->assertEquals(40, $res2['outstanding']);
        $this->assertEquals(0, $res2['over_delivery']);
        $this->assertEquals(60.0, $res2['fulfillment_pct']);
        $this->assertFalse($res2['is_complete']);

        // Kasus 3: Terkirim sempurna 100% (PO = 100, Rec = 100)
        $res3 = PurchasingCalculationService::calculateOutstanding(100, 100);
        $this->assertEquals(0, $res3['outstanding']);
        $this->assertEquals(0, $res3['over_delivery']);
        $this->assertEquals(100.0, $res3['fulfillment_pct']);
        $this->assertTrue($res3['is_complete']);

        // Kasus 4: Over Delivery (PO = 100, Rec = 120) -> Outstanding tidak boleh negatif
        $res4 = PurchasingCalculationService::calculateOutstanding(100, 120);
        $this->assertEquals(0, $res4['outstanding']);
        $this->assertEquals(20, $res4['over_delivery']);
        $this->assertEquals(100.0, $res4['fulfillment_pct']);
        $this->assertTrue($res4['is_complete']);
    }

    /**
     * Test Rumus 2: Roll-Forward Stock Balance
     */
    public function test_calculate_roll_forward_stock(): void
    {
        // Stock_i = Stock_{i-1} + PO_i - PROD_i
        // Juni: Stock = 50. Juli: PO = 30, PROD = 20 -> Stock = 60
        $stockJuli = PurchasingCalculationService::calculateRollForwardStock(50, 30, 20);
        $this->assertEquals(60, $stockJuli);

        // Agustus: PO = 40, PROD = 70 -> Stock = 60 + 40 - 70 = 30
        $stockAgustus = PurchasingCalculationService::calculateRollForwardStock($stockJuli, 40, 70);
        $this->assertEquals(30, $stockAgustus);
    }

    /**
     * Test Rumus 3: Live Ratio % and Threshold Classification
     */
    public function test_calculate_live_ratio(): void
    {
        // Kasus Kritis (< 100%): Stock = 40, Demand M+1 = 50 -> Ratio = 80%
        $res1 = PurchasingCalculationService::calculateLiveRatio(40, 50);
        $this->assertEquals(80.0, $res1['ratio_pct']);
        $this->assertEquals('CRITICAL', $res1['status']);

        // Kasus Ideal (100% - 200%): Stock = 60, Demand M+1 = 40 -> Ratio = 150%
        $res2 = PurchasingCalculationService::calculateLiveRatio(60, 40);
        $this->assertEquals(150.0, $res2['ratio_pct']);
        $this->assertEquals('IDEAL', $res2['status']);

        // Kasus Overstock (> 200%): Stock = 100, Demand M+1 = 30 -> Ratio = 333.3%
        $res3 = PurchasingCalculationService::calculateLiveRatio(100, 30);
        $this->assertEquals(333.3, $res3['ratio_pct']);
        $this->assertEquals('OVERSTOCK', $res3['status']);

        // Kasus No Demand (PROD M+1 = 0)
        $res4 = PurchasingCalculationService::calculateLiveRatio(50, 0);
        $this->assertNull($res4['ratio_pct']);
        $this->assertEquals('NO_DEMAND', $res4['status']);
    }

    /**
     * Test Rumus 4 & 5: Forecast & Actual Currency Conversion
     */
    public function test_calculate_currency_amounts(): void
    {
        // Forecast: 100 unit * $15.50 * Rp 16.000 = Rp 24.800.000
        $fc = PurchasingCalculationService::calculateForecastAmount(100, 15.50, 16000);
        $this->assertEquals(1550.0, $fc['amount_usd']);
        $this->assertEquals(24800000.0, $fc['amount_idr']);

        // Actual: 80 unit * $15.50 * Rp 16.250 = Rp 20.150.000
        $act = PurchasingCalculationService::calculateActualAmount(80, 15.50, 16250);
        $this->assertEquals(1240.0, $act['amount_usd']);
        $this->assertEquals(20150000.0, $act['amount_idr']);
    }

    /**
     * Test Rumus 6: Potential Supply & Coverage Ratio (Step 6)
     */
    public function test_calculate_potential_supply_and_gap(): void
    {
        // Kasus Defisit: Stock = 20, PO = 30 (Pot. Supply = 50), Demand = 80 -> Gap = -30
        $resDeficit = PurchasingCalculationService::calculatePotentialSupplyAndGap(20, 30, 80);
        $this->assertEquals(50, $resDeficit['potential_supply']);
        $this->assertEquals(-30, $resDeficit['net_gap']);
        $this->assertEquals(62.5, $resDeficit['coverage_pct']);
        $this->assertEquals('CRITICAL_DEFICIT', $resDeficit['status']);

        // Kasus Surplus: Stock = 100, PO = 20, Demand = 80 -> Gap = +40
        $resSurplus = PurchasingCalculationService::calculatePotentialSupplyAndGap(100, 20, 80);
        $this->assertEquals(120, $resSurplus['potential_supply']);
        $this->assertEquals(40, $resSurplus['net_gap']);
        $this->assertEquals(150.0, $resSurplus['coverage_pct']);
        $this->assertEquals('SURPLUS', $resSurplus['status']);
    }
}
