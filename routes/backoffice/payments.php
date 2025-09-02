<?php

use App\Http\Controllers\Backoffice\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaymentController::class, 'index'])->middleware(['can:browse-payments'])->name('index');
Route::post('/', [PaymentController::class, 'store'])->middleware(['can:create-payments'])->name('store');
Route::get('/create', [PaymentController::class, 'create'])->middleware(['can:create-payments'])->name('create');
Route::get('/export', [PaymentController::class, 'export'])->middleware(['can:export-payments'])->name('export');
Route::get('/import', [PaymentController::class, 'import'])->middleware(['can:import-payments'])->name('import');
Route::post('/import', [PaymentController::class, 'processImport'])->middleware(['can:import-payments'])->name('import');
Route::get('/{payment}', [PaymentController::class, 'show'])->middleware(['can:browse-payments'])->name('show');
Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->middleware(['can:update-payments'])->name('edit');
Route::put('/{payment}', [PaymentController::class, 'update'])->middleware(['can:update-payments'])->name('update');
Route::patch('/{payment}', [PaymentController::class, 'update'])->middleware(['can:update-payments'])->name('update');
Route::delete('/{payment}', [PaymentController::class, 'destroy'])->middleware(['can:delete-payments'])->name('destroy');
