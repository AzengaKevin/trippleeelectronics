<?php

use App\Http\Controllers\Backoffice\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MediaController::class, 'index'])->middleware(['can:browse-media'])->name('index');
Route::post('/', [MediaController::class, 'store'])->middleware(['can:create-media'])->name('store');
Route::get('/create', [MediaController::class, 'create'])->middleware(['can:create-media'])->name('create');
Route::get('/export', [MediaController::class, 'export'])->middleware(['can:export-media'])->name('export');
Route::get('/import', [MediaController::class, 'import'])->middleware(['can:import-media'])->name('import');
Route::post('/import', [MediaController::class, 'processImport'])->middleware(['can:import-media'])->name('import');
Route::get('/{individual}', [MediaController::class, 'show'])->middleware(['can:browse-media'])->name('show');
Route::get('/{individual}/edit', [MediaController::class, 'edit'])->middleware(['can:update-media'])->name('edit');
Route::put('/{individual}', [MediaController::class, 'update'])->middleware(['can:update-media'])->name('update');
Route::patch('/{individual}', [MediaController::class, 'update'])->middleware(['can:update-media'])->name('update');
Route::delete('/{individual}', [MediaController::class, 'destroy'])->middleware(['can:delete-media'])->name('destroy');
