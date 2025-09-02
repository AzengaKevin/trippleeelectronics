<?php

use App\Http\Controllers\Backoffice\ItemCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemCategoryController::class, 'index'])->middleware(['can:browse-item-categories'])->name('index');
Route::post('/', [ItemCategoryController::class, 'store'])->middleware(['can:browse-item-categories'])->name('store');
Route::get('/export', [ItemCategoryController::class, 'export'])->middleware(['can:browse-item-categories'])->name('export');
Route::get('/import', [ItemCategoryController::class, 'import'])->middleware(['can:browse-item-categories'])->name('import');
Route::post('/import', [ItemCategoryController::class, 'processImport'])->middleware(['can:browse-item-categories'])->name('import');
Route::get('/create', [ItemCategoryController::class, 'create'])->middleware(['can:browse-item-categories'])->name('create');
Route::get('/{category}', [ItemCategoryController::class, 'show'])->middleware(['can:browse-item-categories'])->name('show');
Route::get('/{category}/edit', [ItemCategoryController::class, 'edit'])->middleware(['can:browse-item-categories'])->name('edit');
Route::put('/{category}', [ItemCategoryController::class, 'update'])->middleware(['can:browse-item-categories'])->name('update');
Route::patch('/{category}', [ItemCategoryController::class, 'update'])->middleware(['can:browse-item-categories'])->name('update');
Route::delete('/{category}', [ItemCategoryController::class, 'destroy'])->middleware(['can:browse-item-categories'])->name('destroy');
