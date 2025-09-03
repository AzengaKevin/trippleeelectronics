<?php

use App\Http\Controllers\Backoffice\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoomController::class, 'index'])->middleware(['can:browse-rooms'])->name('index');
Route::post('/', [RoomController::class, 'store'])->middleware(['can:create-rooms'])->name('store');
Route::get('/create', [RoomController::class, 'create'])->middleware(['can:create-rooms'])->name('create');
Route::get('/export', [RoomController::class, 'export'])->middleware(['can:export-rooms'])->name('export');
Route::post('/import', [RoomController::class, 'import'])->middleware(['can:import-rooms'])->name('import');
Route::get('/{room}', [RoomController::class, 'show'])->middleware(['can:browse-rooms'])->name('show');
Route::get('/{room}/edit', [RoomController::class, 'edit'])->middleware(['can:update-rooms'])->name('edit');
Route::put('/{room}', [RoomController::class, 'update'])->middleware(['can:update-rooms'])->name('update');
Route::delete('/{room}', [RoomController::class, 'destroy'])->middleware(['can:delete-rooms'])->name('destroy');
