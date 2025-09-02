<?php

use App\Http\Controllers\Backoffice\OrganizationCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrganizationCategoryController::class, 'index'])->middleware(['can:browse-organization-categories'])->name('index');
Route::post('/', [OrganizationCategoryController::class, 'store'])->middleware(['can:create-organization-categories'])->name('store');
Route::get('/create', [OrganizationCategoryController::class, 'create'])->middleware(['can:create-organization-categories'])->name('create');
Route::get('/export', [OrganizationCategoryController::class, 'export'])->middleware(['can:export-organization-categories'])->name('export');
Route::get('/import', [OrganizationCategoryController::class, 'import'])->middleware(['can:import-organization-categories'])->name('import');
Route::post('/import', [OrganizationCategoryController::class, 'processImport'])->middleware(['can:import-organization-categories'])->name('import');
Route::get('/{category}', [OrganizationCategoryController::class, 'show'])->middleware(['can:browse-organization-categories'])->name('show');
Route::get('/{category}/edit', [OrganizationCategoryController::class, 'edit'])->middleware(['can:update-organization-categories'])->name('edit');
Route::put('/{category}', [OrganizationCategoryController::class, 'update'])->middleware(['can:update-organization-categories'])->name('update');
Route::patch('/{category}', [OrganizationCategoryController::class, 'update'])->middleware(['can:update-organization-categories'])->name('update');
Route::delete('/{category}', [OrganizationCategoryController::class, 'destroy'])->middleware(['can:delete-organization-categories'])->name('destroy');
