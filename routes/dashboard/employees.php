<?php

use App\Http\Controllers\Backoffice\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EmployeeController::class, 'index'])->middleware(['can:browse-employees'])->name('index');
Route::get('/{employee}', [EmployeeController::class, 'show'])->middleware(['can:browse-employees'])->name('show');
Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->middleware(['can:update-employees'])->name('edit');
Route::put('/{employee}', [EmployeeController::class, 'update'])->middleware(['can:update-employees'])->name('update');
Route::post('/{employee}/suspend', [EmployeeController::class, 'suspend'])->middleware(['can:update-employees'])->name('suspend');
Route::patch('/{employee}', [EmployeeController::class, 'update'])->middleware(['can:update-employees'])->name('update');
Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->middleware(['can:delete-employees'])->name('destroy');
