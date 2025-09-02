<?php

use App\Http\Controllers\Backoffice\SuspensionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SuspensionController::class, 'index'])->name('index');
Route::post('/', [SuspensionController::class, 'store'])->middleware(['can:create-suspensions'])->name('store');
Route::get('/create', [SuspensionController::class, 'create'])->middleware(['can:create-suspensions'])->name('create');
Route::get('/{suspension}', [SuspensionController::class, 'show'])->middleware(['can:browse-suspensions'])->name('show');
Route::get('/{suspension}/edit', [SuspensionController::class, 'edit'])->middleware(['can:update-suspensions'])->name('edit');
Route::put('/{suspension}', [SuspensionController::class, 'update'])->middleware(['can:update-suspensions'])->name('update');
Route::patch('/{suspension}', [SuspensionController::class, 'update'])->middleware(['can:update-suspensions'])->name('update');
Route::delete('/{suspension}', [SuspensionController::class, 'destroy'])->middleware(['can:delete-suspensions'])->name('destroy');
