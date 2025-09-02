<?php

use App\Http\Controllers\Backoffice\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ResourceController::class, 'index'])->middleware(['can:browse-resources'])->name('index');
Route::post('/', [ResourceController::class, 'store'])->middleware(['can:create-resources'])->name('store');
Route::get('/create', [ResourceController::class, 'create'])->middleware(['can:create-resources'])->name('create');
Route::get('/export', [ResourceController::class, 'export'])->middleware(['can:export-resources'])->name('export');
Route::get('/import', [ResourceController::class, 'import'])->middleware(['can:import-resources'])->name('import');
Route::post('/import', [ResourceController::class, 'processImport'])->middleware(['can:import-resources'])->name('import');
Route::get('/{resource}', [ResourceController::class, 'show'])->middleware(['can:browse-resources'])->name('show');
Route::get('/{resource}/edit', [ResourceController::class, 'edit'])->middleware(['can:update-resources'])->name('edit');
Route::put('/{resource}', [ResourceController::class, 'update'])->middleware(['can:update-resources'])->name('update');
Route::patch('/{resource}', [ResourceController::class, 'update'])->middleware(['can:update-resources'])->name('update');
Route::delete('/{resource}', [ResourceController::class, 'destroy'])->middleware(['can:delete-resources'])->name('destroy');
