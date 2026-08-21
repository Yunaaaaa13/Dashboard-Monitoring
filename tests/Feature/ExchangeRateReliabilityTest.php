<?php

namespace Tests\Feature;

use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExchangeRateReliabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'exchange_rate_admin',
        ]);
    }

    public function test_monthly_chart_keeps_all_datasets_aligned_for_partial_actual_data(): void
    {
        TaxExchangeRate::create([
            'exch_year' => 2026, 'exch_month' => 1, 'week_code' => 1,
            'currency_code' => 2, 'tax_exchange_rate' => 16500,
        ]);
        TaxExchangeRate::create([
            'exch_year' => 2026, 'exch_month' => 3, 'week_code' => 1,
            'currency_code' => 2, 'tax_exchange_rate' => 17000,
        ]);
        TaxBudgetForecastRate::create([
            'exch_year' => 2026, 'exch_month' => 1, 'currency_code' => 2,
            'budget_rate' => 16000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('exchange-rate.index', [
            'year' => 2026, 'month' => 1, 'currency' => 2,
        ]));

        $response->assertOk()
            ->assertSee('const monthlyLabels = ["Januari","Februari","Maret"', false)
            ->assertSee('const monthlyValues = [16500,null,17000', false)
            ->assertSee('const budgetValues  = [16000,0,0', false);
    }

    public function test_analysis_chart_initialization_is_inside_a_script_element(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.analysis'));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '#<script>\s*// .*GLOBAL SLIDE TAB RESIZE HELPERS#s',
            $response->getContent()
        );
        $response->assertSee('window.switchFxChartMode = function(mode)', false);
    }

    public function test_edit_rejects_duplicate_period_and_invalid_date_range(): void
    {
        $first = TaxExchangeRate::create([
            'exch_year' => 2026, 'exch_month' => 5, 'week_code' => 1,
            'currency_code' => 2, 'tax_exchange_rate' => 16600,
        ]);
        $second = TaxExchangeRate::create([
            'exch_year' => 2026, 'exch_month' => 5, 'week_code' => 2,
            'currency_code' => 2, 'tax_exchange_rate' => 16700,
        ]);

        $duplicate = $this->actingAs($this->admin)->put(route('exchange-rate.update', $second), [
            'exch_year' => 2026,
            'exch_month' => 5,
            'week_code' => 1,
            'currency_code' => 2,
            'tax_exchange_rate' => 16800,
        ]);
        $duplicate->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('tax_exchange_rates', [
            'id' => $second->id,
            'week_code' => 2,
            'tax_exchange_rate' => 16700,
        ]);

        $invalidDate = $this->actingAs($this->admin)->post(route('exchange-rate.store'), [
            'exch_year' => 2026,
            'exch_month' => 6,
            'week_code' => 1,
            'currency_code' => 2,
            'tax_exchange_rate' => 16800,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-01',
        ]);
        $invalidDate->assertSessionHasErrors('end_date');
        $this->assertDatabaseMissing('tax_exchange_rates', ['exch_month' => 6]);
        $this->assertDatabaseHas('tax_exchange_rates', ['id' => $first->id]);
    }

    public function test_csv_import_upserts_in_batches_and_skips_invalid_rows(): void
    {
        $csv = implode("\n", [
            'Exch_Year,Exch_Month,Week_Code,Currency_Code,Tax_ExchangeRate,Start_Date,End_Date,Last_Update,Last_User,Register_Date',
            '2026,8,1,2,16800,20260801,20260807,20260801,Importer,20260801',
            '2026,8,6,2,16810,20260808,20260814,20260808,Importer,20260808',
            '2026,8,2,2,16850,20260808,20260814,20260808,Importer,20260808',
        ]);
        $file = UploadedFile::fake()->createWithContent('kurs.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('exchange-rate.import'), [
            'excel_file' => $file,
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseCount('tax_exchange_rates', 2);
        $this->assertDatabaseHas('tax_exchange_rates', [
            'exch_year' => 2026, 'exch_month' => 8, 'week_code' => 2,
            'tax_exchange_rate' => 16850,
        ]);
        $this->assertDatabaseMissing('tax_exchange_rates', [
            'exch_year' => 2026, 'exch_month' => 8, 'week_code' => 6,
        ]);
    }

    public function test_analysis_provides_comparison_monthly_insights(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.analysis'));

        $response->assertOk()
            ->assertViewHas('comparisonMonthlyInsights')
            ->assertSee('modalMonthlyInsight', false)
            ->assertSee('Interactive Financial Insight per Bulan', false)
            ->assertSee('window.openMonthlyInsightModal', false);
    }
}
