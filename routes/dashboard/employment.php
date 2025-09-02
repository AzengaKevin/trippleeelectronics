<?php

use App\Http\Controllers\Backoffice\EmploymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EmploymentController::class, 'show'])->middleware(['can:read-employment'])->name('show');
Route::get('/edit', [EmploymentController::class, 'edit'])->middleware(['can:update-employment'])->name('edit');
Route::patch('/', [EmploymentController::class, 'update'])->middleware(['can:update-employment'])->name('update');
Route::put('/', [EmploymentController::class, 'update'])->middleware(['can:update-employment'])->name('update');
