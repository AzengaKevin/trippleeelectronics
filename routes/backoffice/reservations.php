<?php

use App\Http\Controllers\Backoffice\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReservationController::class, 'index'])->middleware(['can:browse-reservations'])->name('index');
Route::get('/create', [ReservationController::class, 'create'])->middleware(['can:create-reservations'])->name('create');
Route::post('/', [ReservationController::class, 'store'])->middleware(['can:create-reservations'])->name('store');
Route::get('/{reservation}', [ReservationController::class, 'show'])->middleware(['can:browse-reservations'])->name('show');
