<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\PurchasingOutstandingController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ForecastComparisonController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\OutstandingController;
use App\Http\Controllers\ActualController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\IntegratedImportController;
use App\Http\Controllers\DataTraceController;


// Autentikasi Role (Admin, Supervisor, Leader, Staff)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/demo/{identifier}', [AuthController::class, 'demoLogin'])->name('auth.demo');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Modul User Management & Hak Akses (Khusus Admin)
Route::get('/users', [UserController::class, 'index'])->middleware(['auth', 'role:admin'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->middleware(['auth', 'role:admin'])->name('users.store');
Route::put('/users/{id}', [UserController::class, 'update'])->middleware(['auth', 'role:admin'])->name('users.update');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware(['auth', 'role:admin'])->name('users.destroy');
Route::get('/users/monitoring', [UserController::class, 'monitoring'])->middleware('auth')->name('users.monitoring');
Route::get('/users/monitoring/{id}/dashboard', [UserController::class, 'inspectUserDashboard'])->middleware('auth')->name('users.inspect');
Route::post('/users/{id}/toggle-monitoring-access', [UserController::class, 'toggleMonitoringAccess'])->middleware(['auth', 'role:admin'])->name('users.toggle-monitoring');
Route::post('/users/{id}/note', [UserController::class, 'updateNote'])->middleware(['auth', 'role:admin'])->name('users.update-note');

// Dashboard Utama Monitoring Purchasing & Pengadaan Material PT Kawai Indonesia
Route::get('/', [PurchasingController::class, 'index'])->name('dashboard.overview');

// Komparasi Forecasting dan Outstanding berdasarkan Part Number + Periode
Route::post('/purchasing/outstanding/forecasting', [ForecastController::class, 'store'])->middleware('auth')->name('purchasing.outstanding.forecasting.store');
Route::post('/purchasing/outstanding/comparison-data', [OutstandingController::class, 'store'])->middleware('auth')->name('purchasing.outstanding.comparison-data.store');
Route::post('/purchasing/outstanding/actual', [ActualController::class, 'store'])->middleware('auth')->name('purchasing.outstanding.actual.store');
Route::post('/forecast/store', [ForecastController::class, 'store'])->middleware('auth')->name('forecast.store');
Route::post('/outstanding/store-comparison', [OutstandingController::class, 'store'])->middleware('auth')->name('outstanding-comparison.store');
Route::post('/actual/store', [ActualController::class, 'store'])->middleware('auth')->name('actual.store');
Route::delete('/purchasing/outstanding/comparison/delete', [PurchasingOutstandingController::class, 'destroyComparison'])->middleware('auth')->name('purchasing.outstanding.comparison.delete');
Route::delete('/purchasing/outstanding/target/delete', [OutstandingController::class, 'destroy'])->middleware('auth')->name('purchasing.outstanding.target.delete');
Route::delete('/purchasing/outstanding/actual/delete', [ActualController::class, 'destroy'])->middleware('auth')->name('purchasing.outstanding.actual.delete');


// Halaman History & Audit (Hasil Input Incoming & Outstanding PO yang Bisa Di-edit / Di-hapus)
Route::get('/purchasing/history', [HistoryController::class, 'index'])->middleware('auth')->name('purchasing.history');
Route::get('/purchasing/history/export', [HistoryController::class, 'export'])->middleware('auth')->name('purchasing.history.export');
Route::put('/purchasing/history/input/{id}', [HistoryController::class, 'updateInputLog'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.history.input.update');
Route::post('/purchasing/history/input/{id}/approve', [HistoryController::class, 'approveInputLog'])->middleware(['auth', 'role:leader,supervisor'])->name('purchasing.history.input.approve');
Route::post('/purchasing/history/input/{id}/reject', [HistoryController::class, 'rejectInputLog'])->middleware(['auth', 'role:leader,supervisor'])->name('purchasing.history.input.reject');
Route::delete('/purchasing/history/input/{id}', [HistoryController::class, 'destroyInputLog'])->middleware(['auth', 'role:supervisor'])->name('purchasing.history.input.destroy');
Route::put('/purchasing/history/outstanding/{id}', [HistoryController::class, 'updateOutstanding'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.history.outstanding.update');
Route::delete('/purchasing/history/outstanding/{id}', [HistoryController::class, 'destroyOutstanding'])->middleware(['auth', 'role:supervisor'])->name('purchasing.history.outstanding.destroy');

// Monitoring Outstanding Order (PO Material & Komponen PT Kawai Indonesia)
Route::get('/purchasing/outstanding/comparison-json', [PurchasingOutstandingController::class, 'comparisonJson'])->name('purchasing.outstanding.comparison-json');
// Step 1: Outstanding/Forecast
Route::get('/purchasing/outstanding', [PurchasingOutstandingController::class, 'index'])->name('purchasing.outstanding');
Route::post('/purchasing/outstanding', [PurchasingOutstandingController::class, 'store'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.outstanding.store');
Route::put('/purchasing/outstanding/{id}', [PurchasingOutstandingController::class, 'update'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.outstanding.update');
Route::delete('/purchasing/outstanding/{id}', [PurchasingOutstandingController::class, 'destroy'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.outstanding.destroy');
Route::post('/purchasing/outstanding/destroy-bulk', [PurchasingOutstandingController::class, 'destroyBulk'])->middleware('auth')->name('purchasing.outstanding.destroy-bulk');
Route::post('/purchasing/outstanding/destroy-all', [PurchasingOutstandingController::class, 'destroyAll'])->middleware('auth')->name('purchasing.outstanding.destroy-all');
Route::post('/purchasing/outstanding/seed', [PurchasingOutstandingController::class, 'seedDefault'])->middleware(['auth', 'role:supervisor'])->name('purchasing.outstanding.seed');
Route::post('/purchasing/outstanding/months', [PurchasingOutstandingController::class, 'updateMonths'])->name('purchasing.outstanding.months');
Route::get('/purchasing/outstanding/template', [ForecastController::class, 'downloadTemplate'])->middleware('auth')->name('purchasing.template');
Route::get('/purchasing/outstanding/export', [HistoryController::class, 'export'])->middleware('auth')->name('purchasing.export');
Route::get('/purchasing/outstanding/import', function() {
    return redirect()->route('purchasing.outstanding');
});
Route::post('/purchasing/outstanding/import', [PurchasingOutstandingController::class, 'importExcel'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.outstanding.import');
Route::post('/purchasing/outstanding/{id}/workflow', [PurchasingOutstandingController::class, 'updateWorkflow'])->middleware('auth')->name('purchasing.outstanding.workflow');
 
// Input Real Data Pembelian Bulanan (Buyer Entry - Step 3)
Route::get('/purchasing/input', [PurchasingController::class, 'createLog'])->middleware('auth')->name('purchasing.input');
Route::post('/purchasing/input', [PurchasingController::class, 'storeLog'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.store');
Route::post('/purchasing/log/{id}/verify', [HistoryController::class, 'approveInputLog'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.log.verify');
Route::delete('/purchasing/log/{id}', [PurchasingController::class, 'destroyLog'])->middleware(['auth', 'role:supervisor'])->name('purchasing.log.destroy');
Route::post('/purchasing/log/destroy-bulk', [PurchasingController::class, 'destroyLogBulk'])->middleware('auth')->name('purchasing.log.destroy-bulk');
Route::post('/purchasing/log/destroy-all', [PurchasingController::class, 'destroyLogAll'])->middleware('auth')->name('purchasing.log.destroy-all');
Route::put('/purchasing/log/{id}', [PurchasingController::class, 'updateLog'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.log.update');
Route::get('/purchasing/input/template', [IntegratedImportController::class, 'downloadIncomingTemplate'])->middleware('auth')->name('purchasing.input.template');
Route::post('/purchasing/input/import', [IntegratedImportController::class, 'importIncoming'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.input.import');
Route::post('/purchasing/input/bulk', [PurchasingController::class, 'storeLogBulk'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.input.bulk');
Route::get('/purchasing/outstanding-po', [AnalysisController::class, 'outstandingPo'])->middleware('auth')->name('purchasing.outstanding-po');

// Modul Import Terpadu PO & Incoming (1 Excel -> Master PO + Incoming)
Route::post('/purchasing/integrated-import/preview', [IntegratedImportController::class, 'preview'])->middleware('auth')->name('purchasing.integrated-import.preview');
Route::post('/purchasing/integrated-import/execute', [IntegratedImportController::class, 'execute'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.integrated-import.execute');
Route::get('/purchasing/integrated-import/template', [IntegratedImportController::class, 'downloadTemplate'])->middleware('auth')->name('purchasing.integrated-import.template');

Route::post('/integrated-import/smart-preview', [IntegratedImportController::class, 'smartPreview'])->name('integrated-import.smart-preview');
Route::post('/integrated-import/smart-import', [IntegratedImportController::class, 'smartImport'])->name('integrated-import.smart-import');

// Data Traceability & Integration Health Check
Route::get('/system/data-health', [DataTraceController::class, 'index'])->middleware('auth')->name('system.data-health');
Route::get('/api/system/data-health', [DataTraceController::class, 'apiHealth'])->middleware('auth')->name('api.system.data-health');

// Dashboard Master PO (Terpisah & Independen - Step 2)
Route::get('/purchasing/master-po', [PurchasingController::class, 'masterPoIndex'])->middleware('auth')->name('purchasing.master-po');
Route::post('/purchasing/master-po/store', [PurchasingController::class, 'storeMasterPo'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.master-po.store');
Route::post('/purchasing/master-po/bulk', [PurchasingController::class, 'storeMasterPoBulk'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.master.bulk');
Route::get('/purchasing/master-po/template', [IntegratedImportController::class, 'downloadMasterPoTemplate'])->middleware('auth')->name('purchasing.master-po.template');
Route::post('/purchasing/master-po/import', [IntegratedImportController::class, 'importMasterPo'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.master-po.import');
Route::put('/purchasing/master-po/{id}', [PurchasingController::class, 'updateMasterPo'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.master-po.update');
Route::delete('/purchasing/master-po/{id}', [PurchasingController::class, 'destroyMasterPo'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.master-po.destroy');
Route::post('/purchasing/master-po/destroy-bulk', [PurchasingController::class, 'destroyMasterPoBulk'])->middleware('auth')->name('purchasing.master-po.destroy-bulk');
Route::post('/purchasing/master-po/destroy-all', [PurchasingController::class, 'destroyMasterPoAll'])->middleware('auth')->name('purchasing.master-po.destroy-all');
 
// Manajemen Master Kategori Material PT Kawai Indonesia
Route::get('/purchasing/categories', [PurchasingController::class, 'categories'])->middleware('auth')->name('purchasing.categories');
Route::post('/purchasing/categories', [PurchasingController::class, 'storeCategory'])->middleware('auth')->name('purchasing.categories.store');
Route::put('/purchasing/categories/{id}', [PurchasingController::class, 'updateCategory'])->middleware('auth')->name('purchasing.categories.update');
Route::delete('/purchasing/categories/{id}', [PurchasingController::class, 'destroyCategory'])->middleware('auth')->name('purchasing.categories.destroy');
Route::post('/purchasing/categories/destroy-bulk', [PurchasingController::class, 'destroyCategoryBulk'])->middleware('auth')->name('purchasing.categories.destroy-bulk');
 
// Reset / Bersihkan log testing agar sistem 100% siap untuk data asli
Route::post('/purchasing/clear-dummy', [PurchasingController::class, 'clearLogs'])->middleware(['auth', 'role:supervisor'])->name('purchasing.clear_dummy');
 
// Monitoring produksi
Route::get('/production/input', [ProductionController::class, 'createLog'])->middleware('auth')->name('production.input');
Route::post('/production/input', [ProductionController::class, 'storeLog'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('production.store');
Route::delete('/production/log/{id}', [ProductionController::class, 'destroyLog'])->middleware(['auth', 'role:supervisor'])->name('production.log.destroy');
Route::get('/production/lines', [ProductionController::class, 'lines'])->middleware('auth')->name('production.lines');
Route::post('/production/lines', [ProductionController::class, 'storeLine'])->middleware(['auth', 'role:supervisor'])->name('production.lines.store');
Route::put('/production/lines/{id}', [ProductionController::class, 'updateLine'])->middleware(['auth', 'role:supervisor'])->name('production.lines.update');
Route::post('/production/clear-dummy', [ProductionController::class, 'clearLogs'])->middleware(['auth', 'role:supervisor'])->name('production.clear_dummy');
 
// ─── Master Data Purchasing (Terpisah) ───────────────────────────────────────
// Master Forecast
Route::get('/purchasing/master/forecast', [ForecastController::class, 'masterIndex'])->middleware('auth')->name('purchasing.master.forecast');
Route::post('/purchasing/master/forecast', [ForecastController::class, 'store'])->middleware('auth')->name('purchasing.master.forecast.store');
Route::put('/purchasing/master/forecast/{id}', [ForecastController::class, 'update'])->middleware('auth')->name('purchasing.master.forecast.update');
Route::delete('/purchasing/master/forecast/{id}', [ForecastController::class, 'destroy'])->middleware(['auth', 'role:supervisor,leader'])->name('purchasing.master.forecast.destroy');
Route::post('/purchasing/master/forecast/destroy-bulk', [ForecastController::class, 'destroyBulk'])->middleware('auth')->name('purchasing.master.forecast.destroy-bulk');

// Master Actual
Route::get('/purchasing/master/actual', [ActualController::class, 'masterIndex'])->middleware('auth')->name('purchasing.master.actual');
Route::post('/purchasing/master/actual', [ActualController::class, 'store'])->middleware('auth')->name('purchasing.master.actual.store');
Route::put('/purchasing/master/actual/{id}', [ActualController::class, 'update'])->middleware('auth')->name('purchasing.master.actual.update');
Route::delete('/purchasing/master/actual/{id}', [ActualController::class, 'destroyById'])->middleware(['auth', 'role:supervisor,leader'])->name('purchasing.master.actual.destroy');
Route::post('/purchasing/master/actual/destroy-bulk', [ActualController::class, 'destroyBulk'])->middleware('auth')->name('purchasing.master.actual.destroy-bulk');

// Step 5: Aktual Produksi
Route::get('/purchasing/actual-production', [\App\Http\Controllers\ActualProductionController::class, 'index'])->middleware('auth')->name('purchasing.actual-production');
Route::post('/purchasing/actual-production/store', [\App\Http\Controllers\ActualProductionController::class, 'store'])->middleware('auth')->name('purchasing.actual-production.store');
Route::put('/purchasing/actual-production/{id}', [\App\Http\Controllers\ActualProductionController::class, 'update'])->middleware('auth')->name('purchasing.actual-production.update');
Route::delete('/purchasing/actual-production/{id}', [\App\Http\Controllers\ActualProductionController::class, 'destroy'])->middleware('auth')->name('purchasing.actual-production.destroy');
Route::post('/purchasing/actual-production/destroy-bulk', [\App\Http\Controllers\ActualProductionController::class, 'destroyBulk'])->middleware('auth')->name('purchasing.actual-production.destroy-bulk');
Route::get('/purchasing/actual-production/template/download', [\App\Http\Controllers\ActualProductionController::class, 'downloadTemplate'])->middleware('auth')->name('purchasing.actual-production.template');
Route::post('/purchasing/actual-production/import', [\App\Http\Controllers\ActualProductionController::class, 'import'])->middleware('auth')->name('purchasing.actual-production.import');
Route::post('/purchasing/actual-production/destroy-all', [\App\Http\Controllers\ActualProductionController::class, 'destroyAll'])->middleware('auth')->name('purchasing.actual-production.destroy-all');

// Step 6: Aktual Inventory
Route::get('/purchasing/actual-inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->middleware('auth')->name('purchasing.actual-inventory');
Route::get('/purchasing/actual-inventory/template', [\App\Http\Controllers\InventoryController::class, 'downloadTemplate'])->middleware('auth')->name('purchasing.actual-inventory.template');
Route::post('/purchasing/actual-inventory/import', [\App\Http\Controllers\InventoryController::class, 'importExcel'])->middleware(['auth', 'role:staff,leader,supervisor'])->name('purchasing.actual-inventory.import');
Route::post('/purchasing/actual-inventory/store', [\App\Http\Controllers\InventoryController::class, 'store'])->middleware('auth')->name('purchasing.actual-inventory.store');
Route::put('/purchasing/actual-inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'update'])->middleware('auth')->name('purchasing.actual-inventory.update');
Route::delete('/purchasing/actual-inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'destroy'])->middleware('auth')->name('purchasing.actual-inventory.destroy');
Route::post('/purchasing/actual-inventory/destroy-bulk', [\App\Http\Controllers\InventoryController::class, 'destroyBulk'])->middleware('auth')->name('purchasing.actual-inventory.destroy-bulk');
Route::post('/purchasing/actual-inventory/destroy-all', [\App\Http\Controllers\InventoryController::class, 'destroyAll'])->middleware('auth')->name('purchasing.actual-inventory.destroy-all');
 
// Master Outstanding
Route::get('/purchasing/master/outstanding', [OutstandingController::class, 'masterIndex'])->middleware('auth')->name('purchasing.master.outstanding');
Route::post('/purchasing/master/outstanding', [OutstandingController::class, 'store'])->middleware('auth')->name('purchasing.master.outstanding.store');
Route::put('/purchasing/master/outstanding/{id}', [OutstandingController::class, 'update'])->middleware('auth')->name('purchasing.master.outstanding.update');
Route::delete('/purchasing/master/outstanding/{id}', [OutstandingController::class, 'destroy'])->middleware(['auth', 'role:supervisor,leader'])->name('purchasing.master.outstanding.destroy');
Route::post('/purchasing/master/outstanding/destroy-bulk', [OutstandingController::class, 'destroyBulk'])->middleware('auth')->name('purchasing.master.outstanding.destroy-bulk');

// ─── Modul Analisis & Komparasi ──────────────────────────────────────────────
Route::get('/purchasing/analysis', [AnalysisController::class, 'index'])->middleware('auth')->name('purchasing.analysis');
Route::get('/purchasing/analysis/data', [AnalysisController::class, 'data'])->middleware('auth')->name('purchasing.analysis.data');
Route::post('/purchasing/analysis/inventory-reason', [AnalysisController::class, 'storeInventoryReason'])->middleware('auth')->name('purchasing.analysis.inventory-reason');

// Modul Khusus Comparison Outstanding vs Actual (Prompt 3 & 5)
Route::get('/purchasing/comparison', [AnalysisController::class, 'comparison'])->middleware('auth')->name('purchasing.comparison');
Route::get('/api/v1/purchasing/comparison/outstanding-vs-actual', [AnalysisController::class, 'apiComparison'])->middleware('auth')->name('api.purchasing.comparison');

// ─── Modul Tax Exchange Rate (Kurs Pajak Mingguan & Bulanan) ──────────────────
Route::get('/exchange-rate', [ExchangeRateController::class, 'index'])->middleware('auth')->name('exchange-rate.index');
Route::post('/exchange-rate', [ExchangeRateController::class, 'store'])->middleware('auth')->name('exchange-rate.store');
Route::put('/exchange-rate/{id}', [ExchangeRateController::class, 'update'])->middleware('auth')->name('exchange-rate.update');
Route::delete('/exchange-rate/{id}', [ExchangeRateController::class, 'destroy'])->middleware('auth')->name('exchange-rate.destroy');
Route::post('/exchange-rate/destroy-bulk', [ExchangeRateController::class, 'destroyBulk'])->middleware('auth')->name('exchange-rate.destroy-bulk');
Route::post('/exchange-rate/import', [ExchangeRateController::class, 'import'])->middleware('auth')->name('exchange-rate.import');
Route::get('/exchange-rate/template', [ExchangeRateController::class, 'downloadTemplate'])->middleware('auth')->name('exchange-rate.template');
Route::post('/exchange-rate/budget-forecast', [ExchangeRateController::class, 'storeBudgetForecast'])->middleware('auth')->name('exchange-rate.budget-forecast.store');
Route::post('/exchange-rate/budget-forecast/bulk', [ExchangeRateController::class, 'updateBudgetForecastBulk'])->middleware('auth')->name('exchange-rate.budget-forecast.bulk');


