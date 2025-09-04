<?php

use App\Http\Controllers\Backoffice\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/booking', [BookingController::class, 'show'])->middleware(['can:access-booking'])->name('show');
