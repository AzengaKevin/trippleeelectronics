<?php

use App\Http\Controllers\Backoffice\AccountingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AccountingController::class, 'index'])->middleware(['can:browse-accounting-periods'])->name('index');
Route::get('/{accountingPeriod}', [AccountingController::class, 'show'])->middleware(['can:browse-accounting-periods'])->name('show');
