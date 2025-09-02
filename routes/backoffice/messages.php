<?php

use App\Http\Controllers\Backoffice\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MessageController::class, 'index'])->middleware(['can:browse-messages'])->name('index');
Route::post('/', [MessageController::class, 'store'])->middleware(['can:create-messages'])->name('store');
Route::get('/{thread}', [MessageController::class, 'show'])->middleware(['can:browse-messages'])->name('show');
Route::put('/{message}/read', [MessageController::class, 'read'])->middleware(['can:browse-messages'])->name('read');
