<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MpesaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockLevelController;
use App\Http\Controllers\Api\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::as('api.')->group(function () {

    Route::get('/user', fn (Request $request) => new UserResource($request->user()))->middleware('auth:sanctum');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/products/{product}/stock-levels', [ProductController::class, 'stockLevels'])->name('products.stock-levels.index');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
    Route::get('/stock-levels', [StockLevelController::class, 'index'])->name('stock-levels.index');

    Route::any('/mpesa/validate', [MpesaController::class, 'validate'])->name('mpesa.validate');
    Route::any('/mpesa/confirm', [MpesaController::class, 'confirm'])->name('mpesa.confirm');
    Route::any('/mpesa/timeout', [MpesaController::class, 'timeout'])->name('mpesa.timeout');
    Route::any('/mpesa/result', [MpesaController::class, 'result'])->name('mpesa.result');
    Route::any('/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');

    Route::prefix('rooms')->as('rooms.')->group(base_path('routes/api/rooms.php'));
});
