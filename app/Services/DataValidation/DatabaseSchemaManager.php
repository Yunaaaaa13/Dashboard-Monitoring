<?php

namespace App\Services\DataValidation;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class DatabaseSchemaManager
{
    protected static bool $integrityEnsured = false;

    /**
     * Pastikan integritas seluruh skema tabel di aplikasi terjamin.
     */
    public static function ensureAllTablesIntegrity(bool $force = false): void
    {
        if (static::$integrityEnsured && !$force) {
            return;
        }
        static::$integrityEnsured = true;

        try {
            static::ensureMasterPosTable();
            static::ensurePurchasingLogsTable();
            static::ensureForecastingsTable();
            static::ensurePurchasingOutstandingsTable();
            static::ensureActualProductionsTable();
            static::ensureInventoriesTable();
            static::ensureActualsTable();
            static::ensureOutstandingsTable();
        } catch (\Throwable $e) {
            Log::warning('DatabaseSchemaManager::ensureAllTablesIntegrity caught exception: ' . $e->getMessage());
        }
    }

    /**
     * 1. Tabel master_pos (Step 2)
     */
    public static function ensureMasterPosTable(): void
    {
        if (!Schema::hasTable('master_pos')) return;

        $existing = Schema::getColumnListing('master_pos');
        $missing = [];

        foreach (['category_id', 'factory_code', 'delivery_category_code', 'price', 'currency', 'user_id', 'created_by'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('master_pos', function (Blueprint $table) use ($missing) {
                if (in_array('category_id', $missing, true)) {
                    $table->unsignedBigInteger('category_id')->nullable()->index();
                }
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('Plant 3')->index();
                }
                if (in_array('delivery_category_code', $missing, true)) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (in_array('price', $missing, true)) {
                    $table->decimal('price', 15, 4)->default(0);
                }
                if (in_array('currency', $missing, true)) {
                    $table->string('currency', 10)->default('USD');
                }
                if (in_array('user_id', $missing, true)) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                if (in_array('created_by', $missing, true)) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
            });

            if (class_exists(\App\Models\MasterPo::class)) {
                \App\Models\MasterPo::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 2. Tabel purchasing_logs (Step 3)
     */
    public static function ensurePurchasingLogsTable(): void
    {
        if (!Schema::hasTable('purchasing_logs')) return;

        $existing = Schema::getColumnListing('purchasing_logs');
        $missing = [];

        foreach (['purchasing_category_id', 'factory_code', 'delivery_category_code', 'price', 'currency', 'amount', 'production_qty', 'pending_order', 'user_id'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('purchasing_logs', function (Blueprint $table) use ($missing) {
                if (in_array('purchasing_category_id', $missing, true)) {
                    $table->unsignedBigInteger('purchasing_category_id')->nullable()->index();
                }
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('Plant 3')->index();
                }
                if (in_array('delivery_category_code', $missing, true)) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (in_array('price', $missing, true)) {
                    $table->decimal('price', 15, 4)->default(0);
                }
                if (in_array('currency', $missing, true)) {
                    $table->string('currency', 10)->default('USD');
                }
                if (in_array('amount', $missing, true)) {
                    $table->decimal('amount', 18, 4)->default(0);
                }
                if (in_array('production_qty', $missing, true)) {
                    $table->integer('production_qty')->default(0);
                }
                if (in_array('pending_order', $missing, true)) {
                    $table->integer('pending_order')->default(0);
                }
                if (in_array('user_id', $missing, true)) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
            });

            if (class_exists(\App\Models\PurchasingLog::class)) {
                \App\Models\PurchasingLog::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 3. Tabel forecastings
     */
    public static function ensureForecastingsTable(): void
    {
        if (!Schema::hasTable('forecastings')) return;

        $existing = Schema::getColumnListing('forecastings');
        $missing = [];

        foreach (['delivery_category_code', 'factory_code', 'user_id', 'supplier_name', 'price', 'currency'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('forecastings', function (Blueprint $table) use ($missing) {
                if (in_array('delivery_category_code', $missing, true)) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('Plant 3')->index();
                }
                if (in_array('user_id', $missing, true)) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                if (in_array('supplier_name', $missing, true)) {
                    $table->string('supplier_name')->nullable();
                }
                if (in_array('price', $missing, true)) {
                    $table->decimal('price', 15, 4)->default(0);
                }
                if (in_array('currency', $missing, true)) {
                    $table->string('currency', 10)->default('USD');
                }
            });

            if (class_exists(\App\Models\Forecasting::class)) {
                \App\Models\Forecasting::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 4. Tabel purchasing_outstandings
     */
    public static function ensurePurchasingOutstandingsTable(): void
    {
        if (!Schema::hasTable('purchasing_outstandings')) return;

        $existing = Schema::getColumnListing('purchasing_outstandings');
        $missing = [];

        foreach (['category_id', 'factory_code', 'delivery_category_code', 'price', 'currency', 'price_deviation_reason', 'amount', 'complete', 'status', 'workflow_stage', 'approval_notes', 'supplier_name', 'pic_buyer', 'eta_date', 'plan_stock', 'plan_outstand'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) use ($missing) {
                if (in_array('category_id', $missing, true)) {
                    $table->unsignedBigInteger('category_id')->nullable()->index();
                }
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('KIP 1')->index();
                }
                if (in_array('delivery_category_code', $missing, true)) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (in_array('price', $missing, true)) {
                    $table->decimal('price', 15, 4)->default(0);
                }
                if (in_array('currency', $missing, true)) {
                    $table->string('currency', 10)->default('USD');
                }
                if (in_array('price_deviation_reason', $missing, true)) {
                    $table->text('price_deviation_reason')->nullable();
                }
                if (in_array('amount', $missing, true)) {
                    $table->decimal('amount', 18, 4)->default(0);
                }
                if (in_array('complete', $missing, true)) {
                    $table->integer('complete')->default(0);
                }
                if (in_array('status', $missing, true)) {
                    $table->string('status', 50)->default('Pending');
                }
                if (in_array('workflow_stage', $missing, true)) {
                    $table->string('workflow_stage', 50)->default('waiting_manager');
                }
                if (in_array('approval_notes', $missing, true)) {
                    $table->text('approval_notes')->nullable();
                }
                if (in_array('supplier_name', $missing, true)) {
                    $table->string('supplier_name')->nullable();
                }
                if (in_array('pic_buyer', $missing, true)) {
                    $table->string('pic_buyer')->nullable();
                }
                if (in_array('eta_date', $missing, true)) {
                    $table->date('eta_date')->nullable();
                }
                if (in_array('plan_stock', $missing, true)) {
                    $table->integer('plan_stock')->default(0);
                }
                if (in_array('plan_outstand', $missing, true)) {
                    $table->integer('plan_outstand')->default(0);
                }
            });

            if (class_exists(\App\Models\PurchasingOutstanding::class)) {
                \App\Models\PurchasingOutstanding::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 5. Tabel actual_productions (Step 5)
     */
    public static function ensureActualProductionsTable(): void
    {
        if (!Schema::hasTable('actual_productions')) return;

        $existing = Schema::getColumnListing('actual_productions');
        $missing = [];

        foreach (['delivery_category_code', 'import_batch_id', 'excel_row_number', 'supplier_code', 'supplier_name', 'description', 'factory_code', 'user_id', 'qty', 'currency'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('actual_productions', function (Blueprint $table) use ($missing) {
                if (in_array('delivery_category_code', $missing, true)) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (in_array('import_batch_id', $missing, true)) {
                    $table->string('import_batch_id', 100)->nullable()->index();
                }
                if (in_array('excel_row_number', $missing, true)) {
                    $table->integer('excel_row_number')->nullable();
                }
                if (in_array('supplier_code', $missing, true)) {
                    $table->string('supplier_code', 50)->nullable();
                }
                if (in_array('supplier_name', $missing, true)) {
                    $table->string('supplier_name')->nullable();
                }
                if (in_array('description', $missing, true)) {
                    $table->string('description')->nullable();
                }
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('KIP 1')->index();
                }
                if (in_array('user_id', $missing, true)) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                if (in_array('qty', $missing, true)) {
                    $table->integer('qty')->default(0);
                }
                if (in_array('currency', $missing, true)) {
                    $table->string('currency', 10)->default('USD');
                }
            });

            if (class_exists(\App\Models\ActualProduction::class)) {
                \App\Models\ActualProduction::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 6. Tabel inventories (Step 6)
     */
    public static function ensureInventoriesTable(): void
    {
        if (!Schema::hasTable('inventories')) return;

        $existing = Schema::getColumnListing('inventories');
        $missing = [];

        foreach (['drawing', 'supplier_code', 'supplier_name', 'category_id', 'factory_code', 'current_stock', 'm0_inventory', 'unit_measure', 'unit_price', 'currency', 'warehouse_location', 'status', 'user_id', 'tanggal_inventory'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('inventories', function (Blueprint $table) use ($missing) {
                if (in_array('tanggal_inventory', $missing, true)) {
                    $table->date('tanggal_inventory')->nullable()->index();
                }
                if (in_array('drawing', $missing, true)) {
                    $table->string('drawing', 100)->nullable();
                }
                if (in_array('supplier_code', $missing, true)) {
                    $table->string('supplier_code', 50)->nullable();
                }
                if (in_array('supplier_name', $missing, true)) {
                    $table->string('supplier_name')->nullable();
                }
                if (in_array('category_id', $missing, true)) {
                    $table->unsignedBigInteger('category_id')->nullable();
                }
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('KIP1')->index();
                }
                if (in_array('current_stock', $missing, true)) {
                    $table->integer('current_stock')->default(0);
                }
                if (in_array('m0_inventory', $missing, true)) {
                    $table->integer('m0_inventory')->default(0);
                }
                if (in_array('unit_measure', $missing, true)) {
                    $table->string('unit_measure', 20)->default('PCS');
                }
                if (in_array('unit_price', $missing, true)) {
                    $table->decimal('unit_price', 15, 4)->default(0);
                }
                if (in_array('currency', $missing, true)) {
                    $table->string('currency', 10)->default('USD');
                }
                if (in_array('warehouse_location', $missing, true)) {
                    $table->string('warehouse_location')->nullable();
                }
                if (in_array('status', $missing, true)) {
                    $table->string('status', 50)->default('OPTIMAL');
                }
                if (in_array('user_id', $missing, true)) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
            });

            if (class_exists(\App\Models\Inventory::class)) {
                \App\Models\Inventory::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 7. Tabel actuals
     */
    public static function ensureActualsTable(): void
    {
        if (!Schema::hasTable('actuals')) return;

        $existing = Schema::getColumnListing('actuals');
        $missing = [];

        foreach (['factory_code', 'description', 'periode', 'period_month', 'actual_qty', 'actual_stock'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('actuals', function (Blueprint $table) use ($missing) {
                if (in_array('factory_code', $missing, true)) {
                    $table->string('factory_code', 50)->default('KIP 1');
                }
                if (in_array('description', $missing, true)) {
                    $table->string('description')->nullable();
                }
                if (in_array('periode', $missing, true)) {
                    $table->string('periode', 20)->nullable()->index();
                }
                if (in_array('period_month', $missing, true)) {
                    $table->string('period_month', 20)->nullable()->index();
                }
                if (in_array('actual_qty', $missing, true)) {
                    $table->integer('actual_qty')->default(0);
                }
                if (in_array('actual_stock', $missing, true)) {
                    $table->integer('actual_stock')->default(0);
                }
            });

            if (class_exists(\App\Models\Actual::class)) {
                \App\Models\Actual::clearPhysicalColumnsCache();
            }
        }
    }

    /**
     * 8. Tabel outstandings
     */
    public static function ensureOutstandingsTable(): void
    {
        if (!Schema::hasTable('outstandings')) return;

        $existing = Schema::getColumnListing('outstandings');
        $missing = [];

        foreach (['periode', 'period_month', 'outstanding_qty', 'description', 'po'] as $col) {
            if (!in_array($col, $existing, true)) {
                $missing[] = $col;
            }
        }

        if (!empty($missing)) {
            Schema::table('outstandings', function (Blueprint $table) use ($missing) {
                if (in_array('periode', $missing, true)) {
                    $table->string('periode', 20)->nullable()->index();
                }
                if (in_array('period_month', $missing, true)) {
                    $table->string('period_month', 20)->nullable()->index();
                }
                if (in_array('outstanding_qty', $missing, true)) {
                    $table->integer('outstanding_qty')->default(0);
                }
                if (in_array('description', $missing, true)) {
                    $table->string('description')->nullable();
                }
                if (in_array('po', $missing, true)) {
                    $table->string('po')->nullable();
                }
            });

            if (class_exists(\App\Models\Outstanding::class)) {
                \App\Models\Outstanding::clearPhysicalColumnsCache();
            }
        }
    }
}
