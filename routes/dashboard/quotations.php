<?php

use App\Http\Controllers\Backoffice\QuotationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuotationController::class, 'index'])->middleware(['can:browse-quotations'])->name('index');
Route::post('/', [QuotationController::class, 'store'])->middleware(['can:create-quotations'])->name('store');
Route::get('/create', [QuotationController::class, 'create'])->middleware(['can:create-quotations'])->name('create');
Route::get('/export', [QuotationController::class, 'export'])->middleware(['can:export-quotations'])->name('export');
Route::get('/import', [QuotationController::class, 'import'])->middleware(['can:import-quotations'])->name('import');
Route::post('/import', [QuotationController::class, 'processImport'])->middleware(['can:import-quotations'])->name('import');
Route::get('/{quotation}', [QuotationController::class, 'show'])->middleware(['can:browse-quotations'])->name('show');
Route::get('/{quotation}/edit', [QuotationController::class, 'edit'])->middleware(['can:update-quotations'])->name('edit');
Route::get('/{quotation}/print', [QuotationController::class, 'print'])->middleware(['can:browse-quotations'])->name('print');
Route::put('/{quotation}', [QuotationController::class, 'update'])->middleware(['can:update-quotations'])->name('update');
Route::patch('/{quotation}', [QuotationController::class, 'update'])->middleware(['can:update-quotations'])->name('update');
Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->middleware(['can:delete-quotations'])->name('destroy');
