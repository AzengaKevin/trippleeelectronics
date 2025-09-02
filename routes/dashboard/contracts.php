<?php

use App\Http\Controllers\Backoffice\ContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContractController::class, 'index'])->name('index');
Route::post('/', [ContractController::class, 'store'])->middleware(['can:create-contracts'])->name('store');
Route::get('/create', [ContractController::class, 'create'])->middleware(['can:create-contracts'])->name('create');
Route::get('/{contract}', [ContractController::class, 'show'])->middleware(['can:browse-contracts'])->name('show');
Route::get('/{contract}/edit', [ContractController::class, 'edit'])->middleware(['can:update-contracts'])->name('edit');
Route::put('/{contract}', [ContractController::class, 'update'])->middleware(['can:update-contracts'])->name('update');
Route::patch('/{contract}', [ContractController::class, 'update'])->middleware(['can:update-contracts'])->name('update');
Route::delete('/{contract}', [ContractController::class, 'destroy'])->middleware(['can:delete-contracts'])->name('destroy');
