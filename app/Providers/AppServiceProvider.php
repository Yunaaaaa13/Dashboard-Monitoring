<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        try {
            \App\Models\MasterPo::ensureSchemaIntegrity();
            \App\Models\PurchasingLog::ensureSchemaIntegrity();
        } catch (\Throwable $e) {
            // Ignore if DB connection not ready yet
        }

        View::composer(['purchasing.*', 'partials.*', 'production.*'], function ($view) {
            $registeredItemsMap = [];
            $registeredItems = [];

            try {
                if (Schema::hasTable('purchasing_outstandings')) {
                    \App\Models\PurchasingOutstanding::select('part_number', 'drawing', 'description')->get()->each(function($i) use (&$registeredItemsMap) {
                        $code = strtoupper(trim($i->part_number ?: $i->drawing));
                        if ($code && !isset($registeredItemsMap[$code])) {
                            $registeredItemsMap[$code] = $i->description ?: $code;
                        }
                    });
                }

                if (Schema::hasTable('forecastings')) {
                    \App\Models\Forecasting::select('part_number', 'description')->get()->each(function($i) use (&$registeredItemsMap) {
                        $code = strtoupper(trim($i->part_number));
                        if ($code && !isset($registeredItemsMap[$code])) {
                            $registeredItemsMap[$code] = $i->description ?: $code;
                        }
                    });
                }

                if (Schema::hasTable('purchasing_master_pos')) {
                    \App\Models\MasterPo::select('item_code', 'name')->get()->each(function($i) use (&$registeredItemsMap) {
                        $code = strtoupper(trim($i->item_code));
                        if ($code && !isset($registeredItemsMap[$code])) {
                            $registeredItemsMap[$code] = $i->name ?: $code;
                        }
                    });
                }

                foreach ($registeredItemsMap as $code => $name) {
                    $registeredItems[] = [
                        'item_code' => $code,
                        'name'      => $name,
                    ];
                }
            } catch (\Throwable $e) {
                // Fallback empty
            }

            $view->with('registeredItems', $registeredItems);
            $view->with('registeredItemsMapJson', json_encode($registeredItemsMap));
        });
    }
}
