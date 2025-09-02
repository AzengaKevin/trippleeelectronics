<?php

use App\Http\Controllers\Backoffice\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoleController::class, 'index'])->middleware(['can:browse-roles'])->name('index');
Route::post('/', [RoleController::class, 'store'])->middleware(['can:create-roles'])->name('store');
Route::get('/create', [RoleController::class, 'create'])->middleware(['can:create-roles'])->name('create');
Route::get('/export', [RoleController::class, 'export'])->middleware(['can:export-roles'])->name('export');
Route::get('/import', [RoleController::class, 'import'])->middleware(['can:import-roles'])->name('import');
Route::post('/import', [RoleController::class, 'processImport'])->middleware(['can:import-roles'])->name('import');
Route::get('/{role}', [RoleController::class, 'show'])->middleware(['can:browse-roles'])->name('show');
Route::get('/{role}/edit', [RoleController::class, 'edit'])->middleware(['can:update-roles'])->name('edit');
Route::put('/{role}', [RoleController::class, 'update'])->middleware(['can:update-roles'])->name('update');
Route::patch('/{role}', [RoleController::class, 'update'])->middleware(['can:update-roles'])->name('update');
Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware(['can:delete-roles'])->name('destroy');
