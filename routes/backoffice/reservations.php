<?php

use App\Http\Controllers\Backoffice\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReservationController::class, 'index'])->middleware(['can:browse-reservations'])->name('index');
Route::post('/', [ReservationController::class, 'store'])->middleware(['can:create-bookings'])->name('store');
