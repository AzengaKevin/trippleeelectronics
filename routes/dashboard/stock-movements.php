<?php

use App\Http\Controllers\Backoffice\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StockMovementController::class, 'index'])->middleware(['can:browse-stock-movements'])->name('index');
Route::post('/', [StockMovementController::class, 'store'])->middleware(['can:create-stock-movements'])->name('store');
Route::get('/create', [StockMovementController::class, 'create'])->middleware(['can:create-stock-movements'])->name('create');
Route::get('/export', [StockMovementController::class, 'export'])->middleware(['can:export-stock-movements'])->name('export');
Route::get('/import', [StockMovementController::class, 'import'])->middleware(['can:import-stock-movements'])->name('import');
Route::post('/transfer', [StockMovementController::class, 'transfer'])->middleware(['can:transfer-stock'])->name('transfer');
Route::post('/import', [StockMovementController::class, 'processImport'])->middleware(['can:import-stock-movements'])->name('import');
Route::get('/initial-items-stock', [StockMovementController::class, 'initialItemsStock'])->middleware(['can:browse-stock-movements'])->name('initial-items-stock');
Route::get('/initial-items-stock/export', [StockMovementController::class, 'exportInitialItemsStock'])->middleware(['can:export-stock-movements'])->name('initial-items-stock.export');
Route::post('/initial-items-stock/import', [StockMovementController::class, 'importInitialItemsStock'])->middleware(['can:import-stock-movements'])->name('initial-items-stock.import');
Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->middleware(['can:browse-stock-movements'])->name('show');
Route::get('/{stockMovement}/edit', [StockMovementController::class, 'edit'])->middleware(['can:update-stock-movements'])->name('edit');
Route::put('/{stockMovement}', [StockMovementController::class, 'update'])->middleware(['can:update-stock-movements'])->name('update');
Route::patch('/{stockMovement}', [StockMovementController::class, 'update'])->middleware(['can:update-stock-movements'])->name('update');
Route::delete('/{stockMovement}', [StockMovementController::class, 'destroy'])->middleware(['can:delete-stock-movements'])->name('destroy');
