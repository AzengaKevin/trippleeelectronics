<?php

use App\Http\Controllers\Backoffice\AgreementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AgreementController::class, 'index'])->middleware(['can:browse-agreements'])->name('index');
Route::post('/', [AgreementController::class, 'store'])->middleware(['can:create-agreements'])->name('store');
Route::get('/create', [AgreementController::class, 'create'])->middleware(['can:create-agreements'])->name('create');
Route::get('/export', [AgreementController::class, 'export'])->middleware(['can:export-agreements'])->name('export');
Route::post('/import', [AgreementController::class, 'import'])->middleware(['can:import-agreements'])->name('import');
Route::get('/{agreement}', [AgreementController::class, 'show'])->middleware(['can:browse-agreements'])->name('show');
Route::get('/{agreement}/edit', [AgreementController::class, 'edit'])->middleware(['can:update-agreements'])->name('edit');
Route::get('/{agreement}/print', [AgreementController::class, 'print'])->middleware(['can:import-agreements'])->name('print');
Route::put('/{agreement}', [AgreementController::class, 'update'])->middleware(['can:update-agreements'])->name('update');
Route::patch('/{agreement}', [AgreementController::class, 'update'])->middleware(['can:update-agreements'])->name('update');
Route::delete('/{agreement}', [AgreementController::class, 'destroy'])->middleware(['can:delete-agreements'])->name('destroy');
