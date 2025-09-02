<?php

use App\Http\Controllers\Backoffice\OrderController;
use App\Http\Controllers\Backoffice\OrderPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class, 'index'])->middleware(['can:browse-orders'])->name('index');
Route::post('/', [OrderController::class, 'store'])->middleware(['can:create-orders'])->name('store');
Route::get('/create', [OrderController::class, 'create'])->middleware(['can:create-orders'])->name('create');
Route::get('/export', [OrderController::class, 'export'])->middleware(['can:export-orders'])->name('export');
Route::get('/import', [OrderController::class, 'import'])->middleware(['can:import-orders'])->name('import');
Route::post('/import', [OrderController::class, 'processImport'])->middleware(['can:import-orders'])->name('import');
Route::get('/reports', [OrderController::class, 'reports'])->middleware(['can:browse-orders'])->name('reports');
Route::get('/reports/detailed-report', [OrderController::class, 'detailedReport'])->middleware(['can:export-orders'])->name('reports.detailed-report');
Route::get('/{order}', [OrderController::class, 'show'])->middleware(['can:browse-orders'])->name('show');
Route::get('/{order}/payments', [OrderPaymentController::class, 'index'])->middleware(['can:browse-orders'])->name('payments.index');
Route::post('/{order}/payments', [OrderPaymentController::class, 'store'])->middleware('can:browse-orders', 'can:create-payments')->name('payments.store');
Route::get('/{order}/edit', [OrderController::class, 'edit'])->middleware(['can:update-orders'])->name('edit');
Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->middleware(['can:browse-orders'])->name('invoice');
Route::get('/{order}/receipt', [OrderController::class, 'receipt'])->middleware(['can:browse-orders'])->name('receipt');
Route::patch('/{order}/mark-complete', [OrderController::class, 'markComplete'])->middleware(['can:update-orders'])->name('mark-complete');
Route::put('/{order}', [OrderController::class, 'update'])->middleware(['can:update-orders'])->name('update');
Route::patch('/{order}', [OrderController::class, 'partialUpdate'])->middleware(['can:update-orders'])->name('update');
Route::delete('/{order}', [OrderController::class, 'destroy'])->middleware(['can:delete-orders'])->name('destroy');
