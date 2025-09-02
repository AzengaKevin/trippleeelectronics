<?php

use App\Http\Controllers\Backoffice\ItemVariantController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemVariantController::class, 'index'])->middleware(['can:browse-item-variants'])->name('index');
Route::post('/', [ItemVariantController::class, 'store'])->middleware(['can:create-item-variants'])->name('store');
Route::get('/create', [ItemVariantController::class, 'create'])->middleware(['can:create-item-variants'])->name('create');
Route::get('/export', [ItemVariantController::class, 'export'])->middleware(['can:export-item-variants'])->name('export');
Route::get('/import', [ItemVariantController::class, 'import'])->middleware(['can:import-item-variants'])->name('import');
Route::post('/import', [ItemVariantController::class, 'processImport'])->middleware(['can:import-item-variants'])->name('import');
Route::get('/{itemVariant}', [ItemVariantController::class, 'show'])->middleware(['can:browse-item-variants'])->name('show');
Route::get('/{itemVariant}/edit', [ItemVariantController::class, 'edit'])->middleware(['can:update-item-variants'])->name('edit');
Route::put('/{itemVariant}', [ItemVariantController::class, 'update'])->middleware(['can:update-item-variants'])->name('update');
Route::patch('/{itemVariant}', [ItemVariantController::class, 'update'])->middleware(['can:update-item-variants'])->name('update');
Route::delete('/{itemVariant}', [ItemVariantController::class, 'destroy'])->middleware(['can:delete-item-variants'])->name('destroy');
