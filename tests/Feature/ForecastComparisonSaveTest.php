<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastComparisonSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_staff_can_save_forecast_and_outstanding_data(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $forecast = $this->actingAs($user)->postJson(route('purchasing.outstanding.forecasting.store'), [
            'part_number' => '954418', 'description' => 'WB-35 B', 'period_month' => '2026-08',
            'po_qty' => 15, 'production_qty' => 16, 'stock_qty' => 500, 'actual_qty' => 499,
        ]);
        $forecast->assertOk()->assertJsonPath('period', '2026-08');
        $this->assertDatabaseHas('forecastings', ['part_number' => '954418', 'period_month' => '2026-08']);

        $outstanding = $this->actingAs($user)->postJson(route('purchasing.outstanding.comparison-data.store'), [
            'part_number' => '954418', 'period_month' => '2026-08', 'outstanding_qty' => 98,
        ]);
        $outstanding->assertOk()->assertJsonPath('period', '2026-08');
        $this->assertDatabaseHas('outstanding_records', ['part_number' => '954418', 'period_month' => '2026-08']);
    }
}
