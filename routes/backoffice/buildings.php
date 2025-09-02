<?php

use App\Http\Controllers\Backoffice\BuildingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BuildingController::class, 'index'])->middleware(['can:browse-buildings'])->name('index');
Route::post('/', [BuildingController::class, 'store'])->middleware(['can:create-buildings'])->name('store');
Route::get('/create', [BuildingController::class, 'create'])->middleware(['can:create-buildings'])->name('create');
Route::get('/export', [BuildingController::class, 'export'])->middleware(['can:export-buildings'])->name('export');
Route::post('/import', [BuildingController::class, 'import'])->middleware(['can:import-buildings'])->name('import');
Route::get('/{building}', [BuildingController::class, 'show'])->middleware(['can:browse-buildings'])->name('show');
Route::get('/{building}/edit', [BuildingController::class, 'edit'])->middleware(['can:update-buildings'])->name('edit');
Route::put('/{building}', [BuildingController::class, 'update'])->middleware(['can:update-buildings'])->name('update');
Route::delete('/{building}', [BuildingController::class, 'destroy'])->middleware(['can:delete-buildings'])->name('destroy');
