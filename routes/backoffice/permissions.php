<?php

use App\Http\Controllers\Backoffice\PermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PermissionController::class, 'index'])->middleware(['can:browse-permissions'])->name('index');
Route::post('/', [PermissionController::class, 'store'])->middleware(['can:create-permissions'])->name('store');
Route::get('/create', [PermissionController::class, 'create'])->middleware(['can:create-permissions'])->name('create');
Route::get('/export', [PermissionController::class, 'export'])->middleware(['can:export-permissions'])->name('export');
Route::get('/import', [PermissionController::class, 'import'])->middleware(['can:import-permissions'])->name('import');
Route::post('/import', [PermissionController::class, 'processImport'])->middleware(['can:import-permissions'])->name('import');
Route::get('/{permission}', [PermissionController::class, 'show'])->middleware(['can:browse-permissions'])->name('show');
Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->middleware(['can:update-permissions'])->name('edit');
Route::put('/{permission}', [PermissionController::class, 'update'])->middleware(['can:update-permissions'])->name('update');
Route::patch('/{permission}', [PermissionController::class, 'update'])->middleware(['can:update-permissions'])->name('update');
Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware(['can:delete-permissions'])->name('destroy');
