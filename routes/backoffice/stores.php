<?php

use App\Http\Controllers\Backoffice\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->middleware(['can:browse-stores'])->name('index');
Route::post('/', [StoreController::class, 'store'])->middleware(['can:browse-stores'])->name('store');
Route::get('/create', [StoreController::class, 'create'])->middleware(['can:browse-stores'])->name('create');
Route::get('/export', [StoreController::class, 'export'])->middleware(['can:browse-stores'])->name('export');
Route::get('/import', [StoreController::class, 'import'])->middleware(['can:browse-stores'])->name('import');
Route::post('/import', [StoreController::class, 'processImport'])->middleware(['can:browse-stores'])->name('import');
Route::get('/{store}', [StoreController::class, 'show'])->middleware(['can:browse-stores'])->name('show');
Route::get('/{store}/edit', [StoreController::class, 'edit'])->middleware(['can:browse-stores'])->name('edit');
Route::put('/{store}', [StoreController::class, 'update'])->middleware(['can:browse-stores'])->name('update');
Route::patch('/{store}', [StoreController::class, 'update'])->middleware(['can:browse-stores'])->name('update');
Route::delete('/{store}', [StoreController::class, 'destroy'])->middleware(['can:browse-stores'])->name('destroy');
