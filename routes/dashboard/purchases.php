<?php

use App\Http\Controllers\Backoffice\PurchaseController;
use App\Http\Controllers\PurchasePaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PurchaseController::class, 'index'])->middleware(['can:browse-purchases'])->name('index');
Route::post('/', [PurchaseController::class, 'store'])->middleware(['can:create-purchases'])->name('store');
Route::get('/create', [PurchaseController::class, 'create'])->middleware(['can:create-purchases'])->name('create');
Route::get('/export', [PurchaseController::class, 'export'])->middleware(['can:export-purchases'])->name('export');
Route::get('/import', [PurchaseController::class, 'import'])->middleware(['can:import-purchases'])->name('import');
Route::post('/import', [PurchaseController::class, 'processImport'])->middleware(['can:import-purchases'])->name('import');
Route::get('/{purchase}', [PurchaseController::class, 'show'])->middleware(['can:browse-purchases'])->name('show');
Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->middleware(['can:update-purchases'])->name('edit');
Route::post('/{purchase}/payments', [PurchasePaymentController::class, 'store'])->middleware('can:browse-purchases', 'can:create-payments')->name('payments.store');
Route::put('/{purchase}', [PurchaseController::class, 'update'])->middleware(['can:update-purchases'])->name('update');
Route::patch('/{purchase}', [PurchaseController::class, 'update'])->middleware(['can:update-purchases'])->name('update');
Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->middleware(['can:delete-purchases'])->name('destroy');
