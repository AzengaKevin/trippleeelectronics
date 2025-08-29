<?php

use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
