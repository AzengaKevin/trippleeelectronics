<?php

use App\Http\Controllers\Backoffice\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index'])->middleware(['can:browse-users'])->name('index');
Route::post('/', [UserController::class, 'store'])->middleware(['can:create-users'])->name('store');
Route::get('/create', [UserController::class, 'create'])->middleware(['can:create-users'])->name('create');
Route::get('/export', [UserController::class, 'export'])->middleware(['can:export-users'])->name('export');
Route::get('/import', [UserController::class, 'import'])->middleware(['can:import-users'])->name('import');
Route::post('/import', [UserController::class, 'processImport'])->middleware(['can:import-users'])->name('import');
Route::get('/{user}', [UserController::class, 'show'])->middleware(['can:browse-users'])->name('show');
Route::get('/{user}/edit', [UserController::class, 'edit'])->middleware(['can:update-users'])->name('edit');
Route::put('/{user}', [UserController::class, 'update'])->middleware(['can:update-users'])->name('update');
Route::patch('/{user}', [UserController::class, 'update'])->middleware(['can:update-users'])->name('update');
Route::patch('/{user}/password', [UserController::class, 'updatePassword'])->middleware(['can:update-users'])->name('update-password');
Route::delete('/{user}', [UserController::class, 'destroy'])->middleware(['can:delete-users'])->name('destroy');
