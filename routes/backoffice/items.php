<?php

use App\Http\Controllers\Backoffice\ItemController;
use App\Http\Controllers\Backoffice\ItemMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->middleware(['can:browse-items'])->name('index');
Route::post('/', [ItemController::class, 'store'])->middleware(['can:create-items'])->name('store');
Route::get('/create', [ItemController::class, 'create'])->middleware(['can:create-items'])->name('create');
Route::get('/export', [ItemController::class, 'export'])->middleware(['can:export-items'])->name('export');
Route::get('/import', [ItemController::class, 'import'])->middleware(['can:import-items'])->name('import');
Route::post('/import', [ItemController::class, 'processImport'])->middleware(['can:import-items'])->name('import');
Route::get('/{item}', [ItemController::class, 'show'])->middleware(['can:browse-items'])->name('show');
Route::get('/{item}/media', [ItemMediaController::class, 'index'])->middleware(['can:browse-items'])->name('media.index');
Route::delete('/{item}/media/{media}', [ItemMediaController::class, 'destroy'])->middleware(['can:browse-items'])->name('media.destroy');
Route::get('/{item}/edit', [ItemController::class, 'edit'])->middleware(['can:update-items'])->name('edit');
Route::put('/{item}', [ItemController::class, 'update'])->middleware(['can:update-items'])->name('update');
Route::patch('/{item}', [ItemController::class, 'update'])->middleware(['can:update-items'])->name('update');
Route::delete('/{item}', [ItemController::class, 'destroy'])->middleware(['can:delete-items'])->name('destroy');
