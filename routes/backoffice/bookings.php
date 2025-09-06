<?php

use App\Http\Controllers\Backoffice\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'show'])->middleware(['can:browse-bookings'])->name('show');
