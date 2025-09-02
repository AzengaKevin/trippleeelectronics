<?php

use App\Http\Controllers\Backoffice\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TransactionController::class, 'index'])->middleware(['can:browse-transactions'])->name('index');
Route::post('/', [TransactionController::class, 'store'])->middleware(['can:create-transactions'])->name('store');
Route::get('/create', [TransactionController::class, 'create'])->middleware(['can:create-transactions'])->name('create');
Route::get('/export', [TransactionController::class, 'export'])->middleware(['can:export-transactions'])->name('export');
Route::get('/import', [TransactionController::class, 'import'])->middleware(['can:import-transactions'])->name('import');
Route::post('/import', [TransactionController::class, 'processImport'])->middleware(['can:import-transactions'])->name('import');
Route::get('/{transaction}', [TransactionController::class, 'show'])->middleware(['can:browse-transactions'])->name('show');
Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->middleware(['can:update-transactions'])->name('edit');
Route::put('/{transaction}', [TransactionController::class, 'update'])->middleware(['can:update-transactions'])->name('update');
Route::patch('/{transaction}', [TransactionController::class, 'update'])->middleware(['can:update-transactions'])->name('update');
Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->middleware(['can:delete-transactions'])->name('destroy');
