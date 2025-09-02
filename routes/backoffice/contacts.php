<?php

use App\Http\Controllers\Backoffice\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContactController::class, 'index'])->middleware(['can:browse-contacts'])->name('index');
Route::get('/{contact}', [ContactController::class, 'show'])->middleware(['can:browse-contacts'])->name('show');
Route::delete('/{contact}', [ContactController::class, 'destroy'])->middleware(['can:delete-contacts'])->name('destroy');
