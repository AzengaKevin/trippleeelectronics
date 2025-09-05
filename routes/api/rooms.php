<?php

use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoomController::class, 'index'])->name('index');
