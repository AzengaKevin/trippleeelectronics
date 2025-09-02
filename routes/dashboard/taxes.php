<?php

use App\Http\Controllers\Backoffice\TaxController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaxController::class, 'index'])->middleware(['can:browse-taxes'])->name('index');
Route::post('/', [TaxController::class, 'store'])->middleware(['can:create-taxes'])->name('store');
Route::get('/create', [TaxController::class, 'create'])->middleware(['can:create-taxes'])->name('create');
Route::get('/export', [TaxController::class, 'export'])->middleware(['can:export-taxes'])->name('export');
Route::get('/import', [TaxController::class, 'import'])->middleware(['can:import-taxes'])->name('import');
Route::get('/{tax}', [TaxController::class, 'show'])->middleware(['can:browse-taxes'])->name('show');
Route::get('/{tax}/edit', [TaxController::class, 'edit'])->middleware(['can:update-taxes'])->name('edit');
Route::put('/{tax}', [TaxController::class, 'update'])->middleware(['can:update-taxes'])->name('update');
Route::patch('/{tax}', [TaxController::class, 'update'])->middleware(['can:update-taxes'])->name('update');
Route::delete('/{tax}', [TaxController::class, 'destroy'])->middleware(['can:delete-taxes'])->name('destroy');
