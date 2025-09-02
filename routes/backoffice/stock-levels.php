<?php

use App\Http\Controllers\Backoffice\StockLevelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StockLevelController::class, 'index'])->middleware(['can:browse-stock-levels'])->name('index');
Route::post('/', [StockLevelController::class, 'store'])->middleware(['can:create-stock-levels'])->name('store');
Route::get('/create', [StockLevelController::class, 'create'])->middleware(['can:create-stock-levels'])->name('create');
Route::get('/export', [StockLevelController::class, 'export'])->middleware(['can:export-stock-levels'])->name('export');
Route::get('/import', [StockLevelController::class, 'import'])->middleware(['can:import-stock-levels'])->name('import');
Route::post('/import', [StockLevelController::class, 'processImport'])->middleware(['can:import-stock-levels'])->name('import');
Route::get('/{stockLevel}', [StockLevelController::class, 'show'])->middleware(['can:browse-stock-levels'])->name('show');
Route::get('/{stockLevel}/edit', [StockLevelController::class, 'edit'])->middleware(['can:update-stock-levels'])->name('edit');
Route::put('/{stockLevel}', [StockLevelController::class, 'update'])->middleware(['can:update-stock-levels'])->name('update');
Route::patch('/{stockLevel}', [StockLevelController::class, 'update'])->middleware(['can:update-stock-levels'])->name('update');
Route::delete('/{stockLevel}', [StockLevelController::class, 'destroy'])->middleware(['can:delete-stock-levels'])->name('destroy');
