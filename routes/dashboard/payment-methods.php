<?php

use App\Http\Controllers\Backoffice\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaymentMethodController::class, 'index'])->middleware(['can:browse-payment-methods'])->name('index');
Route::post('/', [PaymentMethodController::class, 'store'])->middleware(['can:create-payment-methods'])->name('store');
Route::get('/create', [PaymentMethodController::class, 'create'])->middleware(['can:create-payment-methods'])->name('create');
Route::get('/export', [PaymentMethodController::class, 'export'])->middleware(['can:export-payment-methods'])->name('export');
Route::post('/import', [PaymentMethodController::class, 'import'])->middleware(['can:import-payment-methods'])->name('import');
Route::get('/{method}', [PaymentMethodController::class, 'show'])->middleware(['can:browse-payment-methods'])->name('show');
Route::get('/{method}/edit', [PaymentMethodController::class, 'edit'])->middleware(['can:update-payment-methods'])->name('edit');
Route::put('/{method}', [PaymentMethodController::class, 'update'])->middleware(['can:update-payment-methods'])->name('update');
Route::patch('/{method}', [PaymentMethodController::class, 'update'])->middleware(['can:update-payment-methods'])->name('update');
Route::delete('/{method}', [PaymentMethodController::class, 'destroy'])->middleware(['can:delete-payment-methods'])->name('destroy');
