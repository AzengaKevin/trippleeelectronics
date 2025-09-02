<?php

use App\Http\Controllers\Backoffice\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ServiceController::class, 'index'])->middleware(['can:browse-services'])->name('index');
Route::post('/', [ServiceController::class, 'store'])->middleware(['can:create-services'])->name('store');
Route::get('/create', [ServiceController::class, 'create'])->middleware(['can:create-services'])->name('create');
Route::get('/export', [ServiceController::class, 'export'])->middleware(['can:export-services'])->name('export');
Route::get('/import', [ServiceController::class, 'import'])->middleware(['can:import-services'])->name('import');
Route::post('/import', [ServiceController::class, 'processImport'])->middleware(['can:import-services'])->name('import');
Route::get('/{service}', [ServiceController::class, 'show'])->middleware(['can:browse-services'])->name('show');
Route::get('/{service}/edit', [ServiceController::class, 'edit'])->middleware(['can:update-services'])->name('edit');
Route::put('/{service}', [ServiceController::class, 'update'])->middleware(['can:update-services'])->name('update');
Route::patch('/{service}', [ServiceController::class, 'update'])->middleware(['can:update-services'])->name('update');
Route::delete('/{service}', [ServiceController::class, 'destroy'])->middleware(['can:delete-services'])->name('destroy');
