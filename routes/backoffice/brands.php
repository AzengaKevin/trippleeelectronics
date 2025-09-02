<?php

use App\Http\Controllers\Backoffice\BrandController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BrandController::class, 'index'])->middleware(['can:browse-brands'])->name('index');
Route::post('/', [BrandController::class, 'store'])->middleware(['can:create-brands'])->name('store');
Route::get('/create', [BrandController::class, 'create'])->middleware(['can:create-brands'])->name('create');
Route::get('/export', [BrandController::class, 'export'])->middleware(['can:export-brands'])->name('export');
Route::get('/import', [BrandController::class, 'import'])->middleware(['can:import-brands'])->name('import');
Route::post('/import', [BrandController::class, 'processImport'])->middleware(['can:import-brands'])->name('import');
Route::get('/{brand}', [BrandController::class, 'show'])->middleware(['can:browse-brands'])->name('show');
Route::get('/{brand}/edit', [BrandController::class, 'edit'])->middleware(['can:update-brands'])->name('edit');
Route::put('/{brand}', [BrandController::class, 'update'])->middleware(['can:update-brands'])->name('update');
Route::patch('/{brand}', [BrandController::class, 'update'])->middleware(['can:update-brands'])->name('update');
Route::delete('/{brand}', [BrandController::class, 'destroy'])->middleware(['can:delete-brands'])->name('destroy');
