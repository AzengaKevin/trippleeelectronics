<?php

use App\Http\Controllers\Backoffice\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->middleware(['can:browse-bookings'])->name('index');
Route::get('/create', [BookingController::class, 'create'])->middleware(['can:create-bookings'])->name('create');
