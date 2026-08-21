<?php

use App\Http\Controllers\ProductionController;
use Illuminate\Support\Facades\Route;

// Endpoint mesin harus dilindungi token sebelum dihubungkan ke EZRunner produksi.
Route::post('/ezrunner/sync', [ProductionController::class, 'ezrunnerSync'])->middleware('ezrunner.key');
