<?php

use App\Http\Controllers\Backoffice\CarouselController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CarouselController::class, 'index'])->middleware(['can:browse-carousels'])->name('index');
Route::post('/', [CarouselController::class, 'store'])->middleware(['can:create-carousels'])->name('store');
Route::get('/create', [CarouselController::class, 'create'])->middleware(['can:create-carousels'])->name('create');
Route::get('/export', [CarouselController::class, 'export'])->middleware(['can:export-carousels'])->name('export');
Route::get('/import', [CarouselController::class, 'import'])->middleware(['can:import-carousels'])->name('import');
Route::post('/import', [CarouselController::class, 'processImport'])->middleware(['can:import-carousels'])->name('import');
Route::get('/{carousel}', [CarouselController::class, 'show'])->middleware(['can:browse-carousels'])->name('show');
Route::get('/{carousel}/edit', [CarouselController::class, 'edit'])->middleware(['can:update-carousels'])->name('edit');
Route::put('/{carousel}', [CarouselController::class, 'update'])->middleware(['can:update-carousels'])->name('update');
Route::patch('/{carousel}', [CarouselController::class, 'update'])->middleware(['can:update-carousels'])->name('update');
Route::delete('/{carousel}', [CarouselController::class, 'destroy'])->middleware(['can:delete-carousels'])->name('destroy');
