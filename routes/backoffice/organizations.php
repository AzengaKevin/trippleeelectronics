<?php

use App\Http\Controllers\Backoffice\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrganizationController::class, 'index'])->middleware(['can:browse-organizations'])->name('index');
Route::post('/', [OrganizationController::class, 'store'])->middleware(['can:create-organizations'])->name('store');
Route::get('/create', [OrganizationController::class, 'create'])->middleware(['can:create-organizations'])->name('create');
Route::get('/export', [OrganizationController::class, 'export'])->middleware(['can:export-organizations'])->name('export');
Route::get('/import', [OrganizationController::class, 'import'])->middleware(['can:import-organizations'])->name('import');
Route::post('/import', [OrganizationController::class, 'processImport'])->middleware(['can:import-organizations'])->name('import');
Route::get('/{organization}', [OrganizationController::class, 'show'])->middleware(['can:browse-organizations'])->name('show');
Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->middleware(['can:update-organizations'])->name('edit');
Route::put('/{organization}', [OrganizationController::class, 'update'])->middleware(['can:update-organizations'])->name('update');
Route::patch('/{organization}', [OrganizationController::class, 'update'])->middleware(['can:update-organizations'])->name('update');
Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->middleware(['can:delete-organizations'])->name('destroy');
