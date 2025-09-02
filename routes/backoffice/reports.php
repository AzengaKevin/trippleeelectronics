<?php

use App\Http\Controllers\Backoffice\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReportController::class, 'index'])->middleware(['can:browse-reports'])->name('index');
Route::get('/print', [ReportController::class, 'print'])->middleware(['can:print-reports'])->name('print');
Route::get('/pnl', [ReportController::class, 'pnl'])->middleware(['can:browse-reports'])->name('pnl');
Route::get('/pnl/print', [ReportController::class, 'pnlPrint'])->middleware(['can:print-reports'])->name('pnl.print');
