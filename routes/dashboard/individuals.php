<?php

use App\Http\Controllers\Backoffice\IndividualController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndividualController::class, 'index'])->middleware(['can:browse-individuals'])->name('index');
Route::post('/', [IndividualController::class, 'store'])->middleware(['can:create-individuals'])->name('store');
Route::get('/create', [IndividualController::class, 'create'])->middleware(['can:create-individuals'])->name('create');
Route::get('/export', [IndividualController::class, 'export'])->middleware(['can:export-individuals'])->name('export');
Route::get('/import', [IndividualController::class, 'import'])->middleware(['can:import-individuals'])->name('import');
Route::post('/import', [IndividualController::class, 'processImport'])->middleware(['can:import-individuals'])->name('import');
Route::get('/{individual}', [IndividualController::class, 'show'])->middleware(['can:browse-individuals'])->name('show');
Route::get('/{individual}/edit', [IndividualController::class, 'edit'])->middleware(['can:update-individuals'])->name('edit');
Route::put('/{individual}', [IndividualController::class, 'update'])->middleware(['can:update-individuals'])->name('update');
Route::patch('/{individual}', [IndividualController::class, 'update'])->middleware(['can:update-individuals'])->name('update');
Route::delete('/{individual}', [IndividualController::class, 'destroy'])->middleware(['can:delete-individuals'])->name('destroy');
