<?php

use App\Http\Controllers\Backoffice\PropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PropertyController::class, 'index'])->middleware(['can:browse-properties'])->name('index');
Route::post('/', [PropertyController::class, 'store'])->middleware(['can:create-properties'])->name('store');
Route::get('/create', [PropertyController::class, 'create'])->middleware(['can:create-properties'])->name('create');
Route::get('/export', [PropertyController::class, 'export'])->middleware(['can:export-properties'])->name('export');
Route::post('/import', [PropertyController::class, 'import'])->middleware(['can:import-properties'])->name('import');
Route::get('/{property}', [PropertyController::class, 'show'])->middleware(['can:browse-properties'])->name('show');
Route::get('/{property}/edit', [PropertyController::class, 'edit'])->middleware(['can:update-properties'])->name('edit');
Route::put('/{property}', [PropertyController::class, 'update'])->middleware(['can:update-properties'])->name('update');
Route::delete('/{property}', [PropertyController::class, 'destroy'])->middleware(['can:delete-properties'])->name('destroy');
